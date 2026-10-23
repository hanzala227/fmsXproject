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
        Schema::table('air_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('air_bookings', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_blocked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_bookings', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
};
