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
        Schema::table('warehouse_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('truck_shipment_id')->nullable()->after('company_id');
            $table->index('truck_shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_receipts', function (Blueprint $table) {
            $table->dropIndex(['truck_shipment_id']);
            $table->dropColumn('truck_shipment_id');
        });
    }
};
