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
            if (!Schema::hasColumn('ocean_exports', 'freight')) {
                $table->string('freight')->nullable();
            }
            if (!Schema::hasColumn('ocean_exports', 'transportation')) {
                $table->string('transportation')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocean_exports', function (Blueprint $table) {
            if (Schema::hasColumn('ocean_exports', 'freight')) {
                $table->dropColumn('freight');
            }
            if (Schema::hasColumn('ocean_exports', 'transportation')) {
                $table->dropColumn('transportation');
            }
        });
    }
};
