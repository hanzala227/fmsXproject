<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'charges',
            'ocean_import_charges',
            'ocean_export_charges',
            'air_import_charges',
            'air_export_charges',
            'truck_shipment_charges'
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (!Schema::hasColumn($t, 'party')) {
                        $table->string('party')->nullable()->after('type');
                    }
                    if (!Schema::hasColumn($t, 'party_name')) {
                        $table->string('party_name')->nullable()->after('party');
                    }
                    if (!Schema::hasColumn($t, 'sal')) {
                        $table->string('sal')->nullable()->after('party_name');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'charges',
            'ocean_import_charges',
            'ocean_export_charges',
            'air_import_charges',
            'air_export_charges',
            'truck_shipment_charges'
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    $cols = array_filter(['party', 'party_name', 'sal'], fn($c) => Schema::hasColumn($t, $c));
                    if (!empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
