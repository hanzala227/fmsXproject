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
        Schema::table('ocean_import_hbls', function (Blueprint $table) {
            $columnsToDrop = [];
            
            $candidates = [
                'is_express_bl',
                'is_door_move',
                'referred_by_id',
                'is_obl_received',
                'obl_received_date',
                'is_fr_released',
                'fr_released_date',
                'is_an_sent',
                'an_sent_date',
                'name_account',
                'group_comm',
                'line_code',
                'is_ecommerce',
                'is_customs_doc',
            ];

            foreach ($candidates as $col) {
                if (Schema::hasColumn('ocean_import_hbls', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                // If dropping foreign key referred_by_id, drop foreign first
                if (in_array('referred_by_id', $columnsToDrop)) {
                    try {
                        $table->dropForeign(['referred_by_id']);
                    } catch (\Throwable $e) {
                        // ignore if FK doesn't exist
                    }
                }
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_import_hbls', function (Blueprint $table) {
            $table->foreignId('referred_by_id')->nullable()->constrained('trade_partners');
            $table->boolean('is_express_bl')->default(false);
            $table->boolean('is_door_move')->default(false);
            $table->boolean('is_obl_received')->default(false);
            $table->date('obl_received_date')->nullable();
            $table->boolean('is_fr_released')->default(false);
            $table->date('fr_released_date')->nullable();
            $table->boolean('is_an_sent')->default(false);
            $table->date('an_sent_date')->nullable();
            $table->string('name_account')->nullable();
            $table->string('group_comm')->nullable();
            $table->string('line_code')->nullable();
            $table->boolean('is_ecommerce')->default(false);
            $table->boolean('is_customs_doc')->default(false);
        });
    }
};
