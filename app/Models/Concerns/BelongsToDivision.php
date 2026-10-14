<?php

namespace App\Models\Concerns;

use App\Support\Division;
use Illuminate\Database\Eloquent\Builder;

/**
 * Add to any model whose rows belong to a division (stock, sales, GRN, expenses...).
 * Every query is filtered to the logged-in user's division automatically, and new rows
 * get the user's division when none is set. Overall users see every division.
 *
 * The old JMS tables already carry the division in `stock_loc_sys_code`.
 * A model can use another column with: public const DIVISION_COLUMN = 'xxx';
 */
trait BelongsToDivision
{
    public static function bootBelongsToDivision(): void
    {
        static::addGlobalScope('division', function (Builder $q) {
            Division::apply($q, $q->getModel()->qualifyColumn(static::divisionColumn()));
        });

        static::creating(function ($model) {
            $column = static::divisionColumn();
            if (trim((string) $model->{$column}) === '') {
                $model->{$column} = Division::forSaving();
            }
        });
    }

    public static function divisionColumn(): string
    {
        return defined(static::class . '::DIVISION_COLUMN') ? static::DIVISION_COLUMN : 'stock_loc_sys_code';
    }

    public function getDivisionCodeAttribute(): string
    {
        return trim((string) $this->{static::divisionColumn()});
    }

    public function getDivisionNameAttribute(): string
    {
        return Division::names()[$this->division_code] ?? $this->division_code;
    }
}
