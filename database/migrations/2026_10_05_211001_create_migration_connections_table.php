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
        Schema::create('migration_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_project_id')->constrained('migration_projects')->cascadeOnDelete();
            
            // Database Connection Details (for mysql, sqlsrv)
            $table->string('db_host')->nullable();
            $table->string('db_port')->nullable();
            $table->string('db_database')->nullable();
            $table->string('db_username')->nullable();
            $table->text('db_password')->nullable(); // Should be encrypted in a real prod env
            $table->boolean('use_ssl')->default(false);
            
            // File Details (for csv, excel)
            $table->string('file_path')->nullable();
            
            // API Details
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_connections');
    }
};
