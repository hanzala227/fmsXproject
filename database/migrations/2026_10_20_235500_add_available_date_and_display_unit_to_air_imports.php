<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('air_imports', 'available_date')) {
                $table->date('available_date')->nullable()->after('last_free_day');
            }
            if (!Schema::hasColumn('air_imports', 'display_unit')) {
                $table->string('display_unit')->nullable()->after('is_ecommerce');
            }
        });

        Schema::table('air_import_hbls', function (Blueprint $table) {
            if (!Schema::hasColumn('air_import_hbls', 'available_date')) {
                $table->date('available_date')->nullable()->after('last_free_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            if (Schema::hasColumn('air_imports', 'available_date')) {
                $table->dropColumn('available_date');
            }
            if (Schema::hasColumn('air_imports', 'display_unit')) {
                $table->dropColumn('display_unit');
            }
        });

        Schema::table('air_import_hbls', function (Blueprint $table) {
            if (Schema::hasColumn('air_import_hbls', 'available_date')) {
                $table->dropColumn('available_date');
            }
        });
    }
};

