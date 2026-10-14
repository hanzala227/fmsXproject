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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('active_provider', ['openai', 'gemini'])->default('openai');
            $table->text('openai_api_key')->nullable();
            $table->string('openai_model')->default('gpt-4o');
            $table->text('gemini_api_key')->nullable();
            $table->string('gemini_model')->default('gemini-1.5-pro');
            $table->timestamps();
        });

        // Insert a default row
        DB::table('ai_settings')->insert([
            'active_provider' => 'openai',
            'openai_model' => 'gpt-4o',
            'gemini_model' => 'gemini-1.5-pro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
