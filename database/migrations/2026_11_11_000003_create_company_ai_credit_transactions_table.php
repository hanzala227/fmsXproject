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
        if (!Schema::hasTable('company_ai_credit_transactions')) {
            Schema::create('company_ai_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 50)->index(); // INITIAL_BONUS, USAGE_DEDUCTION, RECHARGE, ADMIN_ADJUSTMENT, REFUND
                $table->decimal('credits_amount', 12, 4); // Negative for debit, positive for credit
                $table->decimal('balance_before', 12, 4);
                $table->decimal('balance_after', 12, 4);
                $table->unsignedBigInteger('tokens_used')->default(0);
                $table->unsignedBigInteger('prompt_tokens')->default(0);
                $table->unsignedBigInteger('completion_tokens')->default(0);
                $table->string('module', 64)->nullable()->index(); // ocean_import, air_export, etc.
                $table->string('reference_doc', 255)->nullable(); // filename or shipment ref
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'created_at']);
                $table->index(['company_id', 'type']);

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_ai_credit_transactions');
    }
};
