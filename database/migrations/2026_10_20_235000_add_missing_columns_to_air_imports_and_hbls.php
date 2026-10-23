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
        Schema::table('air_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('air_imports', 'awb_type')) {
                $table->string('awb_type')->nullable()->default('NORMAL')->after('office_id');
            }
            if (!Schema::hasColumn('air_imports', 'dm_sales_type')) {
                $table->string('dm_sales_type')->nullable()->after('dm_sales_person_id');
            }
            if (!Schema::hasColumn('air_imports', 'gross_weight_kg')) {
                $table->decimal('gross_weight_kg', 15, 2)->nullable()->after('gross_weight');
            }
            if (!Schema::hasColumn('air_imports', 'gross_weight_lb')) {
                $table->decimal('gross_weight_lb', 15, 2)->nullable()->after('gross_weight_kg');
            }
            if (!Schema::hasColumn('air_imports', 'chargeable_weight_kg')) {
                $table->decimal('chargeable_weight_kg', 15, 2)->nullable()->after('chargeable_weight');
            }
            if (!Schema::hasColumn('air_imports', 'chargeable_weight_lb')) {
                $table->decimal('chargeable_weight_lb', 15, 2)->nullable()->after('chargeable_weight_kg');
            }
            if (!Schema::hasColumn('air_imports', 'volume_weight_kg')) {
                $table->decimal('volume_weight_kg', 15, 2)->nullable()->after('volume');
            }
            if (!Schema::hasColumn('air_imports', 'volume_cbm')) {
                $table->decimal('volume_cbm', 15, 3)->nullable()->after('volume_weight_kg');
            }
            if (!Schema::hasColumn('air_imports', 'incoterm_id')) {
                $table->foreignId('incoterm_id')->nullable()->constrained('incoterms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_imports', 'svc_term_from_id')) {
                $table->foreignId('svc_term_from_id')->nullable()->constrained('service_terms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_imports', 'svc_term_to_id')) {
                $table->foreignId('svc_term_to_id')->nullable()->constrained('service_terms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_imports', 'service_term_from')) {
                $table->string('service_term_from')->nullable();
            }
            if (!Schema::hasColumn('air_imports', 'service_term_to')) {
                $table->string('service_term_to')->nullable();
            }
            if (!Schema::hasColumn('air_imports', 'cargo_type')) {
                $table->string('cargo_type')->nullable()->default('GENERAL CARGO');
            }
            if (!Schema::hasColumn('air_imports', 'stackable')) {
                $table->boolean('stackable')->default(true);
            }
        });

        Schema::table('air_import_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('air_import_hbls', 'freight_location_id')) {
                $table->foreignId('freight_location_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'final_destination_id')) {
                $table->foreignId('final_destination_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'final_eta')) {
                $table->date('final_eta')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'delivery_location_id')) {
                $table->foreignId('delivery_location_id')->nullable()->constrained('trade_partners')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'trucker_id')) {
                $table->foreignId('trucker_id')->nullable()->constrained('trade_partners')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'last_free_day')) {
                $table->date('last_free_day')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'storage_start_date')) {
                $table->date('storage_start_date')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'sales_type')) {
                $table->string('sales_type')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'gross_weight_kg')) {
                $table->decimal('gross_weight_kg', 15, 2)->nullable()->after('gross_weight');
            }
            if (!Schema::hasColumn('air_import_hbls', 'gross_weight_lb')) {
                $table->decimal('gross_weight_lb', 15, 2)->nullable()->after('gross_weight_kg');
            }
            if (!Schema::hasColumn('air_import_hbls', 'chargeable_weight_kg')) {
                $table->decimal('chargeable_weight_kg', 15, 2)->nullable()->after('chargeable_weight');
            }
            if (!Schema::hasColumn('air_import_hbls', 'chargeable_weight_lb')) {
                $table->decimal('chargeable_weight_lb', 15, 2)->nullable()->after('chargeable_weight_kg');
            }
            if (!Schema::hasColumn('air_import_hbls', 'volume_weight_kg')) {
                $table->decimal('volume_weight_kg', 15, 2)->nullable()->after('volume');
            }
            if (!Schema::hasColumn('air_import_hbls', 'volume_cbm')) {
                $table->decimal('volume_cbm', 15, 3)->nullable()->after('volume_weight_kg');
            }
            if (!Schema::hasColumn('air_import_hbls', 'entry_no')) {
                $table->string('entry_no')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'class_of_entry')) {
                $table->string('class_of_entry')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'service_term_from_id')) {
                $table->foreignId('service_term_from_id')->nullable()->constrained('service_terms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'service_term_to_id')) {
                $table->foreignId('service_term_to_id')->nullable()->constrained('service_terms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'released_by_id')) {
                $table->foreignId('released_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_import_hbls', 'cargo_released_to')) {
                $table->string('cargo_released_to')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'c_released_date')) {
                $table->date('c_released_date')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'door_delivered_date')) {
                $table->date('door_delivered_date')->nullable();
            }
            if (!Schema::hasColumn('air_import_hbls', 'ship_type')) {
                $table->string('ship_type')->nullable()->default('NORMAL');
            }
            if (!Schema::hasColumn('air_import_hbls', 'display_unit')) {
                $table->string('display_unit')->nullable()->default('BOTH');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            $fks = ['incoterm_id', 'svc_term_from_id', 'svc_term_to_id'];
            foreach ($fks as $fk) {
                if (Schema::hasColumn('air_imports', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }
            $cols = [
                'awb_type', 'dm_sales_type', 'gross_weight_kg', 'gross_weight_lb',
                'chargeable_weight_kg', 'chargeable_weight_lb', 'volume_weight_kg', 'volume_cbm',
                'incoterm_id', 'svc_term_from_id', 'svc_term_to_id', 'service_term_from',
                'service_term_to', 'cargo_type', 'stackable'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('air_imports', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('air_import_hbls', function (Blueprint $table) {
            $fks = [
                'freight_location_id', 'final_destination_id', 'delivery_location_id',
                'trucker_id', 'service_term_from_id', 'service_term_to_id', 'released_by_id'
            ];
            foreach ($fks as $fk) {
                if (Schema::hasColumn('air_import_hbls', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }
            $cols = [
                'freight_location_id', 'final_destination_id', 'final_eta', 'delivery_location_id',
                'trucker_id', 'last_free_day', 'storage_start_date', 'sales_type',
                'gross_weight_kg', 'gross_weight_lb', 'chargeable_weight_kg', 'chargeable_weight_lb',
                'volume_weight_kg', 'volume_cbm', 'entry_no', 'class_of_entry',
                'service_term_from_id', 'service_term_to_id', 'released_by_id',
                'cargo_released_to', 'c_released_date', 'door_delivered_date', 'ship_type', 'display_unit'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('air_import_hbls', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

