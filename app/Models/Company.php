<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Old JMS table Mcomp_tab: one row for the shop (comp_code '01').
 * Shared by all divisions. Pu_Rate / Sel_Rate are the gold buying / selling rates.
 * Columns the old app uses for other things (closed_year/month, Percentage, Fax) are left untouched.
 */
class Company extends Model
{
    public const COMPANY = '01';

    protected $table = 'Mcomp_tab';
    protected $primaryKey = 'comp_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Pu_Rate' => 'float',
        'Sel_Rate' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', fn (Builder $q) => $q->where('comp_code', self::COMPANY));
    }

    public static function current(): self
    {
        return static::first() ?? new static(['comp_code' => self::COMPANY, 'status' => 'A']);
    }
}
