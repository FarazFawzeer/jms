<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDivision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Old JMS table POther_InDed_tab: one income or expense entry, kept in one division.
 *   Ent_No           = 7 digit entry no. ('0001022'), one running number for all divisions
 *   Type             = A money in (income) / D money out (expense)
 *   add_ded_sys_code = account type (PAdd_Ded_Types_tab)
 *   emp_sys_code     = paid to / received from: a customer code ('000001' = CASH), name copied to Emp_Name
 *   misc_desc        = description, P_Type = payment mode, PStatus = A active / I cancelled
 *
 * Like the old app, every active entry is also posted to the cash book TR_Master
 * (income = TR type 2 "Receipt" debit, expense = TR type 3 "Voucher" credit), so the old
 * B/F and C/F cash balances keep working. A cancelled entry has no TR_Master row.
 */
class AccountEntry extends Model
{
    use BelongsToDivision;

    public const COMPANY = '01';
    public const INCOME = 'A';
    public const EXPENSE = 'D';
    public const CASH_PARTY = '000001';

    public const DIRECTIONS = [
        self::INCOME => ['Income', 'badge-soft-success'],
        self::EXPENSE => ['Expense', 'badge-soft-danger'],
    ];

    public const PAY_MODES = ['Cash' => 'Cash', 'Bank' => 'Bank Transfer', 'Cheque' => 'Cheque', 'Card' => 'Card'];

    protected $table = 'POther_InDed_tab';
    protected $primaryKey = 'Ent_No';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Ent_date' => 'datetime',
        'amount' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('POther_InDed_tab.comp_code', self::COMPANY));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('comp_code', self::COMPANY)
            ->where('Ent_No', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'Ent_No';
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'add_ded_sys_code', 'add_ded_sys_code');
    }

    public function isActive(): bool
    {
        return trim((string) $this->PStatus) !== 'I';
    }

    public function getEntryNoAttribute(): string
    {
        return trim((string) $this->Ent_No);
    }

    public function getDirectionAttribute(): string
    {
        return trim((string) $this->getAttribute('Type')) === self::EXPENSE ? self::EXPENSE : self::INCOME;
    }

    public function getDirectionLabelAttribute(): array
    {
        return self::DIRECTIONS[$this->direction];
    }

    public function getAccountCodeAttribute(): string
    {
        return trim((string) $this->add_ded_sys_code);
    }

    public function getPartyCodeAttribute(): string
    {
        return trim((string) $this->emp_sys_code);
    }

    public function getPartyNameAttribute(): string
    {
        return trim((string) $this->Emp_Name);
    }

    public function getDescriptionTextAttribute(): string
    {
        return trim((string) $this->misc_desc);
    }

    public function getPayModeAttribute(): string
    {
        return trim((string) $this->P_Type) ?: 'Cash';
    }

    /** The entry's cash book row in TR_Master (null for old entries the old app never posted). */
    public function posting(): ?object
    {
        $column = $this->direction === self::EXPENSE ? 'CRAmount' : 'DBAmount';

        return DB::table('TR_Master')
            ->where('Comp_code', self::COMPANY)
            ->where('Doc_Sys_Code', $this->getOriginal('Ent_No') ?? $this->Ent_No)
            ->where('Acc_Code', $this->getOriginal('add_ded_sys_code') ?? $this->add_ded_sys_code)
            ->whereIn('TR_Type_Code', ['2', '3'])
            ->whereRaw("ABS({$column} - ?) < 0.005", [(float) ($this->getOriginal('amount') ?? $this->amount)])
            ->orderBy('AutoNo')
            ->first();
    }

    /** Write (or rewrite) this entry's cash book row. Call inside a DB transaction. */
    public function post(?object $old = null): void
    {
        $income = $this->direction === self::INCOME;
        $row = [
            'Comp_code' => self::COMPANY,
            'Year_No' => $this->Ent_date->format('Y'),
            'Doc_Sys_Code' => $this->Ent_No,
            'Acc_Code' => $this->add_ded_sys_code,
            'TR_Sin_Code' => $income ? '2' : '3',
            'TR_Type_Code' => $income ? '2' : '3',
            'DBAmount' => $income ? $this->amount : 0,
            'CRAmount' => $income ? 0 : $this->amount,
            'TR_Date' => $this->Ent_date->format('Y-m-d'),
            // the old app keeps the party name in AddUser and the description in PaymantMode
            'AddUser' => mb_substr($this->party_name, 0, 50),
            'TR_Naration' => $income ? 'Receipt' : 'Voucher',
            'PaymantMode' => mb_substr($this->description_text, 0, 50),
            'Pay_Type' => $this->pay_mode,
            'stock_loc_sys_code' => $this->stock_loc_sys_code,
        ];

        if ($old) {
            DB::table('TR_Master')->where('AutoNo', $old->AutoNo)->update($row);

            return;
        }

        $next = (int) DB::table('TR_Master')->lockForUpdate()->max('AutoNo') + 1;
        DB::table('TR_Master')->insert(['AutoNo' => $next] + $row);
    }

    /** Remove the cash book row (when the entry is cancelled). */
    public static function unpost(?object $old): void
    {
        if ($old) {
            DB::table('TR_Master')->where('AutoNo', $old->AutoNo)->delete();
        }
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';

        return $q->where(fn (Builder $w) => $w->where('misc_desc', 'like', $like)
            ->orWhere('Emp_Name', 'like', $like)
            ->orWhere('Ent_No', 'like', $like));
    }

    public function scopeEntryStatus(Builder $q, string $status): Builder
    {
        return match ($status) {
            'active' => $q->where(fn ($w) => $w->where('PStatus', '<>', 'I')->orWhereNull('PStatus')),
            'cancelled' => $q->where('PStatus', 'I'),
            default => $q,
        };
    }
}
