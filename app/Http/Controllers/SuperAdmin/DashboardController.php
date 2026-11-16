<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CarrierApiConfig;
use App\Models\Company;
use App\Models\SuperAdminAuditLog;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Overview tab (default)
     */
    public function index(Request $request)
    {
        $selectedDate = $request->get('date');
        $asOfDate = null;
        if (!empty($selectedDate)) {
            try {
                $asOfDate = Carbon::parse($selectedDate)->endOfDay();
            } catch (\Throwable $e) {
                $selectedDate = null;
                $asOfDate = null;
            }
        }

        $applyAsOf = function ($query, $dateCol = 'created_at') use ($asOfDate) {
            if ($asOfDate) {
                $query->where($dateCol, '<=', $asOfDate);
            }
            return $query;
        };

        // Dynamic 6-Month Trend Data anchored to selected date or current time
        $anchorDate = $asOfDate ? $asOfDate->copy() : Carbon::now();
        $months = [];
        $companyGrowthData = [];
        $oceanVolumeData = [];
        $airVolumeData = [];
        $truckVolumeData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $anchorDate->copy()->subMonths($i);
            $start = $monthDate->copy()->startOfMonth();
            $end = $monthDate->copy()->endOfMonth();
            if ($asOfDate && $end->gt($asOfDate)) {
                $end = $asOfDate->copy();
            }
            $label = $monthDate->format('M');
            $months[] = $label;

            if ($start->gt($end)) {
                $companyGrowthData[] = 0;
                $oceanVolumeData[] = 0;
                $airVolumeData[] = 0;
                $truckVolumeData[] = 0;
            } else {
                $companyGrowthData[] = Company::whereBetween('created_at', [$start, $end])->count();

                $oceanCount = DB::table('ocean_imports')->whereBetween('created_at', [$start, $end])->count()
                            + DB::table('ocean_exports')->whereBetween('created_at', [$start, $end])->count();
                $oceanVolumeData[] = $oceanCount;

                $airCount = DB::table('air_imports')->whereBetween('created_at', [$start, $end])->count()
                          + DB::table('air_exports')->whereBetween('created_at', [$start, $end])->count();
                $airVolumeData[] = $airCount;

                $truckCount = DB::table('truck_shipments')->whereBetween('created_at', [$start, $end])->count();
                $truckVolumeData[] = $truckCount;
            }
        }

        // Operational Counts as of selected date
        $oceanImports = $applyAsOf(DB::table('ocean_imports'))->count();
        $oceanExports = $applyAsOf(DB::table('ocean_exports'))->count();
        $oceanShipments = $oceanImports + $oceanExports;
        $oceanContainers = $applyAsOf(DB::table('ocean_import_containers'))->count() + $applyAsOf(DB::table('ocean_export_containers'))->count();
        $oceanHbls = $applyAsOf(DB::table('ocean_import_hbls'))->count() + $applyAsOf(DB::table('ocean_export_hbls'))->count();

        $airImports = $applyAsOf(DB::table('air_imports'))->count();
        $airExports = $applyAsOf(DB::table('air_exports'))->count();
        $airShipments = $airImports + $airExports;
        $airHawbs = $applyAsOf(DB::table('air_import_hbls'))->count() + $applyAsOf(DB::table('air_export_hbls'))->count();

        $truckShipments = $applyAsOf(DB::table('truck_shipments'))->count();
        $totalMovements = $oceanShipments + $airShipments + $truckShipments;

        $activeCarrierApis = Schema::hasTable('carrier_api_configs')
            ? CarrierApiConfig::where('is_active', true)->where('carrier_code', 'shipmentbeat')->count()
            : 0;

        $invoicedSum = Schema::hasTable('invoices')
            ? (float) $applyAsOf(DB::table('invoices')->where('status', '!=', 'cancelled'))->sum('total_amount')
            : 0;

        // Base subscription revenue per active tenant as of selected date
        $activeTenantsQuery = Company::where('subscription_status', 'active');
        $activeTenantsCount = $applyAsOf($activeTenantsQuery)->count();

        $revenueQuery = DB::table('companies')
            ->join('module_packages', 'companies.plan_id', '=', 'module_packages.id')
            ->where('companies.subscription_status', 'active');
        if ($asOfDate) {
            $revenueQuery->where('companies.created_at', '<=', $asOfDate);
        }
        $monthlyRevenue = (float) $revenueQuery->sum('module_packages.price');

        if ($monthlyRevenue <= 0 && $activeTenantsCount > 0) {
            $monthlyRevenue = $activeTenantsCount * 250;
        }

        // Currency & Exchange Rate conversion scoped to logged-in user
        $user = auth()->user();
        $userCurrencyCode = $user ? $user->getSetting('base_currency', 'USD') : 'USD';
        $userCurrency = \App\Models\Currency::where('code', $userCurrencyCode)->first() 
                     ?? \App\Models\Currency::where('code', 'USD')->first();

        $exchangeRate = (float) ($userCurrency->exchange_rate ?? 1.0);
        $currencySymbol = $userCurrency->symbol ?? '$';
        if ($exchangeRate <= 0) $exchangeRate = 1.0;

        $convertedMonthlyRevenue = $monthlyRevenue * $exchangeRate;
        $convertedInvoicedSum = $invoicedSum * $exchangeRate;

        // Recent Freight Movements across all modes as of date
        $recentOceanQuery = DB::table('ocean_imports')
            ->leftJoin('companies', 'ocean_imports.company_id', '=', 'companies.id')
            ->select(
                'ocean_imports.id',
                'ocean_imports.mbl_no as ref_no',
                'ocean_imports.ship_mode',
                'ocean_imports.created_at',
                'companies.name as company_name',
                DB::raw('"Ocean Import" as mode'),
                DB::raw('"#0284c7" as mode_color'),
                DB::raw('"fa-ship" as mode_icon')
            )
            ->orderByDesc('ocean_imports.created_at');
        $recentOcean = $applyAsOf($recentOceanQuery, 'ocean_imports.created_at')->take(3)->get();

        $recentAirQuery = DB::table('air_imports')
            ->leftJoin('companies', 'air_imports.company_id', '=', 'companies.id')
            ->select(
                'air_imports.id',
                'air_imports.mawb_no as ref_no',
                'air_imports.ship_mode',
                'air_imports.created_at',
                'companies.name as company_name',
                DB::raw('"Air Import" as mode'),
                DB::raw('"#6366f1" as mode_color'),
                DB::raw('"fa-plane" as mode_icon')
            )
            ->orderByDesc('air_imports.created_at');
        $recentAir = $applyAsOf($recentAirQuery, 'air_imports.created_at')->take(3)->get();

        $recentTruckQuery = DB::table('truck_shipments')
            ->leftJoin('companies', 'truck_shipments.company_id', '=', 'companies.id')
            ->select(
                'truck_shipments.id',
                'truck_shipments.mbl_no as ref_no',
                'truck_shipments.ship_type as ship_mode',
                'truck_shipments.created_at',
                'companies.name as company_name',
                DB::raw('"Land Transport" as mode'),
                DB::raw('"#f59e0b" as mode_color'),
                DB::raw('"fa-truck" as mode_icon')
            )
            ->orderByDesc('truck_shipments.created_at');
        $recentTruck = $applyAsOf($recentTruckQuery, 'truck_shipments.created_at')->take(2)->get();

        $recentShipments = $recentOcean->concat($recentAir)->concat($recentTruck)->sortByDesc('created_at')->take(5)->values();

        // Open bugs and support tickets as of date
        $openBugsQuery = Ticket::where('status', 'Open');
        $openBugsCount = $applyAsOf($openBugsQuery)->count();

        $openSupportTicketsQuery = Ticket::whereIn('status', ['Open', 'In Progress']);
        $openSupportTicketsCount = $applyAsOf($openSupportTicketsQuery)->count();

        $openTicketsQuery = Ticket::with(['company', 'user'])
            ->where('status', 'Open')
            ->latest();
        $openTicketsList = $applyAsOf($openTicketsQuery)->take(4)->get();

        $totalCompanies = $applyAsOf(Company::query())->count();
        $trialCompanies = $applyAsOf(Company::where('subscription_status', 'trial'))->count();
        $suspendedCompanies = $applyAsOf(Company::where(function ($q) {
            $q->where('status', 'inactive')->orWhere('subscription_status', 'suspended');
        }))->count();
        $totalUsers = $applyAsOf(User::where('role', '!=', 'SuperAdmin'))->count();

        $recentCompaniesQuery = Company::withCount('users')->latest();
        $recentCompanies = $applyAsOf($recentCompaniesQuery)->take(5)->get();

        $recentLogsQuery = SuperAdminAuditLog::with('user')->latest('created_at');
        $recentLogs = $applyAsOf($recentLogsQuery, 'created_at')->take(5)->get();

        $stats = [
            'total_companies' => $totalCompanies,
            'active_companies' => $activeTenantsCount,
            'trial_companies' => $trialCompanies,
            'suspended_companies' => $suspendedCompanies,
            'total_users' => $totalUsers,
            'open_bugs' => $openBugsCount,
            'support_tickets' => $openSupportTicketsCount,
            'active_migrations' => Schema::hasTable('migration_projects') ? $applyAsOf(DB::table('migration_projects')->where('status', 'in_progress'))->count() : 0,
            'monthly_revenue' => $convertedMonthlyRevenue,
            'platform_invoiced_volume' => $convertedInvoicedSum,
            'currency_code' => $userCurrencyCode,
            'currency_symbol' => $currencySymbol,
            
            // Freight Pulse
            'ocean_shipments' => $oceanShipments,
            'ocean_containers' => $oceanContainers,
            'ocean_hbls' => $oceanHbls,
            'air_shipments' => $airShipments,
            'air_hawbs' => $airHawbs,
            'truck_shipments' => $truckShipments,
            'total_freight_movements' => $totalMovements,
            'active_carrier_apis' => $activeCarrierApis,

            'recent_companies' => $recentCompanies,
            'recent_logs' => $recentLogs,
            'recent_shipments' => $recentShipments,
            'open_tickets_list' => $openTicketsList,
            
            // Real charts
            'monthly_growth' => [
                'labels' => $months,
                'data' => $companyGrowthData,
            ],
            'freight_trends' => [
                'labels' => $months,
                'ocean' => $oceanVolumeData,
                'air' => $airVolumeData,
                'truck' => $truckVolumeData,
            ],
            'selected_date' => $selectedDate,
        ];

        return view('super-admin.dashboard.index', compact('stats'));
    }

    /**
     * Reports tab with full filtering, metrics, and modal breakdown
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date') ?: $request->get('date');
        $companyId = $request->get('company_id');

        $applyFilter = function ($query, $dateCol = 'created_at', $companyCol = 'company_id') use ($startDate, $endDate, $companyId) {
            if ($companyId) {
                $query->where($companyCol, $companyId);
            }
            if ($startDate) {
                $query->whereDate($dateCol, '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate($dateCol, '<=', $endDate);
            }
            return $query;
        };

        $oceanImports = $applyFilter(DB::table('ocean_imports'))->count();
        $oceanExports = $applyFilter(DB::table('ocean_exports'))->count();
        $oceanTotal = $oceanImports + $oceanExports;

        $airImports = $applyFilter(DB::table('air_imports'))->count();
        $airExports = $applyFilter(DB::table('air_exports'))->count();
        $airTotal = $airImports + $airExports;

        $truckTotal = $applyFilter(DB::table('truck_shipments'))->count();
        $grandTotal = max($oceanTotal + $airTotal + $truckTotal, 1);

        $invoicesQuery = $applyFilter(DB::table('invoices')->where('status', '!=', 'cancelled'));
        $invoicesCount = $applyFilter(DB::table('invoices'))->count();
        $invoicesSum = (float) $invoicesQuery->sum('total_amount');

        // Containers count in period
        $oceanContainers = $applyFilter(DB::table('ocean_import_containers'))->count()
                         + $applyFilter(DB::table('ocean_export_containers'))->count();

        // 6-Month Volume Trends
        $trendMonths = [];
        $trendOcean = [];
        $trendAir = [];
        $trendTruck = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end = $m->copy()->endOfMonth();
            $trendMonths[] = $m->format('M Y');

            $qOceanImp = DB::table('ocean_imports')->whereBetween('created_at', [$start, $end]);
            $qOceanExp = DB::table('ocean_exports')->whereBetween('created_at', [$start, $end]);
            $qAirImp = DB::table('air_imports')->whereBetween('created_at', [$start, $end]);
            $qAirExp = DB::table('air_exports')->whereBetween('created_at', [$start, $end]);
            $qTruck = DB::table('truck_shipments')->whereBetween('created_at', [$start, $end]);

            if ($companyId) {
                $qOceanImp->where('company_id', $companyId);
                $qOceanExp->where('company_id', $companyId);
                $qAirImp->where('company_id', $companyId);
                $qAirExp->where('company_id', $companyId);
                $qTruck->where('company_id', $companyId);
            }

            $trendOcean[] = $qOceanImp->count() + $qOceanExp->count();
            $trendAir[] = $qAirImp->count() + $qAirExp->count();
            $trendTruck[] = $qTruck->count();
        }

        // Company Breakdown Table
        $companiesQuery = Company::withCount('users');
        if ($companyId) {
            $companiesQuery->where('id', $companyId);
        }
        $companyActivity = $companiesQuery->orderByDesc('created_at')->paginate(15)->appends($request->query());

        // Append throughput metrics for each company in the paginated set
        $companyActivity->getCollection()->transform(function ($c) use ($startDate, $endDate) {
            $applyDate = function ($q) use ($startDate, $endDate) {
                if ($startDate) $q->whereDate('created_at', '>=', $startDate);
                if ($endDate) $q->whereDate('created_at', '<=', $endDate);
                return $q;
            };

            $c->ocean_count = $applyDate(DB::table('ocean_imports')->where('company_id', $c->id))->count()
                            + $applyDate(DB::table('ocean_exports')->where('company_id', $c->id))->count();
            $c->air_count = $applyDate(DB::table('air_imports')->where('company_id', $c->id))->count()
                          + $applyDate(DB::table('air_exports')->where('company_id', $c->id))->count();
            $c->truck_count = $applyDate(DB::table('truck_shipments')->where('company_id', $c->id))->count();
            $c->total_shipments = $c->ocean_count + $c->air_count + $c->truck_count;
            $c->invoiced_sum = (float) $applyDate(DB::table('invoices')->where('company_id', $c->id)->where('status', '!=', 'cancelled'))->sum('total_amount');

            return $c;
        });

        $allCompanies = Company::orderBy('name')->get(['id', 'name', 'code']);

        $reports = [
            'total_migrations' => Schema::hasTable('migration_projects') ? DB::table('migration_projects')->count() : 0,
            'active_tickets' => Ticket::where('status', 'Open')->count(),
            'resolved_tickets' => Ticket::where('status', 'Closed')->count(),
            'open_bugs' => Ticket::where('status', 'Open')->count(),

            'ocean_share_pct' => round(($oceanTotal / $grandTotal) * 100, 1),
            'air_share_pct' => round(($airTotal / $grandTotal) * 100, 1),
            'truck_share_pct' => round(($truckTotal / $grandTotal) * 100, 1),

            'ocean_imports' => $oceanImports,
            'ocean_exports' => $oceanExports,
            'ocean_total' => $oceanTotal,
            'air_imports' => $airImports,
            'air_exports' => $airExports,
            'air_total' => $airTotal,
            'truck_total' => $truckTotal,
            'grand_total' => ($oceanTotal + $airTotal + $truckTotal),
            'ocean_containers' => $oceanContainers,

            'invoices_count' => $invoicesCount,
            'invoices_sum' => $invoicesSum,

            'trend_months' => $trendMonths,
            'trend_ocean' => $trendOcean,
            'trend_air' => $trendAir,
            'trend_truck' => $trendTruck,

            'company_activity' => $companyActivity,
            'all_companies' => $allCompanies,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'company_id' => $companyId,
            ]
        ];

        return view('super-admin.dashboard.reports', compact('reports'));
    }

    /**
     * System Status tab
     */
    public function systemStatus()
    {
        return redirect()->route('super-admin.releases.index');
    }

    /**
     * Real CSV Export of Platform Statistics & Tenant Activity with filters
     */
    public function export(Request $request)
    {
        $fileName = 'freightx-platform-metrics-' . date('Y-m-d-His') . '.csv';

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date') ?: $request->get('date');
        $companyId = $request->get('company_id');

        $companiesQuery = Company::withCount('users');
        if ($companyId) {
            $companiesQuery->where('id', $companyId);
        }
        $companies = $companiesQuery->get();

        $applyDate = function ($q) use ($startDate, $endDate) {
            if ($startDate) $q->whereDate('created_at', '>=', $startDate);
            if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            return $q;
        };

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($companies, $applyDate, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Header line
            fputcsv($handle, ['FreightX SaaS Enterprise Platform - Super Admin Operations Report']);
            fputcsv($handle, ['Export Timestamp', Carbon::now()->toIso8601String()]);
            fputcsv($handle, ['Filter Range', ($startDate ?: 'All Time') . ' to ' . ($endDate ?: 'Present')]);
            fputcsv($handle, []);

            // Tenants Section
            fputcsv($handle, [
                'Company ID',
                'Company Name',
                'Code',
                'Country',
                'Subdomain',
                'Status',
                'Subscription',
                'Active Users',
                'Ocean Shipments',
                'Air Shipments',
                'Land Drayage',
                'Total Movements',
                'Total Invoiced Volume',
                'Created Date'
            ]);

            foreach ($companies as $c) {
                $ocean = $applyDate(DB::table('ocean_imports')->where('company_id', $c->id))->count()
                       + $applyDate(DB::table('ocean_exports')->where('company_id', $c->id))->count();
                $air = $applyDate(DB::table('air_imports')->where('company_id', $c->id))->count()
                     + $applyDate(DB::table('air_exports')->where('company_id', $c->id))->count();
                $truck = $applyDate(DB::table('truck_shipments')->where('company_id', $c->id))->count();
                $totalMov = $ocean + $air + $truck;
                $invoiced = (float) $applyDate(DB::table('invoices')->where('company_id', $c->id)->where('status', '!=', 'cancelled'))->sum('total_amount');

                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->code ?? 'N/A',
                    $c->country ?? 'Global',
                    $c->subdomain ?? 'app',
                    ucfirst($c->status),
                    ucfirst($c->subscription_status ?? 'Trial'),
                    $c->users_count,
                    $ocean,
                    $air,
                    $truck,
                    $totalMov,
                    number_format($invoiced, 2),
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
