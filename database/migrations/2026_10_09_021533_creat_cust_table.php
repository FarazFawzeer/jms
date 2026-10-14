<?php

// EXAMPLE / PATTERN for upgrading the old JMS tables. Copy, rename and edit per change.
// Rules: only ADD (never rename or drop old columns), new columns are nullable or have a default,
// and every step is guarded so it is safe to run again on a fresh restore of the client's backup.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) New column on an existing table
        if (! Schema::hasColumn('PCust_Registration_Tab', 'Mobile_WhatsApp')) {
            Schema::table('PCust_Registration_Tab', function (Blueprint $table) {
                $table->string('Mobile_WhatsApp', 30)->nullable();
            });
        }

        // 2) Brand-new table for a feature the old system did not have
        if (! Schema::hasTable('jms_customer_documents')) {
            Schema::create('jms_customer_documents', function (Blueprint $table) {
                $table->id();
                $table->char('comp_code', 2)->default('01');
                $table->char('Cust_Sys_Code', 6)->index(); // -> PCust_Registration_Tab
                $table->string('title');
                $table->string('file_path');
                $table->foreignId('created_by')->nullable(); // -> users.id
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jms_customer_documents');

        if (Schema::hasColumn('PCust_Registration_Tab', 'Mobile_WhatsApp')) {
            Schema::table('PCust_Registration_Tab', function (Blueprint $table) {
                $table->dropColumn('Mobile_WhatsApp');
            });
        }
    }
};
