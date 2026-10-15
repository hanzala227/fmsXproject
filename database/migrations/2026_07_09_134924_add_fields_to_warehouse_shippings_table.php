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
            if (!Schema::hasColumn('warehouse_shippings', 'color')) {
                $table->string('color', 20)->nullable();
            }
            if (!Schema::hasColumn('warehouse_shippings', 'memos_data')) {
                $table->json('memos_data')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_shippings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('warehouse_shippings', 'color')) $columns[] = 'color';
            if (Schema::hasColumn('warehouse_shippings', 'memos_data')) $columns[] = 'memos_data';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
