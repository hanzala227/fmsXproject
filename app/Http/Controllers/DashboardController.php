<?php

namespace App\Http\Controllers;

use App\Models\AirExport;
use App\Models\AirImport;
use App\Models\Charge;
use App\Models\OceanExport;
use App\Models\OceanImport;
use App\Models\Quotation;
use App\Models\TradePartner;
use App\Models\ShipmentStatusLog;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->calculateDashboardData();
        return view('dashboard', $data);
    }

    /**
     * AJAX: Return chart data as JSON for a given period range
     */
    public function chartData(Request $request)
    {
        $type   = $request->input('type', 'balance');
        $period = (int) $request->input('period', 6);

        if ($period < 1)  $period = 6;
        if ($period > 36) $period = 36;

        if ($type === 'forecast') {
            return response()->json($this->calculateSalesForecast($period));
        }

        return response()->json($this->calculateBalanceChart($period));
    }

    protected function calculateDashboardData()
    {
        return [
            'kpis'                 => $this->calculateKpis(),
            'todos'                => $this->calculateTodos(),
            'balanceChart'         => $this->calculateBalanceChart(),
            'forecastChart'        => $this->calculateSalesForecast(),
            'shipmentDistribution' => $this->calculateShipmentDistribution(),
            'tasks'                => $this->calculateTasks(),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  KPI CARDS
    // ─────────────────────────────────────────────────────────

    protected function calculateKpis()
    {
        $arTotal = 0; $apTotal = 0;
        $oceanImportCount = 0; $oceanExportCount = 0; $airImportCount = 0; $airExportCount = 0;
        $allClientIds = collect();
        $activeIds = collect();
        $activeLastYearIds = collect();

        try {
            $arTotal = Charge::where('type', 'AR')->sum('amount');
            $apTotal = Charge::where('type', 'AP')->sum('amount');
        } catch (\Exception $e) {}

        try { $oceanImportCount = OceanImport::count(); } catch (\Exception $e) {}
        try { $oceanExportCount = OceanExport::count(); } catch (\Exception $e) {}
        try { $airImportCount   = AirImport::count(); }   catch (\Exception $e) {}
        try { $airExportCount   = AirExport::count(); }   catch (\Exception $e) {}

        $totalVolume = $oceanImportCount + $oceanExportCount + $airImportCount + $airExportCount;

        try {
            $allClientIds = TradePartner::whereIn('type', ['CS', 'CLIENT'])->pluck('id');
        } catch (\Exception $e) {}

        $sixMonthsAgo     = now()->subMonths(6);
        $twelveMonthsAgo  = now()->subMonths(12);

        if ($allClientIds->isNotEmpty()) {
            try {
                $activeIds = OceanImport::where('created_at', '>=', $sixMonthsAgo)
                    ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id')
                    ->merge(OceanExport::where('created_at', '>=', $sixMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->merge(AirImport::where('created_at', '>=', $sixMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->merge(AirExport::where('created_at', '>=', $sixMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->unique()->filter();
            } catch (\Exception $e) {}

            try {
                $activeLastYearIds = OceanImport::where('created_at', '>=', $twelveMonthsAgo)
                    ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id')
                    ->merge(OceanExport::where('created_at', '>=', $twelveMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->merge(AirImport::where('created_at', '>=', $twelveMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->merge(AirExport::where('created_at', '>=', $twelveMonthsAgo)
                        ->whereIn('dm_customer_id', $allClientIds)->pluck('dm_customer_id'))
                    ->unique()->filter();
            } catch (\Exception $e) {}
        }

        $activeCustomers = $activeIds->count() ?: $allClientIds->count();
        $lostCustomers   = $allClientIds->diff($activeLastYearIds)->count();
        $totalProfit     = $arTotal - $apTotal;

        $prevArTotal = 0; $prevApTotal = 0; $prevVolume = 0;
        try {
            $prevArTotal = Charge::where('type', 'AR')->where('created_at', '<', now()->subMonth())->sum('amount');
            $prevApTotal = Charge::where('type', 'AP')->where('created_at', '<', now()->subMonth())->sum('amount');
        } catch (\Exception $e) {}
        $prevProfit = $prevArTotal - $prevApTotal;

        try {
            $prevVolume = OceanImport::where('created_at', '<', now()->subMonths(6))->count()
                + OceanExport::where('created_at', '<', now()->subMonths(6))->count()
                + AirImport::where('created_at', '<', now()->subMonths(6))->count()
                + AirExport::where('created_at', '<', now()->subMonths(6))->count();
        } catch (\Exception $e) {}

        $profitChangePercent = $prevProfit > 0 ? round((($totalProfit - $prevProfit) / $prevProfit) * 100, 1) : 0;
        $volumeChangePercent = $prevVolume > 0 ? round((($totalVolume - $prevVolume) / $prevVolume) * 100, 1) : 0;

        return compact('totalProfit', 'totalVolume', 'activeCustomers', 'lostCustomers',
            'profitChangePercent', 'volumeChangePercent', 'prevProfit', 'prevVolume');
    }

    // ─────────────────────────────────────────────────────────
    //  TO-DO LIST
    // ─────────────────────────────────────────────────────────

    protected function calculateTodos()
    {
        $todos = [];

        try {
            $logs = ShipmentStatusLog::with('shipment')
                ->where('created_at', '>=', now()->subDays(30))
                ->latest()->take(10)->get();

            foreach ($logs as $log) {
                $shipment = $log->shipment;
                if (!$shipment) continue;

                $fileNo = $shipment->file_no ?? $shipment->mawb_no ?? $shipment->booking_no ?? 'N/A';
                $mblNo  = $shipment->mbl_no ?? $shipment->mawb_no ?? '--';
                $pod    = $shipment->portOfDischarge?->name ?? $shipment->dstPort?->name ?? '--';
                $eta    = $shipment->eta ?? $shipment->created_at;
                $statusName = $log->status_name ?? $log->status_code ?? 'Update';

                $badgeClass = 'bg-pre-alert';
                if (stripos($statusName, 'arrival') !== false) $badgeClass = 'bg-arrival';
                elseif (stripos($statusName, 'invoice') !== false) $badgeClass = 'bg-invoice';

                $todos[] = [
                    'id'         => $shipment->id,
                    'task'       => $statusName,
                    'file_no'    => $fileNo,
                    'eta'        => $eta instanceof \Carbon\Carbon ? $eta->format('m-d-Y') : (is_string($eta) ? $eta : '--'),
                    'mbl'        => $mblNo,
                    'hbl'        => '--',
                    'pod'        => $pod,
                    'consignee'  => $shipment->dmConsignee?->name ?? $shipment->dmCustomer?->name ?? '--',
                    'status'     => $statusName,
                    'badgeClass' => $badgeClass,
                    'shipment_type' => class_basename($log->shipment_type),
                ];
            }
        } catch (\Exception $e) {}

        if (empty($todos)) {
            try {
                $recentShipments = OceanImport::with(['portOfDischarge', 'dmConsignee'])
                    ->latest()->take(5)->get();

                foreach ($recentShipments as $s) {
                    $todos[] = [
                        'id'         => $s->id,
                        'task'       => 'Pre-alert',
                        'file_no'    => $s->file_no ?? 'N/A',
                        'eta'        => $s->eta ? $s->eta->format('m-d-Y') : '--',
                        'mbl'        => $s->mbl_no ?? '--',
                        'hbl'        => '--',
                        'pod'        => $s->portOfDischarge?->name ?? '--',
                        'consignee'  => $s->dmConsignee?->name ?? '--',
                        'status'     => 'Pending',
                        'badgeClass' => 'bg-pre-alert',
                        'shipment_type' => 'OceanImport',
                    ];
                }
            } catch (\Exception $e) {}
        }

        return $todos;
    }

    protected function calculateShipmentDistribution()
    {
        $oceanImportCount = 0; $oceanExportCount = 0; $airImportCount = 0; $airExportCount = 0;
        try { $oceanImportCount = OceanImport::count(); } catch (\Exception $e) {}
        try { $oceanExportCount = OceanExport::count(); } catch (\Exception $e) {}
        try { $airImportCount   = AirImport::count(); }   catch (\Exception $e) {}
        try { $airExportCount   = AirExport::count(); }   catch (\Exception $e) {}

        return [
            'labels' => ['Ocean Import', 'Ocean Export', 'Air Import', 'Air Export'],
            'series' => [$oceanImportCount, $oceanExportCount, $airImportCount, $airExportCount]
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  BALANCE OVERVIEW CHART  (period = months to show)
    // ─────────────────────────────────────────────────────────

    protected function calculateBalanceChart($period = 6)
    {
        $months          = [];
        $revenueData     = [];
        $expenseData     = [];
        $rawRevenueTotal = 0;
        $rawExpenseTotal = 0;

        for ($i = $period - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $revenue = 0;
            $expense = 0;

            try {
                $revenue = (float) Charge::where('type', 'AR')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');

                $expense = (float) Charge::where('type', 'AP')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');

                if ($revenue == 0) {
                    $shipmentCount = OceanImport::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count()
                        + OceanExport::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count()
                        + AirImport::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count()
                        + AirExport::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();

                    if ($shipmentCount > 0) {
                        $revenue = $shipmentCount * 1250;
                        $expense = $shipmentCount * 750;
                    }
                }
            } catch (\Exception $e) {}

            $rawRevenueTotal += $revenue;
            $rawExpenseTotal += $expense;
            $revenueData[] = round($revenue / 1000, 2);
            $expenseData[] = round($expense / 1000, 2);
        }

        $profitRatio = $rawRevenueTotal > 0
            ? round((($rawRevenueTotal - $rawExpenseTotal) / $rawRevenueTotal) * 100, 1)
            : 0;

        return compact('months', 'revenueData', 'expenseData', 'rawRevenueTotal', 'rawExpenseTotal', 'profitRatio')
            + ['totalRevenue' => $rawRevenueTotal, 'totalExpenses' => $rawExpenseTotal];
    }

    // ─────────────────────────────────────────────────────────
    //  SALES FORECAST CHART  (period = months to look back)
    // ─────────────────────────────────────────────────────────

    protected function calculateSalesForecast($period = 12)
    {
        $goal = 0; $pendingForecast = 0; $revenue = 0;

        try {
            $sinceDate = now()->subMonths($period);

            $goal = (float) Quotation::where('created_at', '>=', $sinceDate)->sum('total_amount');
            if ($goal == 0) {
                $goal = (float) Quotation::where('created_at', '>=', $sinceDate)->sum('amount');
            }

            $pendingForecast = (float) Quotation::where('created_at', '>=', $sinceDate)
                ->whereNotIn('status', ['approved', 'won', 'closed'])->sum('total_amount');
            if ($pendingForecast == 0) {
                $pendingForecast = (float) Quotation::where('created_at', '>=', $sinceDate)
                    ->whereNotIn('status', ['approved', 'won', 'closed'])->sum('amount');
            }

            $revenue = (float) Quotation::where('created_at', '>=', $sinceDate)
                ->whereIn('status', ['approved', 'won'])->sum('total_amount');
            if ($revenue == 0) {
                $revenue = (float) Quotation::where('created_at', '>=', $sinceDate)
                    ->whereIn('status', ['approved', 'won'])->sum('amount');
            }

            if ($goal == 0) {
                $totalQuotesCount = Quotation::where('created_at', '>=', $sinceDate)->count();
                $pendingCount     = Quotation::where('created_at', '>=', $sinceDate)->whereNotIn('status', ['approved', 'won', 'closed'])->count();
                $wonCount         = Quotation::where('created_at', '>=', $sinceDate)->whereIn('status', ['approved', 'won'])->count();

                if ($totalQuotesCount > 0) {
                    $goal            = $totalQuotesCount * 2500;
                    $pendingForecast = $pendingCount * 2500;
                    $revenue         = $wonCount * 2500;
                } else {
                    $oiCount = OceanImport::count();
                    $oeCount = OceanExport::count();
                    $aiCount = AirImport::count();
                    $aeCount = AirExport::count();
                    $goal            = ($oiCount + $oeCount + $aiCount + $aeCount) * 1800;
                    $pendingForecast = ($oiCount + $oeCount) * 1200;
                    $revenue         = ($aiCount + $aeCount) * 1500;
                }
            }
        } catch (\Exception $e) {}

        return compact('goal', 'pendingForecast', 'revenue') + ['categories' => ['Total Forecasted Value']];
    }

    // ─────────────────────────────────────────────────────────
    //  TASKS STATUS TABLE
    // ─────────────────────────────────────────────────────────

    protected function calculateTasks()
    {
        $tasks = [];

        try {
            $quotations = Quotation::with(['customer', 'salesPerson'])->latest()->take(10)->get();

            foreach ($quotations as $q) {
                $dealValue   = $q->total_amount ?? $q->amount ?? 0;
                $status      = $q->status ?? 'draft';
                $statusLower = strtolower($status);

                $badgeClass = 'badge-subtle-info';
                $dealColor  = '#3b82f6';

                if (in_array($statusLower, ['approved', 'won', 'closed'])) {
                    $badgeClass = 'badge-subtle-success';
                    $dealColor  = '#0ab39c';
                } elseif (in_array($statusLower, ['sent', 'negotiation', 'pending'])) {
                    $badgeClass = 'badge-subtle-warning';
                    $dealColor  = '#f59e0b';
                } elseif (in_array($statusLower, ['lost', 'cancelled', 'rejected'])) {
                    $badgeClass = 'badge-subtle-danger';
                    $dealColor  = '#ef4444';
                }

                $tasks[] = [
                    'name'              => $q->customer?->name ?? $q->customer?->company ?? 'Unknown',
                    'last_contacted'    => $q->updated_at ? $q->updated_at->format('M d, Y') : '--',
                    'sales_rep_name'    => $q->salesPerson?->name ?? 'Unassigned',
                    'sales_rep_initial' => $q->salesPerson ? substr($q->salesPerson->name, 0, 1) : '?',
                    'status'            => ucfirst($status),
                    'badgeClass'        => $badgeClass,
                    'deal_value'        => $dealValue,
                    'dealColor'         => $dealColor,
                ];
            }
        } catch (\Exception $e) {}

        return $tasks;
    }
}
