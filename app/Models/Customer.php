<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Old JMS table PCust_Registration_Tab.
 * The old desktop app re-used some columns, so they are mapped to friendly names here:
 *   Contract_Person = title (Mr. / Mrs. / Miss.)
 *   Email           = customer type (Retails / Ledger)
 *   Fax             = religion group (Muslim / NON Muslim)
 *   Tel1 / Tel2     = mobile / other phone, stored as 94XXXXXXXXX
 */
class Customer extends Model
{
    public const COMPANY = '01';

    public const TITLES = ['Mr.', 'Mrs.', 'Miss.'];
    public const TYPES = ['Retails' => 'Retail', 'Ledger' => 'Ledger'];
    public const RELIGIONS = ['Muslim' => 'Muslim', 'NON Muslim' => 'Non-Muslim'];

    protected $table = 'PCust_Registration_Tab';
    protected $primaryKey = 'Cust_Sys_Code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Add_Date' => 'datetime',
        'Change_Date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('Comp_Code', self::COMPANY));
    }

    // Composite key in the DB: always update/delete with Comp_Code too
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('Comp_Code', self::COMPANY)
            ->where('Cust_Sys_Code', $this->getKeyForSaveQuery());
    }

    public function getRouteKeyName(): string
    {
        return 'Cust_Sys_Code';
    }

    public function isActive(): bool
    {
        return trim((string) $this->Status) !== 'I';
    }

    public function getCodeAttribute(): string
    {
        return trim($this->Cust_Sys_Code);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[trim((string) $this->Email)] ?? '';
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(trim((string) $this->Contract_Person) . ' ' . trim((string) $this->Cust_Name));
    }

    public function getAddressAttribute(): string
    {
        return collect([$this->Add1, $this->Add2, $this->Add3])->map(fn ($v) => trim((string) $v))->filter()->implode(', ');
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/[^\p{L}]+/u', trim((string) $this->Cust_Name)) ?: [];
        return strtoupper(collect($words)->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('')) ?: '?';
    }

    /** 94771234567 -> 077 123 4567 (for display) */
    public static function formatPhone(?string $phone): string
    {
        $p = preg_replace('/\D/', '', (string) $phone);
        if (strlen($p) === 11 && str_starts_with($p, '94')) {
            $p = '0' . substr($p, 2);
        }
        return strlen($p) === 10 ? substr($p, 0, 3) . ' ' . substr($p, 3, 3) . ' ' . substr($p, 6) : $p;
    }

    /** 077 123 4567 / 771234567 / +94 77 123 4567 -> 94771234567 (how the old system stores it) */
    public static function normalizePhone(?string $phone): ?string
    {
        $p = preg_replace('/\D/', '', (string) $phone);
        if ($p === '') {
            return null;
        }
        if (strlen($p) === 10 && str_starts_with($p, '0')) {
            return '94' . substr($p, 1);
        }
        if (strlen($p) === 9) {
            return '94' . $p;
        }
        return $p;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $w) use ($term) {
            $like = '%' . str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $term) . '%';
            $w->where('Cust_Name', 'like', $like)
                ->orWhere('Cust_Sys_Code', 'like', $like)
                ->orWhere('Add1', 'like', $like)
                ->orWhere('Add2', 'like', $like)
                ->orWhere('Add3', 'like', $like);

            // let people search phones the way they say them: 0771234567 or 771234567
            $digits = ltrim(preg_replace('/\D/', '', $term), '0');
            if (strlen($digits) >= 3) {
                $w->orWhere('Tel1', 'like', "%{$digits}%")->orWhere('Tel2', 'like', "%{$digits}%");
            }
        });
    }
}
