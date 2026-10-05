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
        Schema::create('migration_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // The target company
            $table->string('project_code')->unique(); // e.g., MIG-000124
            $table->string('source_system')->nullable(); // e.g., CargoSoft, Legacy ERP
            $table->string('source_type'); // mysql, sqlsrv, csv, api
            $table->string('status')->default('draft'); // draft, connecting, mapping, validation, importing, completed, failed
            $table->integer('progress')->default(0); // 0-100 percentage
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('error_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_projects');
    }
};
