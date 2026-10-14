<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('charges', 'mbl_no')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->string('mbl_no')->nullable()->after('invoice_date');
            });
        }

        if (!Schema::hasColumn('invoices', 'mbl_no')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('mbl_no')->nullable()->after('internal_remark');
            });
        }
        
        if (!Schema::hasColumn('invoices', 'remark')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->text('remark')->nullable()->after('mbl_no');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('charges', 'mbl_no')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->dropColumn('mbl_no');
            });
        }

        if (Schema::hasColumn('invoices', 'mbl_no')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('mbl_no');
            });
        }

        if (Schema::hasColumn('invoices', 'remark')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('remark');
            });
        }
    }
};
