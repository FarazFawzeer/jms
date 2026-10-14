<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Old JMS table PAdd_Ded_Types_tab: income / expense heads (SALARY, ELECTRICITY, GOLD TESTING...).
 * Shared by all divisions; only the entries (AccountEntry) belong to a division.
 *   add_ded_sys_code = 4 digit no. ('0028'), also TR_Master.Acc_Code
 *   Acc_Category     = Income / Expense / Both (new column, see migration 2026_10_12_000000)
 *   operation        = old app's A / D, kept in step for the old desktop app
 *   payslip_loc      = display order in the old app
 */
class AccountType extends Model
{
    public const COMPANY = '01';

    public const CATEGORIES = [
        'Income' => ['Income', 'badge-soft-success'],
        'Expense' => ['Expense', 'badge-soft-danger'],
        'Both' => ['Income & Expense', 'badge-soft-info'],
    ];

    protected $table = 'PAdd_Ded_Types_tab';
    protected $primaryKey = 'add_ded_sys_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('PAdd_Ded_Types_tab.comp_code', self::COMPANY));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('comp_code', self::COMPANY)
            ->where('add_ded_sys_code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'add_ded_sys_code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->status) !== 'I';
    }

    public function getCodeAttribute(): string
    {
        return trim((string) $this->add_ded_sys_code);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->add_ded_desc);
    }

    /** Income / Expense / Both (falls back to the old operation column before the migration has run). */
    public function getCategoryAttribute(): string
    {
        $category = trim((string) $this->Acc_Category);
        if (isset(self::CATEGORIES[$category])) {
            return $category;
        }

        return trim((string) $this->operation) === 'D' ? 'Expense' : 'Income';
    }

    public function getCategoryLabelAttribute(): array
    {
        return self::CATEGORIES[$this->category];
    }

    /** Can this head be used for money in (A) / money out (D)? */
    public function allows(string $direction): bool
    {
        return $this->category === 'Both'
            || ($direction === AccountEntry::INCOME && $this->category === 'Income')
            || ($direction === AccountEntry::EXPENSE && $this->category === 'Expense');
    }

    /** Active heads for dropdowns, plus any inactive one already in use. */
    public static function options(array $keep = [])
    {
        return static::query()
            ->where(fn ($q) => $q->where('status', '<>', 'I')->orWhereIn('add_ded_sys_code', $keep ?: ['']))
            ->orderBy('add_ded_desc')
            ->get();
    }

    /** Heads that can be picked for money in (A) or money out (D). */
    public function scopeUsableFor(Builder $q, ?string $direction): Builder
    {
        return match ($direction) {
            AccountEntry::INCOME => $q->whereIn('Acc_Category', ['Income', 'Both']),
            AccountEntry::EXPENSE => $q->whereIn('Acc_Category', ['Expense', 'Both']),
            default => $q,
        };
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';

        return $q->where(fn (Builder $w) => $w->where('add_ded_desc', 'like', $like)
            ->orWhere('add_ded_sys_code', 'like', $like));
    }
}
