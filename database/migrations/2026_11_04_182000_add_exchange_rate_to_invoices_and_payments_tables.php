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
        if (!Schema::hasColumn('invoices', 'exchange_rate')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('exchange_rate', 12, 6)->default(1.0)->after('currency_id');
            });
        }

        if (!Schema::hasColumn('payments', 'exchange_rate')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('exchange_rate', 12, 6)->default(1.0)->after('currency_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'exchange_rate')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('exchange_rate');
            });
        }

        if (Schema::hasColumn('payments', 'exchange_rate')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('exchange_rate');
            });
        }
    }
};
