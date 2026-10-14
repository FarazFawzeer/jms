<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Product;
use App\Models\User;
use App\Support\Division as DivisionAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $status = $request->query('status', 'active');

        $divisions = Division::query()
            ->search($request->query('q'))
            ->when($status === 'active', fn ($q) => $q->where('status', '<>', 'I'))
            ->when($status === 'inactive', fn ($q) => $q->where('status', 'I'))
            ->orderBy('stock_loc_user_code')
            ->paginate(15)
            ->withQueryString();

        // pieces in stock and users per division (old codes are char(3), so trim before matching)
        $stock = Product::withoutGlobalScope('division')
            ->where('SoldType', 'N')->where('status', '<>', 'I')
            ->groupBy('stock_loc_sys_code')
            ->selectRaw('RTRIM(stock_loc_sys_code) as div, COUNT(*) as pieces')
            ->pluck('pieces', 'div');

        $users = User::query()
            ->whereNotNull('stock_loc_sys_code')
            ->groupBy('stock_loc_sys_code')
            ->selectRaw('RTRIM(stock_loc_sys_code) as div, COUNT(*) as total')
            ->pluck('total', 'div');

        $counts = [
            'all' => Division::count(),
            'active' => Division::where('status', '<>', 'I')->count(),
            'inactive' => Division::where('status', 'I')->count(),
        ];

        return view('divisions.index', compact('divisions', 'stock', 'users', 'counts', 'status'));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        $division = new Division();
        $nextCode = Division::nextCode();

        return view('divisions.create', compact('division', 'nextCode'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->validated($request);

        $division = DB::transaction(function () use ($data) {
            $code = Division::nextCode();
            $data['stock_loc_user_code'] ??= str_pad($code, 3, '0', STR_PAD_LEFT);

            return Division::create($data + [
                'Comp_code' => Division::COMPANY,
                'stock_loc_sys_code' => $code,
                'status' => 'A',
            ]);
        });

        DivisionAccess::flush();

        return response()->json([
            'success' => true,
            'message' => "{$division->display_name} saved. Division No: {$division->user_code}",
            'code' => $division->code,
        ]);
    }

    public function edit(Request $request, Division $division)
    {
        $this->authorizeAdmin($request);

        return view('divisions.create', ['division' => $division, 'nextCode' => null]);
    }

    public function update(Request $request, Division $division)
    {
        $this->authorizeAdmin($request);

        $data = $this->validated($request, $division);
        $data['stock_loc_user_code'] ??= $division->user_code;

        // stock already sits in this division, so it cannot become the Overall (view-only) division
        if ($data['Is_Main'] && ! $division->Is_Main && $this->hasStock($division)) {
            return response()->json([
                'success' => false,
                'errors' => ['is_main' => ['This division already has stock, so it cannot be the Overall division. Create a new division for Overall.']],
            ], 422);
        }

        // the user code is part of the composite key, so update by the system no.
        Division::where('stock_loc_sys_code', $division->stock_loc_sys_code)->update($data);
        DivisionAccess::flush();

        return response()->json([
            'success' => true,
            'message' => "{$data['stock_loc_desc']} updated.",
            'code' => $division->code,
        ]);
    }

    public function toggleStatus(Request $request, Division $division)
    {
        $this->authorizeAdmin($request);

        $active = $division->isActive();
        Division::where('stock_loc_sys_code', $division->stock_loc_sys_code)->update(['status' => $active ? 'I' : 'A']);
        DivisionAccess::flush();

        return response()->json([
            'success' => true,
            'active' => ! $active,
            'message' => $active ? 'Division deactivated. Its records and reports stay as they are.' : 'Division is active again.',
        ]);
    }

    private function hasStock(Division $division): bool
    {
        return Product::withoutGlobalScope('division')->where('stock_loc_sys_code', $division->code)->exists();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->canManageSystem(), 403, 'Only an admin of the Overall division can manage divisions.');
    }

    private function validated(Request $request, ?Division $division = null): array
    {
        $request->merge([
            'user_code' => mb_strtoupper(trim((string) $request->input('user_code'))) ?: null,
        ]);

        $v = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'user_code' => [
                'nullable', 'string', 'max:15', 'regex:/^[A-Z0-9\-\/]+$/',
                Rule::unique('pStock_Location_Tab', 'stock_loc_user_code')
                    ->where('Comp_code', Division::COMPANY)
                    ->ignore($division?->stock_loc_sys_code, 'stock_loc_sys_code'),
            ],
            'add1' => ['nullable', 'string', 'max:50'],
            'add2' => ['nullable', 'string', 'max:50'],
            'add3' => ['nullable', 'string', 'max:50'],
            'is_main' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Please enter the division name.',
            'user_code.unique' => 'This division no. is already used.',
            'user_code.regex' => 'Use only letters, numbers, - or /.',
        ]);

        $up = fn ($s) => $s === null ? null : mb_strtoupper(trim($s));

        return [
            'stock_loc_desc' => $up($v['name']),
            'stock_loc_user_code' => $v['user_code'] ?? null,
            'add1' => $up($v['add1'] ?? null),
            'add2' => $up($v['add2'] ?? null),
            'add3' => $up($v['add3'] ?? null),
            'Is_Main' => (bool) ($v['is_main'] ?? false),
        ];
    }
}
