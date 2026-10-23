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
        // 1. Add columns to ocean_export_hbls
        Schema::table('ocean_export_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_export_hbls', 'po_mapping_type')) {
                $table->string('po_mapping_type')->default('container');
            }
            if (!Schema::hasColumn('ocean_export_hbls', 'hbl_mark')) {
                $table->text('hbl_mark')->nullable();
            }
            if (!Schema::hasColumn('ocean_export_hbls', 'hbl_description')) {
                $table->text('hbl_description')->nullable();
            }
        });

        // 2. Add columns to pivot table ocean_export_container_hbl
        Schema::table('ocean_export_container_hbl', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_export_container_hbl', 'pkg_qty')) {
                $table->integer('pkg_qty')->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'pkg_unit')) {
                $table->string('pkg_unit')->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'weight_kg')) {
                $table->decimal('weight_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'weight_unit')) {
                $table->string('weight_unit')->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'measure_cbm')) {
                $table->decimal('measure_cbm', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'measure_unit')) {
                $table->string('measure_unit')->nullable();
            }
            if (!Schema::hasColumn('ocean_export_container_hbl', 'po_no')) {
                $table->string('po_no')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_export_hbls', function (Blueprint $table) {
            $table->dropColumn(['po_mapping_type', 'hbl_mark', 'hbl_description']);
        });

        Schema::table('ocean_export_container_hbl', function (Blueprint $table) {
            $table->dropColumn([
                'pkg_qty', 'pkg_unit', 'weight_kg', 'weight_unit',
                'measure_cbm', 'measure_unit', 'po_no'
            ]);
        });
    }
};
