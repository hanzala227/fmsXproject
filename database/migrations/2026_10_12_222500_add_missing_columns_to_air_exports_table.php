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
        Schema::table('air_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('air_exports', 'dm_sales_person_id')) {
                $table->foreignId('dm_sales_person_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'incoterm_id')) {
                $table->foreignId('incoterm_id')->nullable()->constrained('incoterms')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'mark_number')) {
                $table->string('mark_number')->nullable();
            }
            if (!Schema::hasColumn('air_exports', 'service_term_from')) {
                $table->string('service_term_from')->nullable();
            }
            if (!Schema::hasColumn('air_exports', 'service_term_to')) {
                $table->string('service_term_to')->nullable();
            }
            if (!Schema::hasColumn('air_exports', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->constrained('trade_partners')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'co_loader_id')) {
                $table->foreignId('co_loader_id')->nullable()->constrained('trade_partners')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'trans_port_id')) {
                $table->foreignId('trans_port_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'trans_port1_id')) {
                $table->foreignId('trans_port1_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'trans_port2_id')) {
                $table->foreignId('trans_port2_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'trans_port3_id')) {
                $table->foreignId('trans_port3_id')->nullable()->constrained('ports')->nullOnDelete();
            }
            if (!Schema::hasColumn('air_exports', 'delivery_port_id')) {
                $table->foreignId('delivery_port_id')->nullable()->constrained('ports')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_exports', function (Blueprint $table) {
            $foreignKeys = [
                'dm_sales_person_id', 'incoterm_id', 'agent_id', 'co_loader_id',
                'trans_port_id', 'trans_port1_id', 'trans_port2_id', 'trans_port3_id', 'delivery_port_id'
            ];
            foreach ($foreignKeys as $fk) {
                if (Schema::hasColumn('air_exports', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }

            $columns = [
                'dm_sales_person_id', 'incoterm_id', 'mark_number', 'service_term_from',
                'service_term_to', 'agent_id', 'co_loader_id', 'trans_port_id',
                'trans_port1_id', 'trans_port2_id', 'trans_port3_id', 'delivery_port_id'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('air_exports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
