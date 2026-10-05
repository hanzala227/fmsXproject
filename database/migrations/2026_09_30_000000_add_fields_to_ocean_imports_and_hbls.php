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
        Schema::table('ocean_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_imports', 'transportation')) {
                $table->string('transportation')->nullable();
            }
        });

        Schema::table('ocean_import_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_import_hbls', 'hbl_freight')) {
                $table->string('hbl_freight')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_imports', function (Blueprint $table) {
            if (Schema::hasColumn('ocean_imports', 'transportation')) {
                $table->dropColumn('transportation');
            }
        });

        Schema::table('ocean_import_hbls', function (Blueprint $table) {
            if (Schema::hasColumn('ocean_import_hbls', 'hbl_freight')) {
                $table->dropColumn('hbl_freight');
            }
        });
    }
};
