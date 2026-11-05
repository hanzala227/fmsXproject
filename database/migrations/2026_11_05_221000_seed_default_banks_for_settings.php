<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companyId = 1;

        $standardBanks = [
            [
                'company_id' => $companyId,
                'bank_name' => 'JPMorgan Chase USD Main',
                'gl_no' => '10210 CASH IN BANK: CHASE USD',
                'initial_amount' => 100000.00,
                'currency' => 'USD',
                'revenue_default' => 1,
                'cost_default' => 1,
                'notes_receivable' => 'Main Operating USD Bank Account',
                'notes_payable' => 'Disbursement & Operations',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'bank_name' => 'Emirates NBD USD A/C',
                'gl_no' => '10220 CASH IN BANK: NBD USD',
                'initial_amount' => 50000.00,
                'currency' => 'USD',
                'revenue_default' => 0,
                'cost_default' => 0,
                'notes_receivable' => 'Regional Settlement USD',
                'notes_payable' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'bank_name' => 'Deutsche Bank EUR Account',
                'gl_no' => '10230 CASH IN BANK: DEUTSCHE EUR',
                'initial_amount' => 45000.00,
                'currency' => 'EUR',
                'revenue_default' => 0,
                'cost_default' => 0,
                'notes_receivable' => 'European Operations EUR',
                'notes_payable' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'bank_name' => 'Habib Bank Ltd PKR Account',
                'gl_no' => '10240 CASH IN BANK: HBL PKR',
                'initial_amount' => 2500000.00,
                'currency' => 'PKR',
                'revenue_default' => 0,
                'cost_default' => 0,
                'notes_receivable' => 'Local Currency PKR Account',
                'notes_payable' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($standardBanks as $bank) {
            $exists = DB::table('banks')
                ->where('company_id', $companyId)
                ->where('bank_name', $bank['bank_name'])
                ->exists();

            if (!$exists) {
                DB::table('banks')->insert($bank);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep user data safe; no drop
    }
};
