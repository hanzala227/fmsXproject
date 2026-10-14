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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'smtp_host')) {
                $table->string('smtp_host')->nullable()->after('timezone');
                $table->string('smtp_port')->nullable()->after('smtp_host');
                $table->string('smtp_username')->nullable()->after('smtp_port');
                $table->text('smtp_password')->nullable()->after('smtp_username'); // Encrypted
                $table->string('smtp_encryption')->nullable()->after('smtp_password'); // tls, ssl
                $table->string('from_address')->nullable()->after('smtp_encryption');
                $table->string('from_name')->nullable()->after('from_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'from_address',
                'from_name'
            ]);
        });
    }
};
