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
        Schema::table('air_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('air_exports', 'cargo_type')) {
                $table->string('cargo_type')->nullable()->default('GENERAL CARGO')->after('sales_type');
            }
            if (!Schema::hasColumn('air_exports', 'display_unit')) {
                $table->string('display_unit')->nullable()->default('BOTH')->after('cargo_type');
            }
            if (!Schema::hasColumn('air_exports', 'other_charges')) {
                $table->json('other_charges')->nullable()->after('route_data');
            }
            if (!Schema::hasColumn('air_exports', 'accounting_info')) {
                $table->json('accounting_info')->nullable()->after('other_charges');
            }
            if (!Schema::hasColumn('air_exports', 'commodities')) {
                $table->json('commodities')->nullable()->after('accounting_info');
            }
        });

        Schema::table('air_export_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('air_export_hbls', 'commodities')) {
                $table->json('commodities')->nullable()->after('commodity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_exports', function (Blueprint $table) {
            $cols = ['cargo_type', 'display_unit', 'other_charges', 'accounting_info', 'commodities'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('air_exports', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('air_export_hbls', function (Blueprint $table) {
            if (Schema::hasColumn('air_export_hbls', 'commodities')) {
                $table->dropColumn('commodities');
            }
        });
    }
};

