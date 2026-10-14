<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class NextCode
{
    /**
     * Next zero-padded code for the old JMS tables (e.g. 000221 -> 000222).
     * Call inside DB::transaction() so two users saving at once never get the same code.
     */
    public static function for(string $table, string $column, int $length, array $where = []): string
    {
        $max = DB::table($table)
            ->where($where)
            ->lockForUpdate()
            ->max($column);

        return str_pad(((int) $max) + 1, $length, '0', STR_PAD_LEFT);
    }
}
