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
        Schema::table('ocean_import_hbl_commodities', function (Blueprint $table) {
            $table->string('hbl_no')->nullable()->after('hbl_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_import_hbl_commodities', function (Blueprint $table) {
            $table->dropColumn('hbl_no');
        });
    }
};
