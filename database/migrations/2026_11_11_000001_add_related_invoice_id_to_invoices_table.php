<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'related_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('related_invoice_id')->nullable()->after('invoiceable_id');
                $table->index('related_invoice_id', 'invoices_related_invoice_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'related_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('invoices_related_invoice_id_index');
                $table->dropColumn('related_invoice_id');
            });
        }
    }
};
