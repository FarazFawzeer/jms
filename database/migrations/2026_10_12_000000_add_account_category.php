<?php

// Account types: re-uses the old PAdd_Ded_Types_tab (income / expense heads) and adds one column,
// Acc_Category = Income / Expense / Both. The old `operation` column cannot be trusted for this
// (SALARY, ELECTRICITY... are all 'A'), so the new column is filled once from how each head was
// actually used in POther_InDed_tab. Add-only and guarded, safe on any fresh restore.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('PAdd_Ded_Types_tab', 'Acc_Category')) {
            Schema::table('PAdd_Ded_Types_tab', function (Blueprint $table) {
                $table->string('Acc_Category', 10)->nullable();
            });
        }

        // Only heads that have no category yet: used only for money out = Expense, only money in = Income,
        // both ways = Both. Heads never used: Expense when the old operation is D, otherwise Both
        // (operation A is set on almost every head, so it says nothing). Fix any of them on the screen.
        DB::statement("
            UPDATE t SET Acc_Category =
                CASE
                    WHEN u.ins > 0 AND u.outs > 0 THEN 'Both'
                    WHEN u.outs > 0 THEN 'Expense'
                    WHEN u.ins > 0 THEN 'Income'
                    WHEN t.operation = 'D' THEN 'Expense'
                    ELSE 'Both'
                END
            FROM PAdd_Ded_Types_tab t
            OUTER APPLY (
                SELECT SUM(CASE WHEN e.Type = 'A' THEN 1 ELSE 0 END) AS ins,
                       SUM(CASE WHEN e.Type = 'D' THEN 1 ELSE 0 END) AS outs
                FROM POther_InDed_tab e
                WHERE e.comp_code = t.comp_code AND e.add_ded_sys_code = t.add_ded_sys_code
            ) u
            WHERE t.Acc_Category IS NULL
        ");
    }

    public function down(): void
    {
        if (Schema::hasColumn('PAdd_Ded_Types_tab', 'Acc_Category')) {
            Schema::table('PAdd_Ded_Types_tab', function (Blueprint $table) {
                $table->dropColumn('Acc_Category');
            });
        }
    }
};
