<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\CompanyAiCreditTransaction;
use App\Models\FrexoAiCreditSetting;
use App\Models\SuperAdminAuditLog;
use App\Services\AI\AiManager;
use App\Services\FrexoAiCreditService;
use Illuminate\Http\Request;
use Exception;

class AiIntegrationController extends Controller
{
    public function settings()
    {
        $settings = AiSetting::current();
        
        // If settings don't exist, create defaults
        if (!$settings) {
            $settings = AiSetting::create([
                'api_type' => 'openai_compatible',
                'base_url' => 'https://api.openai.com/v1',
                'model_name' => 'gpt-4o'
            ]);
        }

        return view('super-admin.integrations.ai-settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_type' => 'required|in:openai_compatible,gemini',
            'base_url' => 'required_if:api_type,openai_compatible|nullable|url',
            'api_key' => 'required|string',
            'model_name' => 'required|string',
        ]);

        $settings = AiSetting::current();
        
        $settings->update([
            'api_type' => $request->api_type,
            'base_url' => $request->api_type === 'gemini' ? null : ($request->base_url ?? 'https://api.openai.com/v1'),
            'api_key' => $request->api_key,
            'model_name' => $request->model_name,
        ]);

        return back()->with('success', 'Universal AI Settings updated successfully.');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $ai = AiManager::provider();
            $response = $ai->generateResponse($request->message);
            
            return response()->json([
                'success' => true,
                'reply' => $response
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display dynamic Frexo AI Credit Pricing, Packages, and Bank Wire audit console.
     */
    public function creditSettings()
    {
        $creditSettings = FrexoAiCreditSetting::current();

        $pendingWireRequests = CompanyAiCreditTransaction::with(['company', 'user'])
            ->where(function ($q) {
                $q->where('type', 'BANK_WIRE_PENDING')
                  ->orWhere(function ($sub) {
                      $sub->where('credits_amount', 0)
                          ->where('module', 'bank_wire_request');
                  });
            })
            ->latest('created_at')
            ->take(30)
            ->get();

        return view('super-admin.integrations.ai-credit-settings', compact('creditSettings', 'pendingWireRequests'));
    }

    /**
     * Update dynamic Frexo AI credit packages, rates, and bank details.
     */
    public function updateCreditSettings(Request $request)
    {
        $validated = $request->validate([
            'per_credit_usd' => 'required|numeric|min:0.0001|max:10',
            'min_custom_credits' => 'required|integer|min:1',
            'exchange_rate_pkr' => 'nullable|numeric|min:1',
            'initial_free_credits' => 'required|numeric|min:0',
            'packages' => 'required|array|min:1',
            'packages.*.id' => 'required|string|max:50',
            'packages.*.name' => 'required|string|max:100',
            'packages.*.credits' => 'required|numeric|min:1',
            'packages.*.price_usd' => 'required|numeric|min:0',
            'packages.*.popular' => 'nullable|boolean',
            'packages.*.desc' => 'nullable|string|max:300',
            'packages.*.features' => 'nullable|string|max:1000',
            'bank_name' => 'required|string|max:255',
            'bank_account_title' => 'required|string|max:255',
            'bank_iban' => 'required|string|max:100',
            'bank_swift' => 'nullable|string|max:50',
            'bank_instructions' => 'nullable|string|max:1000',
        ]);

        // Process packages and clean features array
        $formattedPackages = [];
        foreach ($validated['packages'] as $pkg) {
            $featuresList = [];
            if (!empty($pkg['features'])) {
                $featuresList = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $pkg['features'])))));
            }

            $formattedPackages[] = [
                'id' => strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $pkg['id']))),
                'name' => trim($pkg['name']),
                'credits' => (float) $pkg['credits'],
                'price_usd' => (float) $pkg['price_usd'],
                'popular' => !empty($pkg['popular']),
                'desc' => trim($pkg['desc'] ?? ''),
                'features' => $featuresList,
            ];
        }

        $creditSettings = FrexoAiCreditSetting::current();
        $creditSettings->update([
            'per_credit_usd' => (float) $validated['per_credit_usd'],
            'min_custom_credits' => (int) $validated['min_custom_credits'],
            'exchange_rate_pkr' => isset($validated['exchange_rate_pkr']) ? (float) $validated['exchange_rate_pkr'] : ($creditSettings->exchange_rate_pkr ?? 1.0),
            'initial_free_credits' => (float) $validated['initial_free_credits'],
            'packages' => $formattedPackages,
            'bank_name' => trim($validated['bank_name']),
            'bank_account_title' => trim($validated['bank_account_title']),
            'bank_iban' => trim($validated['bank_iban']),
            'bank_swift' => !empty($validated['bank_swift']) ? trim($validated['bank_swift']) : null,
            'bank_instructions' => trim($validated['bank_instructions'] ?? ''),
        ]);

        if (class_exists(SuperAdminAuditLog::class)) {
            SuperAdminAuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_ai_credit_settings',
                'target_type' => 'FrexoAiCreditSetting',
                'target_id' => $creditSettings->id,
                'details' => [
                    'per_credit_usd' => $validated['per_credit_usd'],
                    'packages_count' => count($formattedPackages),
                ],
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->with('success', 'Dynamic Frexo AI credit packages, pricing rates, and bank instructions updated successfully.');
    }

    /**
     * Approve a pending bank wire transfer deposit request.
     */
    public function approveBankWireRequest(Request $request, $id, FrexoAiCreditService $creditService)
    {
        $transaction = CompanyAiCreditTransaction::with('company')->findOrFail($id);

        $meta = is_array($transaction->metadata) ? $transaction->metadata : (json_decode($transaction->metadata, true) ?? []);
        if (($meta['status'] ?? null) === 'approved') {
            return back()->with('info', 'This bank deposit request has already been approved.');
        }

        $creditsToDeposit = (float) ($meta['requested_credits'] ?? 0);
        if ($creditsToDeposit <= 0) {
            return back()->with('error', 'Cannot approve request: Invalid or zero credit quantity specified in metadata.');
        }

        $tx = $creditService->rechargeCredits(
            $transaction->company,
            $creditsToDeposit,
            auth()->id(),
            "Bank Wire Transfer Verified & Approved (Ref: {$transaction->reference_doc})",
            'RECHARGE',
            [
                'original_request_id' => $transaction->id,
                'bank_name' => $meta['bank_name'] ?? 'Bank Wire',
                'reference' => $transaction->reference_doc,
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now()->toDateTimeString(),
            ]
        );

        $meta['status'] = 'approved';
        $meta['approved_at'] = now()->toDateTimeString();
        $meta['approved_by'] = auth()->user()->name;

        $transaction->update([
            'metadata' => $meta,
            'type' => 'RECHARGE',
            'credits_amount' => $creditsToDeposit,
            'balance_after' => $tx->balance_after,
            'description' => $transaction->description . " [APPROVED by SuperAdmin on " . now()->format('M d, Y') . "]",
        ]);

        return back()->with('success', "Bank wire request approved! " . number_format($creditsToDeposit) . " credits deposited to {$transaction->company->name}.");
    }

    /**
     * Reject a pending bank wire transfer deposit request.
     */
    public function rejectBankWireRequest(Request $request, $id)
    {
        $transaction = CompanyAiCreditTransaction::findOrFail($id);

        $meta = is_array($transaction->metadata) ? $transaction->metadata : (json_decode($transaction->metadata, true) ?? []);
        $meta['status'] = 'rejected';
        $meta['rejected_at'] = now()->toDateTimeString();
        $meta['rejected_by'] = auth()->user()->name;

        $transaction->update([
            'metadata' => $meta,
            'description' => $transaction->description . " [REJECTED by SuperAdmin on " . now()->format('M d, Y') . "]",
        ]);

        return back()->with('success', "Bank wire request '{$transaction->reference_doc}' has been marked as rejected.");
    }
}
