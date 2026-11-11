<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('journal_entries', 'company_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index('company_id', 'journal_entries_company_id_index');
            });

            DB::statement('
                UPDATE journal_entries je
                LEFT JOIN users u ON je.created_by = u.id
                SET je.company_id = u.company_id
                WHERE je.company_id IS NULL
            ');
        }

        if (!Schema::hasColumn('journal_entry_lines', 'company_id')) {
            Schema::table('journal_entry_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index('company_id', 'journal_entry_lines_company_id_index');
            });

            DB::statement('
                UPDATE journal_entry_lines jel
                INNER JOIN journal_entries je ON jel.journal_entry_id = je.id
                SET jel.company_id = je.company_id
                WHERE jel.company_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('journal_entry_lines', 'company_id')) {
            Schema::table('journal_entry_lines', function (Blueprint $table) {
                $table->dropIndex('journal_entry_lines_company_id_index');
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasColumn('journal_entries', 'company_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropIndex('journal_entries_company_id_index');
                $table->dropColumn('company_id');
            });
        }
    }
};
