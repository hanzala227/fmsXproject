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
        Schema::table('warehouse_shippings', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_shippings', 'items_data')) {
                $table->json('items_data')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_shippings', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_shippings', 'items_data')) {
                $table->dropColumn('items_data');
            }
        });
    }
};
