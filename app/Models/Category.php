<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Old JMS table PItem_grp_tab: item categories (RING, CHAIN, BANGLE...). Shared by all divisions.
 *   item_grp_sys_code = 3 digit no. ('024'), part of every item code and barcode
 *   SH_Des            = short code printed on tags (RG, CH...)
 * Only one item type exists ('001' = LLJ), so it stays hidden.
 */
class Category extends Model
{
    public const COMPANY = '01';
    public const ITEM_TYPE = '001';

    // Barcodes use the last 2 digits of the category no., so 099 is the last safe number.
    public const MAX_CODE = 99;

    protected $table = 'PItem_grp_tab';
    protected $primaryKey = 'item_grp_sys_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('comp_code', self::COMPANY)
            ->where('item_type_sys_Code', self::ITEM_TYPE));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('comp_code', self::COMPANY)
            ->where('item_type_sys_Code', self::ITEM_TYPE)
            ->where('item_grp_sys_code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'item_grp_sys_code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->status) !== 'I';
    }

    public function getCodeAttribute(): string
    {
        return trim($this->item_grp_sys_code);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->item_grp_desc);
    }

    public function getShortCodeAttribute(): string
    {
        return trim((string) $this->SH_Des);
    }

    /** Active categories for dropdowns, plus any inactive one already in use. */
    public static function options(array $keep = [])
    {
        return static::query()
            ->where(fn ($q) => $q->where('status', '<>', 'I')->orWhereIn('item_grp_sys_code', $keep ?: ['']))
            ->orderBy('item_grp_desc')
            ->get();
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';

        return $q->where(fn (Builder $w) => $w->where('item_grp_desc', 'like', $like)
            ->orWhere('item_grp_sys_code', 'like', $like)
            ->orWhere('SH_Des', 'like', $like));
    }
}
