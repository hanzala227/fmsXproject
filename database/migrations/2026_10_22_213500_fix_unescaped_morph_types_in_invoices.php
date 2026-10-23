<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappings = [
            'AppModelsOceanImport' => 'App\\Models\\OceanImport',
            'AppModelsOceanExport' => 'App\\Models\\OceanExport',
            'AppModelsAirImport' => 'App\\Models\\AirImport',
            'AppModelsAirExport' => 'App\\Models\\AirExport',
            'AppModelsTruckShipment' => 'App\\Models\\TruckShipment',
            'AppModelsWarehouse' => 'App\\Models\\Warehouse',
            'AppModelsHouseBillOfLading' => 'App\\Models\\HouseBillOfLading',
            'AppModelsBillOfLading' => 'App\\Models\\BillOfLading',
            'AppModelsOceanBooking' => 'App\\Models\\OceanBooking',
            'AppModelsAirBooking' => 'App\\Models\\AirBooking',
            'AppModelsBooking' => 'App\\Models\\Booking',
            'AppModelsQuotation' => 'App\\Models\\Quotation',
        ];

        foreach ($mappings as $bad => $good) {
            DB::table('invoices')->where('invoiceable_type', $bad)->update(['invoiceable_type' => $good]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal necessary
    }
};

