<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tickets', 'priority')) {
                $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium')->after('category');
            }
            if (!Schema::hasColumn('tickets', 'assigned_agent_id')) {
                $table->unsignedBigInteger('assigned_agent_id')->nullable()->after('status');
                $table->foreign('assigned_agent_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('tickets', 'sla_due_at')) {
                $table->timestamp('sla_due_at')->nullable()->after('assigned_agent_id');
            }
            if (!Schema::hasColumn('tickets', 'related_bug_id')) {
                $table->unsignedBigInteger('related_bug_id')->nullable()->after('sla_due_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->dropColumn([
                'category',
                'priority',
                'assigned_agent_id',
                'sla_due_at',
                'related_bug_id'
            ]);
        });
    }
};
