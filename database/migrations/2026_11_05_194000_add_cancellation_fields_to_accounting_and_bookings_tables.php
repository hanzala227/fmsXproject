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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('invoices', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('invoices', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('void_date');
            }
            if (!Schema::hasColumn('payments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('payments', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });

        Schema::table('ocean_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_bookings', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('ocean_bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('ocean_bookings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });

        Schema::table('air_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('air_bookings', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('air_bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('air_bookings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancellation_reason']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancellation_reason']);
        });

        Schema::table('ocean_bookings', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancellation_reason']);
        });

        Schema::table('air_bookings', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancellation_reason']);
        });
    }
};
