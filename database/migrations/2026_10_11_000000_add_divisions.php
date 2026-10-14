<?php

// Divisions: re-uses the old pStock_Location_Tab (every stock / sales / GRN / expense table already
// stores the division in stock_loc_sys_code). Add-only and guarded, safe on any fresh restore.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 = Overall division: its users see every division's stock, sales and reports
        if (! Schema::hasColumn('pStock_Location_Tab', 'Is_Main')) {
            Schema::table('pStock_Location_Tab', function (Blueprint $table) {
                $table->boolean('Is_Main')->default(false);
            });
        }

        // The user's division (already in the JMS users table; added here for older copies of it)
        if (! Schema::hasColumn('users', 'stock_loc_sys_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('stock_loc_sys_code', 6)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pStock_Location_Tab', 'Is_Main')) {
            Schema::table('pStock_Location_Tab', function (Blueprint $table) {
                $table->dropColumn('Is_Main');
            });
        }
    }
};
