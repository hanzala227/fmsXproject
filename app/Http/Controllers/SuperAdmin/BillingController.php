<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\ModulePackage;
use App\Models\SuperAdminAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BillingController extends Controller
{
    /**
     * Display SaaS billing, plans, tenant subscriptions, and invoice ledger console.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'subscriptions');

        // CSV Export of Tenant Billing Metrics
        if ($request->get('export') === 'csv') {
            return $this->exportBillingCsv($request);
        }

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
        $suspendedTenantsCount = Company::where('subscription_status', 'suspended')->count();
        
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
            'mrr'               => $mrrConverted,
            'arr'               => $arrConverted,
            'active_paid'       => $activeTenantsCount,
            'active_trials'     => $trialTenantsCount,
            'suspended_tenants' => $suspendedTenantsCount,
            'arpu'              => $arpuConverted,
            'total_tenants'     => Company::count(),
            'currency_code'     => $userCurrencyCode,
            'currency_symbol'   => $currencySymbol,
            'exchange_rate'     => $exchangeRate,
        ];

        // Invoices Stream when tab is 'invoices'
        $invoices = null;
        if ($tab === 'invoices' && Schema::hasTable('invoices')) {
            $invQuery = DB::table('invoices')
                ->leftJoin('companies', 'invoices.company_id', '=', 'companies.id')
                ->select(
                    'invoices.id',
                    'invoices.invoice_no',
                    'invoices.invoice_date',
                    'invoices.due_date',
                    'invoices.total_amount',
                    'invoices.paid_amount',
                    'invoices.balance_amount',
                    'invoices.status',
                    'companies.name as company_name',
                    'companies.code as company_code'
                )
                ->orderByDesc('invoices.created_at');

            if ($search = $request->get('search')) {
                $invQuery->where(function ($q) use ($search) {
                    $q->where('invoices.invoice_no', 'like', "%{$search}%")
                      ->orWhere('companies.name', 'like', "%{$search}%");
                });
            }

            if ($status = $request->get('invoice_status')) {
                $invQuery->where('invoices.status', $status);
            }

            $invoices = $invQuery->paginate(15)->withQueryString();
        }

        return view('super-admin.billing.index', compact('companies', 'plans', 'stats', 'tab', 'invoices'));
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
            'frexo_ai_unlimited'    => 'nullable|boolean',
            'frexo_ai_credits'      => 'nullable|integer|min:0',
        ]);

        $validated['frexo_ai_unlimited'] = $request->boolean('frexo_ai_unlimited');

        $company->update($validated);

        if (class_exists(SuperAdminAuditLog::class)) {
            SuperAdminAuditLog::log('update_subscription', 'company', $company->id, [
                'company' => $company->name,
                'status'  => $validated['subscription_status'],
                'plan_id' => $validated['plan_id'] ?? null,
                'user_limit' => $validated['user_limit'] ?? null,
                'storage_limit' => $validated['storage_limit'] ?? null,
                'ends_at' => $validated['subscription_ends_at'] ?? null,
            ]);
        }

        return back()->with('success', "Subscription and quotas for {$company->name} updated successfully.");
    }

    /**
     * Quick toggle company suspension status
     */
    public function toggleStatus(Company $company)
    {
        $newStatus = $company->subscription_status === 'suspended' ? 'active' : 'suspended';
        $company->update(['subscription_status' => $newStatus]);

        if (class_exists(SuperAdminAuditLog::class)) {
            SuperAdminAuditLog::log('status_changed', 'company', $company->id, [
                'company' => $company->name,
                'new_status' => $newStatus,
            ]);
        }

        $label = $newStatus === 'active' ? 'activated' : 'suspended';
        return back()->with('success', "Company {$company->name} has been {$label}.");
    }

    /**
     * Export full tenant subscription billing report to CSV
     */
    protected function exportBillingCsv(Request $request)
    {
        $fileName = 'freightx-tenant-billing-' . date('Y-m-d-His') . '.csv';

        $companies = Company::with(['plan'])->withCount('users')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['FreightX SaaS Enterprise - Tenant Subscriptions & Billing Report']);
            fputcsv($handle, ['Export Timestamp', now()->toIso8601String()]);
            fputcsv($handle, ['Total Subscribed Tenants', $companies->count()]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'ID',
                'Company Name',
                'Code',
                'Email',
                'Country',
                'Commercial Plan',
                'Monthly Price (USD)',
                'Subscription Status',
                'Active Users',
                'User Limit',
                'Storage Limit (GB)',
                'AI Unlimited',
                'AI Credits',
                'Renewal Date',
                'Onboarding Date'
            ]);

            foreach ($companies as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->code,
                    $c->email,
                    $c->country,
                    $c->plan ? $c->plan->name : 'Unassigned',
                    $c->plan ? number_format($c->plan->price, 2) : '0.00',
                    strtoupper($c->subscription_status ?? 'TRIAL'),
                    $c->users_count,
                    $c->user_limit ?? 25,
                    $c->storage_limit ?? 50,
                    $c->frexo_ai_unlimited ? 'YES' : 'NO',
                    $c->frexo_ai_credits ?? 0,
                    $c->subscription_ends_at ? $c->subscription_ends_at->format('Y-m-d') : 'Evaluation',
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
