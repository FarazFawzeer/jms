<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Old JMS tables pSuppliers_Register_Mast_tab (supplier) + pSuppliers_Register_Det_tab (categories they supply).
 *   Supliers_Sys_Code   = system no. (000001), used by GRNs, never changes
 *   Suppliers_User_Code = code the user can type (defaults to the system no.)
 *   Tel1 / Tel2         = mobile / other phone, stored as 94XXXXXXXXX like customers
 *   Cus_Balance         = read-only, maintained by GRNs / payments
 */
class Supplier extends Model
{
    public const COMPANY = '01';
    public const ITEM_TYPE = '001'; // only item type in the DB ("LLJ")

    public const DETAIL_TABLE = 'pSuppliers_Register_Det_tab';
    public const CATEGORY_TABLE = 'PItem_grp_tab';

    protected $table = 'pSuppliers_Register_Mast_tab';
    protected $primaryKey = 'Supliers_Sys_Code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Add_date' => 'datetime',
        'Change_Date' => 'datetime',
        'Cus_Balance' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('Comp_Code', self::COMPANY));
    }

    // Composite key in the DB: always update with Comp_Code too
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('Comp_Code', self::COMPANY)
            ->where('Supliers_Sys_Code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'Supliers_Sys_Code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->status) !== 'I';
    }

    public function getCodeAttribute(): string
    {
        return trim($this->Supliers_Sys_Code);
    }

    public function getUserCodeAttribute(): string
    {
        return trim((string) $this->Suppliers_User_Code);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->Suppliers_Name);
    }

    public function getAddressAttribute(): string
    {
        return collect([$this->Add1, $this->Add2, $this->Add3])->map(fn ($v) => trim((string) $v))->filter()->implode(', ');
    }

    /** Category list for the form: active ones, plus any inactive one this supplier already has. */
    public static function categoryOptions(array $keep = [])
    {
        return DB::table(self::CATEGORY_TABLE)
            ->where('comp_code', self::COMPANY)
            ->where('item_type_sys_Code', self::ITEM_TYPE)
            ->where(fn ($q) => $q->where('status', '<>', 'I')->orWhereIn('item_grp_sys_code', $keep ?: ['']))
            ->orderBy('item_grp_desc')
            ->get(['item_grp_sys_code as code', 'item_grp_desc as name']);
    }

    /** Category codes this supplier provides, e.g. ['024', '027']. */
    public function categoryCodes(): array
    {
        if (! $this->exists) {
            return [];
        }

        return DB::table(self::DETAIL_TABLE)
            ->where('Comp_Code', self::COMPANY)
            ->where('Supliers_Sys_Code', $this->Supliers_Sys_Code)
            ->pluck('item_grp_sys_code')
            ->map(fn ($c) => trim($c))
            ->all();
    }

    /** Category names per supplier for the list page: ['000001' => 'CHAIN, RING'] */
    public static function categoryNamesFor(array $codes): array
    {
        if (! $codes) {
            return [];
        }

        return DB::table(self::DETAIL_TABLE . ' as d')
            ->join(self::CATEGORY_TABLE . ' as g', function ($j) {
                $j->on('g.comp_code', '=', 'd.Comp_Code')
                    ->on('g.item_type_sys_Code', '=', 'd.item_type_sys_Code')
                    ->on('g.item_grp_sys_code', '=', 'd.item_grp_sys_code');
            })
            ->where('d.Comp_Code', self::COMPANY)
            ->whereIn('d.Supliers_Sys_Code', $codes)
            ->orderBy('g.item_grp_desc')
            ->get(['d.Supliers_Sys_Code as supplier', 'g.item_grp_desc as name'])
            ->groupBy(fn ($r) => trim($r->supplier))
            ->map(fn ($rows) => $rows->pluck('name')->map(fn ($n) => trim($n))->implode(', '))
            ->all();
    }

    /** Replace the supplier's category rows (link table only, no other data depends on it). */
    public function syncCategories(array $codes): void
    {
        DB::table(self::DETAIL_TABLE)
            ->where('Comp_Code', self::COMPANY)
            ->where('Supliers_Sys_Code', $this->Supliers_Sys_Code)
            ->delete();

        $rows = collect($codes)->unique()->map(fn ($code) => [
            'Comp_Code' => self::COMPANY,
            'Supliers_Sys_Code' => $this->Supliers_Sys_Code,
            'item_grp_sys_code' => $code,
            'item_type_sys_Code' => self::ITEM_TYPE,
        ])->values()->all();

        if ($rows) {
            DB::table(self::DETAIL_TABLE)->insert($rows);
        }
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $w) use ($term) {
            $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';
            $w->where('Suppliers_Name', 'like', $like)
                ->orWhere('Contract_Person', 'like', $like)
                ->orWhere('Supliers_Sys_Code', 'like', $like)
                ->orWhere('Suppliers_User_Code', 'like', $like)
                ->orWhere('Add1', 'like', $like)
                ->orWhere('Add3', 'like', $like);

            // phones the way people say them: 0771234567 or 771234567
            $digits = ltrim(preg_replace('/\D/', '', $term), '0');
            if (strlen($digits) >= 3) {
                $w->orWhere('Tel1', 'like', "%{$digits}%")->orWhere('Tel2', 'like', "%{$digits}%");
            }
        });
    }
}
