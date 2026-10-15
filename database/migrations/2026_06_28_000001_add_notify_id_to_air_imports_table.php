<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            $table->foreignId('notify_id')->nullable()->constrained('trade_partners')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('air_imports', function (Blueprint $table) {
            $table->dropForeign(['notify_id']);
            $table->dropColumn('notify_id');
        });
    }
};
