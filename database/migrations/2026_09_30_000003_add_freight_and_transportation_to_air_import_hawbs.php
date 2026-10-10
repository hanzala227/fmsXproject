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
        // Table was renamed to air_import_hbls; apply to correct table if it exists
        $tableName = Schema::hasTable('air_import_hbls') ? 'air_import_hbls' : (Schema::hasTable('air_import_hawbs') ? 'air_import_hawbs' : null);
        if (!$tableName) return;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'freight')) {
                $table->string('freight')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'transportation')) {
                $table->string('transportation')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('air_import_hbls') ? 'air_import_hbls' : (Schema::hasTable('air_import_hawbs') ? 'air_import_hawbs' : null);
        if (!$tableName) return;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'freight')) {
                $table->dropColumn('freight');
            }
            if (Schema::hasColumn($tableName, 'transportation')) {
                $table->dropColumn('transportation');
            }
        });
    }
};
