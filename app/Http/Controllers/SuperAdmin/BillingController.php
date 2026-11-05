<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\ModulePackage;
use App\Models\SuperAdminAuditLog;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display SaaS billing, plans, and tenant subscriptions console.
     */
    public function index(Request $request)
    {
        $query = Company::withCount(['users', 'modules']);

        if ($request->filled('status')) {
            $query->where('subscription_status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('code', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $companies = $query->orderByRaw("FIELD(subscription_status, 'active', 'trial', 'suspended', 'expired')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $plans = ModulePackage::where('is_active', true)->get();

        // Calculate SaaS Billing Financial Metrics
        $activeTenantsCount = Company::where('subscription_status', 'active')->count();
        $trialTenantsCount = Company::where('subscription_status', 'trial')->count();
        
        $mrr = DB::table('companies')
            ->join('module_packages', 'companies.plan_id', '=', 'module_packages.id')
            ->where('companies.subscription_status', 'active')
            ->sum('module_packages.price');

        if ($mrr <= 0 && $activeTenantsCount > 0) {
            $mrr = $activeTenantsCount * 250;
        }

        // Currency conversion scoped strictly to authenticated Super Admin
        $user = auth()->user();
        $userCurrencyCode = $user ? $user->getSetting('base_currency', 'USD') : 'USD';
        $userCurrency = \App\Models\Currency::where('code', $userCurrencyCode)->first() 
                     ?? \App\Models\Currency::where('code', 'USD')->first();

        $exchangeRate = (float) ($userCurrency->exchange_rate ?? 1.0);
        $currencySymbol = $userCurrency->symbol ?? '$';
        if ($exchangeRate <= 0) $exchangeRate = 1.0;

        $mrrConverted = $mrr * $exchangeRate;
        $arrConverted = $mrrConverted * 12;
        $arpuConverted = $activeTenantsCount > 0 ? round($mrrConverted / $activeTenantsCount) : 0;

        $stats = [
            'mrr'            => $mrrConverted,
            'arr'            => $arrConverted,
            'active_paid'    => $activeTenantsCount,
            'active_trials'  => $trialTenantsCount,
            'arpu'           => $arpuConverted,
            'total_tenants'  => Company::count(),
            'currency_code'  => $userCurrencyCode,
            'currency_symbol'=> $currencySymbol,
            'exchange_rate'  => $exchangeRate,
        ];

        return view('super-admin.billing.index', compact('companies', 'plans', 'stats'));
    }

    /**
     * Update tenant subscription tier and quota parameters.
     */
    public function updateSubscription(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id'               => 'nullable|exists:module_packages,id',
            'subscription_status'   => 'required|in:active,trial,suspended,expired',
            'user_limit'            => 'nullable|integer|min:1',
            'storage_limit'         => 'nullable|integer|min:1',
            'subscription_ends_at'  => 'nullable|date',
        ]);

        $company->update($validated);

        if (class_exists(SuperAdminAuditLog::class)) {
            SuperAdminAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update_subscription',
                'target_type' => 'Company',
                'target_id'   => $company->id,
                'details'     => [
                    'company' => $company->name,
                    'status'  => $validated['subscription_status'],
                    'plan_id' => $validated['plan_id'] ?? null,
                ],
                'ip_address'  => $request->ip(),
            ]);
        }

        return back()->with('success', "Subscription for {$company->name} updated successfully.");
    }
}

