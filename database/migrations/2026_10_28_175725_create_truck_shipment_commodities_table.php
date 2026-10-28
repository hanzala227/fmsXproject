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
        Schema::create('truck_shipment_commodities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('truck_shipment_id');
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('description')->nullable();
            $table->string('hts_code')->nullable();
            $table->string('po_no')->nullable();
            $table->decimal('pkg_qty', 12, 2)->nullable();
            $table->decimal('weight', 12, 3)->nullable();
            $table->decimal('cbm', 12, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_shipment_commodities');
    }
};
