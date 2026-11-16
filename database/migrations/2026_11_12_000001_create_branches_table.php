<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->string('code', 50); // Freight Station/Branch Code (e.g. DXB-01, KHI-HQ, JFK-AIR)
                $table->string('name', 255); // Branch Name
                $table->string('branch_type', 50)->default('Regional Hub'); // Head Office, Regional Hub, Airport Station, Seaport Station, CFS / Bonded Warehouse, Land Transport Hub, Sales Office
                $table->boolean('is_head_office')->default(false);
                $table->string('iata_code', 10)->nullable(); // 3-letter IATA Airport/Terminal code (e.g. DXB, KHI, JFK)
                $table->string('unlocode', 10)->nullable(); // 5-letter UN/LOCODE port code (e.g. AEDXB, PKKHI, USNYC)
                $table->string('country', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state_province', 100)->nullable();
                $table->string('postal_code', 30)->nullable();
                $table->text('address')->nullable();
                $table->string('manager_name', 150)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('fax', 50)->nullable();
                $table->string('timezone', 50)->default('UTC');
                $table->string('currency', 10)->default('USD');
                $table->string('tax_number', 100)->nullable(); // TRN / NTN / VAT
                $table->string('customs_code', 50)->nullable(); // Station Customs / Port terminal license
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'code']);
                $table->index(['company_id', 'is_active']);
            });

            // Auto-seed initial Head Office branch for existing companies so data is ready and functional
            $companies = DB::table('companies')->get();
            $now = now();
            foreach ($companies as $comp) {
                $companyCode = strtoupper($comp->code ?: substr(preg_replace('/[^A-Za-z0-9]/', '', $comp->name), 0, 3));
                DB::table('branches')->insert([
                    'company_id' => $comp->id,
                    'code' => $companyCode . '-HQ',
                    'name' => $comp->name . ' - Head Office',
                    'branch_type' => 'Head Office',
                    'is_head_office' => true,
                    'iata_code' => $comp->country === 'Pakistan' ? 'KHI' : ($comp->country === 'UAE' || $comp->country === 'United Arab Emirates' ? 'DXB' : 'JFK'),
                    'unlocode' => $comp->country === 'Pakistan' ? 'PKKHI' : ($comp->country === 'UAE' || $comp->country === 'United Arab Emirates' ? 'AEDXB' : 'USNYC'),
                    'country' => $comp->country ?: 'Global',
                    'city' => $comp->address ? explode(',', $comp->address)[0] : 'Headquarters',
                    'address' => $comp->address ?: ($comp->name . ' Corporate Headquarters'),
                    'manager_name' => 'General Manager',
                    'email' => $comp->email ?: ('operations@' . ($comp->subdomain ?: 'fms') . '.com'),
                    'phone' => $comp->phone ?: '+1 (555) 0100',
                    'timezone' => $comp->timezone ?: 'UTC',
                    'currency' => 'USD',
                    'tax_number' => 'TRN-' . strtoupper($companyCode) . '-9901',
                    'customs_code' => 'CUST-' . strtoupper($companyCode),
                    'is_active' => ($comp->status === 'active'),
                    'notes' => 'Primary operational station created automatically during FreightX 2.0 system provisioning.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
