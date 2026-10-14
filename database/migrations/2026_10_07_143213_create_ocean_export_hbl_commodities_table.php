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
        Schema::create('ocean_export_hbl_commodities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hbl_id')->constrained('ocean_export_hbls')->onDelete('cascade');
            $table->string('hbl_no')->nullable();
            $table->text('commodity_desc')->nullable();
            $table->string('hts_code')->nullable();
            $table->string('container_no')->nullable();
            $table->string('po_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocean_export_hbl_commodities');
    }
};
