<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the old global unique index on invoice_no if it exists
            $table->dropUnique(['invoice_no']);
            
            // Re-add unique index taking company_id into account
            // Assuming company_id exists on invoices. If not, this might fail, but checking previous migration it is there.
            // Actually wait, some tables had company_id added later, so we must ensure company_id is part of the index.
            // Let's first add the new columns.
            
            $table->string('main_sequence_no')->nullable()->after('invoice_no');
            $table->string('group_letter', 2)->nullable()->after('main_sequence_no');
            $table->string('document_type', 10)->default('INV')->after('group_letter');
            
            // Verification fields
            $table->enum('verification_status', ['Pending Verification', 'Verified', 'Rejected', 'Cancelled'])->default('Pending Verification')->after('status');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            
            // Add proper unique constraint for multi-tenancy
            $table->unique(['company_id', 'invoice_no']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'invoice_no']);
            
            $table->dropColumn([
                'main_sequence_no',
                'group_letter',
                'document_type',
                'verification_status',
                'verified_by',
                'verified_at',
                'rejected_by',
                'rejected_at',
                'reject_reason'
            ]);
            
            $table->unique(['invoice_no']);
        });
    }
};
