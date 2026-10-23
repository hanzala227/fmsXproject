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
        Schema::table('warehouse_automobiles', function (Blueprint $table) {
            $table->string('tag_no')->nullable()->after('vin_no');
            $table->string('vehicle_state')->nullable()->after('office_id');
            $table->string('condition')->nullable()->after('internal_remark');
            $table->string('key_number')->nullable();
            $table->string('fuel')->nullable();
            $table->string('tire_size_front')->nullable();
            $table->string('tire_size_rear')->nullable();
            $table->string('mileage')->nullable();
            $table->boolean('w_sticker')->default(false);
            $table->boolean('remote_control')->default(false);
            $table->boolean('headphone')->default(false);
            $table->boolean('owners_manual')->default(false);
            $table->boolean('cd_player')->default(false);
            $table->boolean('cd_changer')->default(false);
            $table->boolean('first_aid_kit')->default(false);
            $table->boolean('floor_mat')->default(false);
            $table->boolean('cigarette_lighter')->default(false);
            $table->boolean('cargo_net')->default(false);
            $table->boolean('ashtray')->default(false);
            $table->boolean('Tools')->default(false);
            $table->boolean('spare_tire')->default(false);
            $table->boolean('sun_roof')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_automobiles', function (Blueprint $table) {
            $table->dropColumn([
                'tag_no', 'vehicle_state', 'condition', 'key_number', 'fuel',
                'tire_size_front', 'tire_size_rear', 'mileage', 'w_sticker',
                'remote_control', 'headphone', 'owners_manual', 'cd_player',
                'cd_changer', 'first_aid_kit', 'floor_mat', 'cigarette_lighter',
                'cargo_net', 'ashtray', 'Tools', 'spare_tire', 'sun_roof'
            ]);
        });
    }
};
