<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAiCreditTransaction;
use App\Models\User;
use App\Services\FrexoAiCreditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FrexoAiCreditBillingTest extends TestCase
{
    use DatabaseTransactions;

    protected FrexoAiCreditService $creditService;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('DB_DATABASE=freightX2.0');
        putenv('DB_CONNECTION=mysql');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'freightX2.0',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
        $this->creditService = app(FrexoAiCreditService::class);
    }

    /**
     * Test 1: New company creation automatically grants initial complimentary credits & logs ledger transaction.
     */
    public function test_new_company_receives_initial_credits_and_ledger_entry(): void
    {
        $company = Company::create([
            'name' => 'Automated Credit Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $this->assertNotNull($company->id);
        $this->assertEquals(1000.0, (float) $company->fresh()->frexo_ai_credits);

        $transaction = CompanyAiCreditTransaction::where('company_id', $company->id)
            ->where('type', 'INITIAL_BONUS')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(1000.0, (float) $transaction->credits_amount);
        $this->assertEquals(0.0, (float) $transaction->balance_before);
        $this->assertEquals(1000.0, (float) $transaction->balance_after);
    }

    /**
     * Test 2: Token calculation and credit conversion math.
     */
    public function test_token_and_credit_conversion_math(): void
    {
        // 400 characters string ~ 100 text tokens
        $text = str_repeat('abcd', 100);
        $tokens = $this->creditService->estimateTokens([$text], [], 'System Instructions');

        $this->assertGreaterThanOrEqual(100, $tokens);

        // 100 tokens per credit (configurable in config/frexo_ai.php)
        $credits = $this->creditService->calculateCreditsFromTokens(500);
        $this->assertEquals(5.0, $credits);

        $creditsSmall = $this->creditService->calculateCreditsFromTokens(25);
        $this->assertGreaterThanOrEqual(0.1, $creditsSmall); // Minimum 0.1 credits floor
    }

    /**
     * Test 3: Pre-flight credit check gates zero-credit companies.
     */
    public function test_preflight_check_blocks_zero_credits(): void
    {
        $company = Company::create([
            'name' => 'Zero Credit Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        // Manually set credits to 0
        $company->update(['frexo_ai_credits' => 0.0]);

        $check = $this->creditService->checkPreflight($company);
        $this->assertFalse($check['allowed']);
        $this->assertStringContainsString('insufficient', strtolower($check['message']));

        // Unlimited bypasses zero balance
        $company->update(['frexo_ai_unlimited' => true]);
        $checkUnlimited = $this->creditService->checkPreflight($company);
        $this->assertTrue($checkUnlimited['allowed']);
        $this->assertTrue($checkUnlimited['unlimited']);
    }

    /**
     * Test 4: Concurrency-safe atomic deduction updates balance and creates transaction record.
     */
    public function test_atomic_credit_deduction(): void
    {
        $company = Company::create([
            'name' => 'Deduction Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $company->update(['frexo_ai_credits' => 100.0]);

        // Deduct 500 tokens (5 credits)
        $res = $this->creditService->deductCredits(
            $company,
            500,
            'ocean_import',
            'BL_123456.pdf',
            null,
            ['prompt_tokens' => 400, 'completion_tokens' => 100]
        );

        $this->assertTrue($res['success']);
        $this->assertEquals(5.0, $res['credits_deducted']);
        $this->assertEquals(95.0, $res['balance_after']);
        $this->assertEquals(95.0, (float) $company->fresh()->frexo_ai_credits);

        $tx = CompanyAiCreditTransaction::find($res['transaction_id']);
        $this->assertEquals('USAGE_DEDUCTION', $tx->type);
        $this->assertEquals(-5.0, (float) $tx->credits_amount);
        $this->assertEquals('ocean_import', $tx->module);
        $this->assertEquals('BL_123456.pdf', $tx->reference_doc);
    }

    /**
     * Test 5: Recharging credits increases balance and logs transaction.
     */
    public function test_recharging_credits(): void
    {
        $company = Company::create([
            'name' => 'Recharge Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $company->update(['frexo_ai_credits' => 50.0]);

        $tx = $this->creditService->rechargeCredits($company, 250.0, null, 'Payment via Bank Invoice #998');

        $this->assertEquals(300.0, (float) $tx->balance_after);
        $this->assertEquals(300.0, (float) $company->fresh()->frexo_ai_credits);
        $this->assertEquals('RECHARGE', $tx->type);
        $this->assertEquals(250.0, (float) $tx->credits_amount);
    }

    /**
     * Test 6: Extraction endpoint blocks request when user's company has 0 credits.
     */
    public function test_extraction_endpoint_blocks_when_credits_exhausted(): void
    {
        $company = Company::create([
            'name' => 'API Block Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $company->update(['frexo_ai_credits' => 0.0, 'frexo_ai_unlimited' => false]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('ai.extract.ocean-import'), []);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'code' => 'INSUFFICIENT_CREDITS',
        ]);
        $this->assertStringContainsString('insufficient', $response->json('message'));
    }

    /**
     * Test 7: API endpoint returns accurate live credit balance.
     */
    public function test_credit_balance_api_endpoint(): void
    {
        $company = Company::create([
            'name' => 'Balance Query Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $company->update(['frexo_ai_credits' => 450.75, 'frexo_ai_unlimited' => false]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ai.credits.balance'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'balance' => 450.75,
            'unlimited' => false,
        ]);
    }
}
