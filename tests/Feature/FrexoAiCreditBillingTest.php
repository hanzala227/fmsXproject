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
        \App\Models\FrexoAiCreditSetting::query()->update([
            'per_credit_usd' => 0.045,
            'min_custom_credits' => 100,
            'exchange_rate_pkr' => 280.00,
            'initial_free_credits' => 1000.00,
            'packages' => \App\Models\FrexoAiCreditSetting::defaultPackages(),
        ]);
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

    /**
     * Test 8: Settings AI Credits page renders properly with modals and pricing packages.
     */
    public function test_settings_ai_credits_page_renders_with_checkout_modals_and_pricing_tiers(): void
    {
        $company = Company::create([
            'name' => 'UI Modal Test Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        \App\Models\CompanyModule::create([
            'company_id' => $company->id,
            'module_key' => 'settings',
            'is_enabled' => true,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'Admin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->get(route('settings.ai-credits'));

        $response->assertStatus(200);
        $response->assertSee('Frexo AI Credits & Document Billing', false);
        $response->assertSee('Starter Pack');
        $response->assertSee('Growth Pack');
        $response->assertSee('Enterprise Scale');
        $response->assertSee('id="aiPayModal"', false);
        $response->assertSee('id="aiBankModal"', false);
        $response->assertSee('window.openPayModal', false);
        $response->assertSee('window.openBankModal', false);
    }

    /**
     * Test 9: Checkout with valid developer sandbox test card charges and grants credits.
     */
    public function test_settings_ai_credits_checkout_with_valid_test_card_grants_credits(): void
    {
        $company = Company::create([
            'name' => 'Developer Sandbox Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        \App\Models\CompanyModule::create([
            'company_id' => $company->id,
            'module_key' => 'settings',
            'is_enabled' => true,
        ]);

        $initialBalance = (float) $company->fresh()->frexo_ai_credits;

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'Admin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.ai-credits.checkout'), [
                'package_id' => 'growth',
                'cardholder_name' => 'FreightX Developer',
                'card_number' => '4242 4242 4242 4242',
                'card_expiry' => '12/28',
                'card_cvc' => '123',
            ]);

        $response->assertRedirect(route('settings.ai-credits'));
        $response->assertSessionHas('success');

        $this->assertEquals($initialBalance + 2500, (float) $company->fresh()->frexo_ai_credits);

        $tx = CompanyAiCreditTransaction::where('company_id', $company->id)
            ->where('type', 'RECHARGE')
            ->latest('id')
            ->first();

        $this->assertNotNull($tx);
        $this->assertEquals(2500, (float) $tx->credits_amount);
        $this->assertStringContainsString('4242', $tx->description);
    }

    /**
     * Test 10: Checkout with declined test card blocks credit addition.
     */
    public function test_settings_ai_credits_checkout_with_declined_test_card_blocks_credits(): void
    {
        $company = Company::create([
            'name' => 'Declined Test Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        \App\Models\CompanyModule::create([
            'company_id' => $company->id,
            'module_key' => 'settings',
            'is_enabled' => true,
        ]);

        $initialBalance = (float) $company->fresh()->frexo_ai_credits;

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'Admin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.ai-credits.checkout'), [
                'package_id' => 'growth',
                'cardholder_name' => 'Declined Test User',
                'card_number' => '4000 0000 0000 0002',
                'card_expiry' => '12/28',
                'card_cvc' => '123',
            ]);

        $response->assertSessionHas('error');
        // Crucial Production Assertion: Balance must remain strictly unchanged!
        $this->assertEquals($initialBalance, (float) $company->fresh()->frexo_ai_credits);
    }

    /**
     * Test 11: Checkout with expired card blocks credit addition.
     */
    public function test_settings_ai_credits_checkout_with_expired_card_blocks_credits(): void
    {
        $company = Company::create([
            'name' => 'Expired Card Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        \App\Models\CompanyModule::create([
            'company_id' => $company->id,
            'module_key' => 'settings',
            'is_enabled' => true,
        ]);

        $initialBalance = (float) $company->fresh()->frexo_ai_credits;

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'Admin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.ai-credits.checkout'), [
                'package_id' => 'growth',
                'cardholder_name' => 'Expired Card User',
                'card_number' => '4242 4242 4242 4242',
                'card_expiry' => '01/20',
                'card_cvc' => '123',
            ]);

        $response->assertSessionHas('error');
        // Crucial Production Assertion: Balance must remain strictly unchanged!
        $this->assertEquals($initialBalance, (float) $company->fresh()->frexo_ai_credits);
    }

    /**
     * Test 12: Bank wire transfer deposit request creates pending record without altering balance.
     */
    public function test_settings_ai_credits_bank_transfer_submission(): void
    {
        $company = Company::create([
            'name' => 'Bank Wire Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        \App\Models\CompanyModule::create([
            'company_id' => $company->id,
            'module_key' => 'settings',
            'is_enabled' => true,
        ]);

        $initialBalance = (float) $company->fresh()->frexo_ai_credits;

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'Admin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.ai-credits.bank-transfer'), [
                'amount_credits' => 5000,
                'bank_name' => 'Emirates NBD',
                'deposit_reference' => 'WIRE-DXB-998811',
                'notes' => 'Urgent topup for ocean import customs filing',
            ]);

        $response->assertRedirect(route('settings.ai-credits'));
        $response->assertSessionHas('success');

        // Crucial Production Assertion: Balance must remain completely unchanged!
        $this->assertEquals($initialBalance, (float) $company->fresh()->frexo_ai_credits);

        $tx = CompanyAiCreditTransaction::where('company_id', $company->id)
            ->where('reference_doc', 'WIRE-DXB-998811')
            ->first();

        $this->assertNotNull($tx);
        $this->assertEquals(0, $tx->credits_amount); // Pending verification
        $this->assertEquals('BANK_WIRE_PENDING', $tx->type);
        $this->assertStringContainsString('WIRE-DXB-998811', $tx->description);
    }

    /**
     * Test 13: SuperAdmin can view dynamic AI credit packages & pricing configuration.
     */
    public function test_superadmin_can_view_dynamic_ai_credits_settings(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SuperAdmin',
            'status' => 'Enable',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('super-admin.ai-settings.credits'));

        $response->assertStatus(200);
        $response->assertSee('Frexo AI Credits & Pricing Engine', false);
        $response->assertSee('Global Credit Valuation & Top-Up Limits (USD)', false);
        $response->assertSee('Dynamic AI Credit Packages Manager', false);
        $response->assertSee('Bank Wire Transfer Beneficiary Settings', false);
    }

    /**
     * Test 14: SuperAdmin can dynamically update packages, per-credit rate, and bank details.
     */
    public function test_superadmin_can_update_dynamic_packages_and_per_credit_rate(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SuperAdmin',
            'status' => 'Enable',
        ]);

        $newPackages = [
            [
                'id' => 'custom_scale_vip',
                'name' => 'VIP Scale Agency',
                'credits' => 15000,
                'price_usd' => 450.00,
                'popular' => 1,
                'desc' => 'High-velocity freight processing tier.',
                'features' => "15,000 Credits\nDedicated Concurrency\nCustom AI Mapping",
            ],
        ];

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.ai-settings.credits.update'), [
                'per_credit_usd' => 0.0500,
                'min_custom_credits' => 200,
                'exchange_rate_pkr' => 285.00,
                'initial_free_credits' => 1200.00,
                'packages' => $newPackages,
                'bank_name' => 'Standard Chartered Bank UAE',
                'bank_account_title' => 'FreightX Global Holdings FZE',
                'bank_iban' => 'AE290260001234567890123',
                'bank_swift' => 'SCBLAEAD',
                'bank_instructions' => 'Include company ID in wire remittance advice.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $setting = \App\Models\FrexoAiCreditSetting::current();
        $this->assertEquals(0.0500, (float) $setting->per_credit_usd);
        $this->assertEquals(200, (int) $setting->min_custom_credits);
        $this->assertEquals(285.00, (float) $setting->exchange_rate_pkr);
        $this->assertEquals(1200.00, (float) $setting->initial_free_credits);
        $this->assertEquals('Standard Chartered Bank UAE', $setting->bank_name);
        $this->assertEquals('AE290260001234567890123', $setting->bank_iban);
        $this->assertEquals('VIP Scale Agency', $setting->packages[0]['name']);
        $this->assertEquals(15000, $setting->packages[0]['credits']);
    }

    /**
     * Test 15: SuperAdmin can verify and approve pending bank wire deposit requests.
     */
    public function test_superadmin_can_approve_pending_bank_wire_request_and_grant_credits(): void
    {
        $company = Company::create([
            'name' => 'Treasury Wire Audit Tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'storage_limit' => 10,
            'user_limit' => 5,
        ]);

        $superAdmin = User::factory()->create([
            'role' => 'SuperAdmin',
            'status' => 'Enable',
        ]);

        $initialBalance = (float) $company->fresh()->frexo_ai_credits;

        // Create a pending wire transfer request
        $tx = CompanyAiCreditTransaction::create([
            'company_id' => $company->id,
            'user_id' => $superAdmin->id,
            'type' => 'BANK_WIRE_PENDING',
            'credits_amount' => 0.0000,
            'balance_before' => $initialBalance,
            'balance_after' => $initialBalance,
            'tokens_used' => 0,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'module' => 'bank_wire_request',
            'reference_doc' => 'WIRE-TREASURY-0099',
            'description' => 'Bank Wire Deposit Pending Approval (HBL): Request for 5,000 credits. Ref: WIRE-TREASURY-0099',
            'metadata' => [
                'bank_name' => 'HBL',
                'requested_credits' => 5000.0,
                'reference' => 'WIRE-TREASURY-0099',
                'status' => 'pending_verification',
            ],
        ]);

        // SuperAdmin approves the wire request
        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.ai-settings.wire-requests.approve', $tx->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert company was credited with 5000 credits
        $this->assertEquals($initialBalance + 5000, (float) $company->fresh()->frexo_ai_credits);

        $freshTx = $tx->fresh();
        $this->assertEquals('RECHARGE', $freshTx->type);
        $this->assertEquals(5000.0, (float) $freshTx->credits_amount);
        $this->assertEquals('approved', $freshTx->metadata['status'] ?? null);
    }
}
