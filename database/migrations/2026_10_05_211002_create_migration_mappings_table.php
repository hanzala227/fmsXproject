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
        Schema::create('migration_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_project_id')->constrained('migration_projects')->cascadeOnDelete();
            
            // Table Level
            $table->string('source_table');
            $table->string('target_table'); // Our FMS tables, e.g., 'customers', 'ocean_imports'
            $table->string('status')->default('pending'); // pending, mapped, ignored
            $table->integer('ai_confidence')->nullable(); // Confidence score for table mapping
            
            // Field Level Mappings (JSON mapping of source column -> target column + transformations)
            $table->json('field_mappings')->nullable(); 
            /*
            Example structure:
            [
                {
                    "source_field": "cust_id",
                    "target_field": "external_id",
                    "confidence": 99,
                    "transformation": null
                },
                ...
            ]
            */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_mappings');
    }
};
