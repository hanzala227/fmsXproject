<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'frexo_ai_credits')) {
                $table->decimal('frexo_ai_credits', 12, 4)->default(1000.0000)->after('subscription_ends_at');
            }
            if (!Schema::hasColumn('companies', 'frexo_ai_unlimited')) {
                $table->boolean('frexo_ai_unlimited')->default(false)->after('frexo_ai_credits');
            }
        });

        // Backfill existing companies with initial complimentary credits if empty
        $initialCredits = (float) config('frexo_ai.billing.initial_company_credits', 1000.0);
        DB::table('companies')
            ->whereNull('frexo_ai_credits')
            ->orWhere('frexo_ai_credits', 0)
            ->update(['frexo_ai_credits' => $initialCredits]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'frexo_ai_unlimited')) {
                $table->dropColumn('frexo_ai_unlimited');
            }
            if (Schema::hasColumn('companies', 'frexo_ai_credits')) {
                $table->dropColumn('frexo_ai_credits');
            }
        });
    }
};
