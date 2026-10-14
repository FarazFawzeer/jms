<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Divisions = old JMS stock locations (pStock_Location_Tab).
 * Every stock / sales / GRN / expense table already stores the division in stock_loc_sys_code,
 * so per-division reports work on the old data without changing it.
 *   stock_loc_sys_code  = system no. used by all other tables ('1', '2', ...), never changes
 *   stock_loc_user_code = number shown to users ('001', '002', ...)
 *   Is_Main             = new column: 1 = Overall division (its users see every division)
 */
class Division extends Model
{
    public const COMPANY = '01';

    protected $table = 'pStock_Location_Tab';
    protected $primaryKey = 'stock_loc_sys_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Is_Main' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('Comp_code', self::COMPANY));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('Comp_code', self::COMPANY)
            ->where('stock_loc_sys_code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'stock_loc_sys_code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->status) !== 'I';
    }

    public function getCodeAttribute(): string
    {
        return trim($this->stock_loc_sys_code);
    }

    public function getUserCodeAttribute(): string
    {
        return trim((string) $this->stock_loc_user_code);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->stock_loc_desc);
    }

    public function getAddressAttribute(): string
    {
        return collect([$this->add1, $this->add2, $this->add3])->map(fn ($v) => trim((string) $v))->filter()->implode(', ');
    }

    /**
     * Next system no. The old data stores it unpadded ('1'), so compare as numbers.
     * Call inside DB::transaction().
     */
    public static function nextCode(): string
    {
        $max = DB::table('pStock_Location_Tab')
            ->where('Comp_code', self::COMPANY)
            ->lockForUpdate()
            ->pluck('stock_loc_sys_code')
            ->map(fn ($c) => (int) trim($c))
            ->max();

        return (string) (((int) $max) + 1);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';

        return $q->where(fn (Builder $w) => $w->where('stock_loc_desc', 'like', $like)
            ->orWhere('stock_loc_user_code', 'like', $like)
            ->orWhere('add1', 'like', $like)
            ->orWhere('add3', 'like', $like));
    }
}
