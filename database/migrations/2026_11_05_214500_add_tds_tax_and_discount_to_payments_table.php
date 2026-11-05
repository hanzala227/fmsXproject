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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'tds_tax')) {
                $table->decimal('tds_tax', 15, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('payments', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0.00)->after('tds_tax');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'discount')) {
                $table->dropColumn('discount');
            }
            if (Schema::hasColumn('payments', 'tds_tax')) {
                $table->dropColumn('tds_tax');
            }
        });
    }
};
