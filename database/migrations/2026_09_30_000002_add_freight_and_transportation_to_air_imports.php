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
        Schema::table('air_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('air_imports', 'freight')) {
                $table->string('freight')->nullable();
            }
            if (!Schema::hasColumn('air_imports', 'transportation')) {
                $table->string('transportation')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            if (Schema::hasColumn('air_imports', 'freight')) {
                $table->dropColumn('freight');
            }
            if (Schema::hasColumn('air_imports', 'transportation')) {
                $table->dropColumn('transportation');
            }
        });
    }
};
