<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SqlViewerController extends Controller
{
    private const CONNECTION = 'sqlsrv';
    private const PER_PAGE = 50;

    // Column types that can't be shown as text (images, binary data, etc.)
    private const SKIP_TYPES = [
        'image', 'varbinary', 'binary', 'timestamp', 'rowversion',
        'geography', 'geometry', 'hierarchyid', 'sql_variant',
    ];

    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    /**
     * List all tables with their row counts.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $tables = collect($this->db()->select("
            SELECT t.name AS table_name, SUM(p.rows) AS row_count
            FROM sys.tables t
            JOIN sys.partitions p
              ON p.object_id = t.object_id AND p.index_id IN (0, 1)
            GROUP BY t.name
            ORDER BY t.name
        "));

        if ($search !== '') {
            $tables = $tables->filter(
                fn ($t) => stripos($t->table_name, $search) !== false
            );
        }

        $database = $this->db()->select('SELECT DB_NAME() AS db')[0]->db;

        return view('sqlviewer.tables', compact('tables', 'search', 'database'));
    }

    /**
     * Show the rows of one table (read-only).
     */
    public function show(Request $request, string $table)
    {
        // Only allow tables that really exist (prevents SQL injection via the URL)
        $columns = $this->db()->table('INFORMATION_SCHEMA.COLUMNS')
            ->select('TABLE_SCHEMA', 'COLUMN_NAME', 'DATA_TYPE')
            ->where('TABLE_NAME', $table)
            ->orderBy('ORDINAL_POSITION')
            ->get();

        abort_if($columns->isEmpty(), 404, "Table [$table] not found");

        $schema = $columns->first()->TABLE_SCHEMA;

        $visible = $columns
            ->reject(fn ($c) => in_array(strtolower($c->DATA_TYPE), self::SKIP_TYPES))
            ->values();

        $hidden = $columns->count() - $visible->count();
        $columnNames = $visible->pluck('COLUMN_NAME')->all();

        abort_if(empty($columnNames), 404, 'This table has no viewable columns');

        // Sorting (only on real columns)
        $sort = $request->query('sort');
        $sort = in_array($sort, $columnNames, true) ? $sort : $columnNames[0];
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $query = $this->db()
            ->table("$schema.$table")
            ->select($columnNames)
            ->orderBy($sort, $dir);

        // Search across all visible columns
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $grammar = $this->db()->getQueryGrammar();
            $query->where(function ($w) use ($columnNames, $grammar, $search) {
                foreach ($columnNames as $col) {
                    $w->orWhereRaw(
                        'CAST(' . $grammar->wrap($col) . ' AS NVARCHAR(4000)) LIKE ?',
                        ['%' . $search . '%']
                    );
                }
            });
        }

        $rows = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('sqlviewer.show', compact(
            'table', 'visible', 'hidden', 'rows', 'sort', 'dir', 'search'
        ));
    }
}