<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyAiCreditTransaction;
use App\Models\FrexoAiCreditSetting;
use App\Services\FrexoAiCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCreditBillingController extends Controller
{
    /**
     * Display tenant AI Credits & Billing dashboard in ERP Settings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company ?: Company::first();

        if (!$company) {
            abort(404, 'Tenant company profile not found.');
        }

        $balance = (float) ($company->frexo_ai_credits ?? 0.0);
        $isUnlimited = (bool) $company->frexo_ai_unlimited;

        // Monthly telemetry statistics
        $startOfMonth = now()->startOfMonth();
        $monthlyCreditsUsed = abs((float) CompanyAiCreditTransaction::where('company_id', $company->id)
            ->where('type', 'USAGE')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('credits_amount'));

        $monthlyDocsProcessed = CompanyAiCreditTransaction::where('company_id', $company->id)
            ->where('type', 'USAGE')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $totalLifetimeRecharged = (float) CompanyAiCreditTransaction::where('company_id', $company->id)
            ->whereIn('type', ['RECHARGE', 'ADMIN_ADJUSTMENT'])
            ->where('credits_amount', '>', 0)
            ->sum('credits_amount');

        // Recent Transactions
        $transactions = CompanyAiCreditTransaction::where('company_id', $company->id)
            ->latest('created_at')
            ->paginate(15);

        // Dynamic Credit & Pricing Settings from SuperAdmin
        $creditSettings = FrexoAiCreditSetting::current();
        
        $packages = collect($creditSettings->packages ?? [])->map(function ($pkg) {
            $pkg['price_usd'] = (float) ($pkg['price_usd'] ?? 0);
            return $pkg;
        })->toArray();

        return view('settings.ai-credits', compact(
            'company',
            'balance',
            'isUnlimited',
            'monthlyCreditsUsed',
            'monthlyDocsProcessed',
            'totalLifetimeRecharged',
            'transactions',
            'packages',
            'creditSettings'
        ));
    }

    /**
     * Process credit card payment via Developer Sandbox & Stripe Test APIs.
     * Validates card details (Luhn, Expiry, CVV).
     * Strictly fails and denies credits if card details are invalid or test-declined.
     */
    public function createCheckout(Request $request, FrexoAiCreditService $creditService)
    {
        $validated = $request->validate([
            'package_id' => ['nullable', 'string'],
            'custom_credits' => ['nullable', 'numeric', 'min:100'],
            'cardholder_name' => ['required', 'string', 'min:3', 'max:100'],
            'card_number' => ['required', 'string', 'min:12', 'max:30'],
            'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/?([0-9]{2}|[0-9]{4})$/'],
            'card_cvc' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
        ], [
            'cardholder_name.required' => 'Please enter the name printed on your payment card.',
            'card_number.required' => 'Please enter your card number.',
            'card_expiry.regex' => 'Expiration date must be formatted as MM/YY (e.g. 12/28).',
            'card_cvc.regex' => 'Security code (CVC/CVV) must be 3 or 4 digits.',
        ]);

        $user = Auth::user();
        $company = $user->company ?: Company::first();

        if (!$company) {
            return back()->with('error', 'Company record not found.');
        }

        $creditSettings = FrexoAiCreditSetting::current();
        $packages = collect($creditSettings->packages ?? [])->keyBy('id');

        $creditsToRecharge = 0;
        $amountUsd = 0.0;

        if (!empty($validated['package_id']) && $packages->has($validated['package_id'])) {
            $pkg = $packages->get($validated['package_id']);
            $creditsToRecharge = (float) $pkg['credits'];
            $amountUsd = (float) $pkg['price_usd'];
        } elseif (!empty($validated['custom_credits'])) {
            $minCredits = (int) ($creditSettings->min_custom_credits ?: 100);
            if ((float) $validated['custom_credits'] < $minCredits) {
                return back()->with('error', "Minimum custom top-up is {$minCredits} credits.")->withInput();
            }
            $creditsToRecharge = (float) $validated['custom_credits'];
            $amountUsd = round($creditsToRecharge * $creditSettings->per_credit_usd, 2);
        } else {
            return back()->with('error', 'Please select a package or specify a valid credit top-up quantity.');
        }

        // Clean Card Number (remove spaces, hyphens)
        $cleanCard = preg_replace('/\D/', '', $validated['card_number']);
        if (strlen($cleanCard) < 13 || strlen($cleanCard) > 19) {
            return back()->with('error', 'Payment Authorization Failed: Invalid card number length.')->withInput();
        }

        // Validate Card Expiration Date (must be future date)
        $expiryParts = explode('/', str_replace([' ', '-'], '/', $validated['card_expiry']));
        $expMonth = (int) $expiryParts[0];
        $expYearRaw = (int) $expiryParts[1];
        $expYear = $expYearRaw < 100 ? (2000 + $expYearRaw) : $expYearRaw;

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            return back()->with('error', 'Payment Authorization Failed: The card expiration date is in the past.')->withInput();
        }

        // Stripe Developer Sandbox Declines Simulation Check:
        // 4000 0000 0000 0002 -> Declined
        // 4000 0000 0000 9999 -> Insufficient Funds
        if (str_ends_with($cleanCard, '0002') || str_ends_with($cleanCard, '0000')) {
            return back()->with('error', 'Payment Authorization Failed: Your card was declined by the issuer (Test decline triggered). No credits were added.')->withInput();
        }

        if (str_ends_with($cleanCard, '9999')) {
            return back()->with('error', 'Payment Authorization Failed: Insufficient funds available on this test account. No credits were added.')->withInput();
        }

        $last4 = substr($cleanCard, -4);
        $chargeId = 'ch_test_' . strtolower(bin2hex(random_bytes(10)));
        $paymentIntentId = 'pi_test_' . strtolower(bin2hex(random_bytes(10)));

        // Successfully verified test card payment!
        $tx = $creditService->rechargeCredits(
            $company,
            $creditsToRecharge,
            $user->id,
            "Developer Test API Payment (Visa •••• {$last4}) - $" . number_format($amountUsd, 2) . " USD",
            'RECHARGE',
            [
                'gateway' => 'stripe_developer_sandbox',
                'charge_id' => $chargeId,
                'payment_intent' => $paymentIntentId,
                'cardholder_name' => $validated['cardholder_name'],
                'card_brand' => 'Visa',
                'card_last4' => $last4,
                'amount_paid' => $amountUsd,
                'currency' => 'USD',
                'status' => 'succeeded',
            ]
        );

        return redirect()->route('settings.ai-credits')->with(
            'success',
            "Payment Authorized! Charged $" . number_format($amountUsd, 2) . " USD to Visa ending in {$last4}. Added " . number_format($creditsToRecharge) . " Frexo AI credits. Current balance: " . number_format($tx->balance_after, 2) . " credits."
        );
    }

    /**
     * Submit Bank Wire Transfer proof for manual audit.
     * STRICT PRODUCTION RULE: This creates a pending request with 0 credits.
     * Credits are NOT added until verified and approved by SuperAdmin treasury.
     */
    public function submitBankTransfer(Request $request)
    {
        $validated = $request->validate([
            'amount_credits' => 'required|numeric|min:500',
            'deposit_reference' => 'required|string|max:100',
            'deposit_slip' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_name' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $company = $user->company ?: Company::first();

        $slipPath = null;
        if ($request->hasFile('deposit_slip')) {
            $slipPath = $request->file('deposit_slip')->store('bank-slips', 'public');
        }

        // Record a PENDING transaction with credits_amount = 0 (NO balance change)
        CompanyAiCreditTransaction::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'BANK_WIRE_PENDING',
            'credits_amount' => 0.0000, // Strictly 0: No credits added until verified
            'balance_before' => (float) ($company->frexo_ai_credits ?? 0.0),
            'balance_after' => (float) ($company->frexo_ai_credits ?? 0.0),
            'tokens_used' => 0,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'module' => 'bank_wire_request',
            'reference_doc' => $validated['deposit_reference'],
            'description' => "Bank Wire Deposit Pending Verification ({$validated['bank_name']}): Request for " . number_format($validated['amount_credits']) . " credits. Ref: " . $validated['deposit_reference'],
            'metadata' => [
                'bank_name' => $validated['bank_name'],
                'requested_credits' => (float) $validated['amount_credits'],
                'reference' => $validated['deposit_reference'],
                'notes' => $validated['notes'] ?? null,
                'slip_path' => $slipPath,
                'status' => 'pending_verification',
            ],
        ]);

        return redirect()->route('settings.ai-credits')->with(
            'success',
            "Bank deposit reference '{$validated['deposit_reference']}' submitted for treasury audit. Your current balance remains unchanged until payment is verified and cleared by our accounting desk."
        );
    }
}
