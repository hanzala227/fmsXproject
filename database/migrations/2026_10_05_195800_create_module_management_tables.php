<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. System Modules
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique(); // e.g., 'ocean_import'
            $table->string('category')->nullable();
            $table->string('version')->default('1.0.0');
            $table->enum('status', ['Active', 'Beta', 'Deprecated', 'Inactive'])->default('Active');
            $table->date('release_date')->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. Module Packages
        Schema::create('module_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->json('module_keys')->nullable(); // Array of module keys included in this package
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Feature Flags
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('company_overrides')->nullable(); // JSON object mapping company_id -> boolean
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('module_packages');
        Schema::dropIfExists('system_modules');
    }
};
