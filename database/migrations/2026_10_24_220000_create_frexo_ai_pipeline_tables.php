<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('frexo_extraction_runs')) {
            Schema::create('frexo_extraction_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('module', 50); // ocean_import, ocean_export, air_import, air_export
                $table->string('schema_version', 20)->default('1.0');
                $table->string('status', 30)->default('COMPLETED'); // PENDING, COMPLETED, APPLIED, FAILED
                $table->string('idempotency_token', 100)->nullable()->unique();
                $table->json('processing_summary')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('frexo_extraction_documents')) {
            Schema::create('frexo_extraction_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('document_id', 100)->index();
                $table->string('filename');
                $table->string('document_type', 50)->default('UNKNOWN');
                $table->integer('pages_total')->default(1);
                $table->integer('pages_processed')->default(1);
                $table->string('status', 30)->default('SUCCESS');
                $table->json('limitations')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('frexo_field_evidence')) {
            Schema::create('frexo_field_evidence', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('field_path', 100)->index();
                $table->string('scope', 30)->default('master');
                $table->text('raw_value')->nullable();
                $table->text('normalized_value')->nullable();
                $table->string('unit', 20)->nullable();
                $table->string('status', 30)->default('EXPLICIT');
                $table->string('source_document_id', 100)->nullable();
                $table->integer('source_page')->default(1);
                $table->string('evidence_text', 200)->nullable();
                $table->json('derivation')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('frexo_review_decisions')) {
            Schema::create('frexo_review_decisions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('field_path', 100)->index();
                $table->string('decision', 30)->default('ACCEPTED'); // ACCEPTED, REJECTED, EDITED
                $table->text('original_value')->nullable();
                $table->text('final_value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('frexo_extraction_audits')) {
            Schema::create('frexo_extraction_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_id')->nullable()->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('field_path', 100)->index();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->string('source_document_id', 100)->nullable();
                $table->integer('source_page')->default(1);
                $table->string('status', 30)->default('APPLIED');
                $table->string('model_version', 50)->nullable();
                $table->string('prompt_version', 20)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('frexo_extraction_audits');
        Schema::dropIfExists('frexo_review_decisions');
        Schema::dropIfExists('frexo_field_evidence');
        Schema::dropIfExists('frexo_extraction_documents');
        Schema::dropIfExists('frexo_extraction_runs');
    }
};

