<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'created_by_id')) {
                $table->unsignedBigInteger('created_by_id')->nullable()->after('company_id');
                $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'accessible_modules')) {
                $table->json('accessible_modules')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by_id')) {
                $table->dropForeign(['created_by_id']);
                $table->dropColumn('created_by_id');
            }
            if (Schema::hasColumn('users', 'accessible_modules')) {
                $table->dropColumn('accessible_modules');
            }
        });
    }
};

