<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDivision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Old JMS table PItems_tab: one row per physical piece (tag), kept in one division.
 *   full_item_code = item type + category + item no. ('001' + '024' + '0601')
 *   BarCode        = last 2 digits of category + item no. ('240601')
 *   unit_code      = karat / metal (MUnitOfMesure: 22 kt, 18 kt, Silver...)
 *   item_size      = weight in grams, Stone_weight = stone weight in grams
 *   SoldType       = N in stock, Y sold (other letters come from the old app)
 *   TagNo          = supplier code (all old pieces are 'LLJ')
 *   U_Price        = the old app stores the weight here too, kept the same
 */
class Product extends Model
{
    use BelongsToDivision;

    public const COMPANY = '01';
    public const ITEM_TYPE = '001';

    public const SOLD_TYPES = [
        'N' => ['In Stock', 'badge-soft-success'],
        'Y' => ['Sold', 'badge-soft-secondary'],
        'R' => ['Returned', 'badge-soft-warning'],
        'T' => ['Code T', 'badge-soft-info'],
        'A' => ['Code A', 'badge-soft-info'],
    ];

    protected $table = 'PItems_tab';
    protected $primaryKey = 'full_item_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'item_size' => 'float',
        'Stone_weight' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('PItems_tab.comp_code', self::COMPANY));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('comp_code', self::COMPANY)
            ->where('full_item_code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'full_item_code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->status) !== 'I';
    }

    public function inStock(): bool
    {
        return trim((string) $this->SoldType) === 'N';
    }

    /** Weight, karat etc. can change only while the piece is unsold. */
    public function isEditable(): bool
    {
        return $this->inStock();
    }

    public function getCodeAttribute(): string
    {
        return trim($this->full_item_code);
    }

    public function getBarcodeNoAttribute(): string
    {
        return trim((string) $this->attributes['BarCode'] ?? '');
    }

    public function getCategoryCodeAttribute(): string
    {
        return trim((string) $this->item_grp_sys_code);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->item_desc);
    }

    public function getSoldLabelAttribute(): array
    {
        $type = trim((string) $this->SoldType);

        return self::SOLD_TYPES[$type] ?? ['Code ' . $type, 'badge-soft-info'];
    }

    /** Karat / metal list (MUnitOfMesure), shared by all divisions: ['01' => '22 kt', ...] */
    public static function unitOptions(): array
    {
        return DB::table('MUnitOfMesure')
            ->where('comp_code', self::COMPANY)
            ->orderBy('unit_code')
            ->pluck('unit_desc', 'unit_code')
            ->mapWithKeys(fn ($name, $code) => [trim($code) => trim((string) $name)])
            ->all();
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';

        return $q->where(fn (Builder $w) => $w->where('BarCode', 'like', $like)
            ->orWhere('full_item_code', 'like', $like)
            ->orWhere('item_desc', 'like', $like));
    }

    /** Status filter used on the list page. */
    public function scopeStockStatus(Builder $q, string $status): Builder
    {
        return match ($status) {
            'stock' => $q->where('SoldType', 'N')->where('status', '<>', 'I'),
            'sold' => $q->where('SoldType', '<>', 'N'),
            'inactive' => $q->where('status', 'I'),
            default => $q,
        };
    }
}
