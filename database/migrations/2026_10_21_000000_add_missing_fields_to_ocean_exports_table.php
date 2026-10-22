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
        Schema::table('ocean_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('ocean_exports', 'mark')) {
                $table->text('mark')->nullable()->after('internal_remark');
            }
            if (!Schema::hasColumn('ocean_exports', 'description')) {
                $table->text('description')->nullable()->after('mark');
            }
            if (!Schema::hasColumn('ocean_exports', 'is_obl_received')) {
                $table->boolean('is_obl_received')->default(false)->after('obl_type');
            }
            if (!Schema::hasColumn('ocean_exports', 'lfd')) {
                $table->date('lfd')->nullable()->after('incoterm_id');
            }
            if (!Schema::hasColumn('ocean_exports', 'is_do_sent')) {
                $table->boolean('is_do_sent')->default(false)->after('lfd');
            }
            if (!Schema::hasColumn('ocean_exports', 'do_sent_date')) {
                $table->date('do_sent_date')->nullable()->after('is_do_sent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_exports', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['mark', 'description', 'is_obl_received', 'lfd', 'is_do_sent', 'do_sent_date'] as $col) {
                if (Schema::hasColumn('ocean_exports', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

