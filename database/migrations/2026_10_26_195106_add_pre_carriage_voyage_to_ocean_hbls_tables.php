<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ocean_import_hbls') && !Schema::hasColumn('ocean_import_hbls', 'pre_carriage_voyage')) {
            Schema::table('ocean_import_hbls', function (Blueprint $table) {
                $table->string('pre_carriage_voyage')->nullable()->after('pre_carriage_by');
            });
        }

        if (Schema::hasTable('ocean_export_hbls') && !Schema::hasColumn('ocean_export_hbls', 'pre_carriage_voyage')) {
            Schema::table('ocean_export_hbls', function (Blueprint $table) {
                $table->string('pre_carriage_voyage')->nullable()->after('pre_carriage_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ocean_import_hbls') && Schema::hasColumn('ocean_import_hbls', 'pre_carriage_voyage')) {
            Schema::table('ocean_import_hbls', function (Blueprint $table) {
                $table->dropColumn('pre_carriage_voyage');
            });
        }

        if (Schema::hasTable('ocean_export_hbls') && Schema::hasColumn('ocean_export_hbls', 'pre_carriage_voyage')) {
            Schema::table('ocean_export_hbls', function (Blueprint $table) {
                $table->dropColumn('pre_carriage_voyage');
            });
        }
    }
};
