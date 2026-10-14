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
        Schema::table('ai_settings', function (Blueprint $table) {
            // Drop old specific columns
            $table->dropColumn([
                'active_provider', 
                'openai_api_key', 
                'openai_model', 
                'gemini_api_key', 
                'gemini_model'
            ]);

            // Add new universal columns
            $table->enum('api_type', ['openai_compatible', 'gemini'])->default('openai_compatible')->after('id');
            $table->string('base_url')->default('https://api.openai.com/v1')->after('api_type');
            $table->text('api_key')->nullable()->after('base_url');
            $table->string('model_name')->default('gpt-4o')->after('api_key');
        });

        // Seed with a default generic row if empty
        if (DB::table('ai_settings')->count() === 0) {
            DB::table('ai_settings')->insert([
                'api_type' => 'openai_compatible',
                'base_url' => 'https://api.openai.com/v1',
                'model_name' => 'gpt-4o',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn(['api_type', 'base_url', 'api_key', 'model_name']);
            
            $table->enum('active_provider', ['openai', 'gemini'])->default('openai');
            $table->text('openai_api_key')->nullable();
            $table->string('openai_model')->default('gpt-4o');
            $table->text('gemini_api_key')->nullable();
            $table->string('gemini_model')->default('gemini-1.5-pro');
        });
    }
};
