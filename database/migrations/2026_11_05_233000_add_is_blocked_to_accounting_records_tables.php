<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'is_blocked')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('status')->index();
            });
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'is_blocked')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('type')->index();
            });
        }

        if (Schema::hasTable('journal_entries') && !Schema::hasColumn('journal_entries', 'is_blocked')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('status')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'is_blocked')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('is_blocked');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'is_blocked')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('is_blocked');
            });
        }

        if (Schema::hasTable('journal_entries') && Schema::hasColumn('journal_entries', 'is_blocked')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropColumn('is_blocked');
            });
        }
    }
};
