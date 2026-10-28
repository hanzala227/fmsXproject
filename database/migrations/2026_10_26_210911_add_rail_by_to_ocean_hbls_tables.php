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
        if (Schema::hasTable('ocean_import_hbls') && !Schema::hasColumn('ocean_import_hbls', 'rail_by')) {
            Schema::table('ocean_import_hbls', function (Blueprint $table) {
                $table->string('rail_by')->nullable()->after('is_rail');
            });
        }

        if (Schema::hasTable('ocean_export_hbls') && !Schema::hasColumn('ocean_export_hbls', 'rail_by')) {
            Schema::table('ocean_export_hbls', function (Blueprint $table) {
                $table->string('rail_by')->nullable()->after('is_rail');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ocean_import_hbls') && Schema::hasColumn('ocean_import_hbls', 'rail_by')) {
            Schema::table('ocean_import_hbls', function (Blueprint $table) {
                $table->dropColumn('rail_by');
            });
        }

        if (Schema::hasTable('ocean_export_hbls') && Schema::hasColumn('ocean_export_hbls', 'rail_by')) {
            Schema::table('ocean_export_hbls', function (Blueprint $table) {
                $table->dropColumn('rail_by');
            });
        }
    }
};
