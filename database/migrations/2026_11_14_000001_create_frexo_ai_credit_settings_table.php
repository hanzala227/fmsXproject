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
        if (!Schema::hasTable('frexo_ai_credit_settings')) {
            Schema::create('frexo_ai_credit_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('per_credit_usd', 8, 4)->default(0.0450);
                $table->unsignedInteger('min_custom_credits')->default(100);
                $table->decimal('exchange_rate_pkr', 8, 2)->default(280.00);
                $table->decimal('initial_free_credits', 10, 2)->default(1000.00);
                $table->json('packages')->nullable();
                $table->string('bank_name', 255)->default('JPMorgan Chase Bank / Habib Bank Limited');
                $table->string('bank_account_title', 255)->default('FreightX Technologies International');
                $table->string('bank_iban', 100)->default('PK36HABB0000001234567890');
                $table->string('bank_swift', 50)->nullable()->default('HABBKPKA');
                $table->text('bank_instructions')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frexo_ai_credit_settings');
    }
};
