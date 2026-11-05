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
        Schema::table('ocean_export_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_export_hbls', 'freight_term')) {
                $table->string('freight_term')->nullable()->default('Prepaid')->after('cargo_type');
            }
            if (!Schema::hasColumn('ocean_export_hbls', 'hbl_freight')) {
                $table->string('hbl_freight')->nullable()->after('freight_payable_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_export_hbls', function (Blueprint $table) {
            if (Schema::hasColumn('ocean_export_hbls', 'freight_term')) {
                $table->dropColumn('freight_term');
            }
            if (Schema::hasColumn('ocean_export_hbls', 'hbl_freight')) {
                $table->dropColumn('hbl_freight');
            }
        });
    }
};
