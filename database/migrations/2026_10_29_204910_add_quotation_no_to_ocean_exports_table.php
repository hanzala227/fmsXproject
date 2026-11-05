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
        Schema::table('ocean_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_exports', 'quotation_no')) {
                $table->string('quotation_no')->nullable()->after('booking_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_exports', function (Blueprint $table) {
            if (Schema::hasColumn('ocean_exports', 'quotation_no')) {
                $table->dropColumn('quotation_no');
            }
        });
    }
};
