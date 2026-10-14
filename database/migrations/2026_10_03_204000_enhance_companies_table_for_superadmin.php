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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('companies', 'country')) {
                $table->string('country')->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('companies', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('plan_id'); // active, trial, suspended, expired, cancelled
            }
            if (!Schema::hasColumn('companies', 'storage_limit')) {
                $table->integer('storage_limit')->default(5)->after('subscription_status'); // in GB
            }
            if (!Schema::hasColumn('companies', 'user_limit')) {
                $table->integer('user_limit')->default(10)->after('storage_limit');
            }
            if (!Schema::hasColumn('companies', 'default_language')) {
                $table->string('default_language')->default('en')->after('user_limit');
            }
            if (!Schema::hasColumn('companies', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('default_language');
            }
            if (!Schema::hasColumn('companies', 'logo')) {
                $table->string('logo')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('companies', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('logo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name', 'country', 'plan_id', 'subscription_status', 
                'storage_limit', 'user_limit', 'default_language', 
                'timezone', 'logo', 'subscription_ends_at'
            ]);
        });
    }
};
