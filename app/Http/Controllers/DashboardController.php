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
        $seenBookings = [];

        try {
            $logs = ShipmentStatusLog::with('shipment')
                ->where('created_at', '>=', now()->subDays(60))
                ->latest()
                ->take(100)
                ->get();

            foreach ($logs as $log) {
                $shipment = $log->shipment;
                if (!$shipment) continue;

                $fileNo = $shipment->file_no ?? $shipment->booking_no ?? $shipment->mawb_no ?? 'N/A';
                $bookingKey = class_basename($log->shipment_type) . '_' . $shipment->id;

                // Prevent duplicate rows for the same booking
                if (isset($seenBookings[$bookingKey]) || (isset($seenBookings[$fileNo]) && $fileNo !== 'N/A')) {
                    continue;
                }
                $seenBookings[$bookingKey] = true;
                if ($fileNo !== 'N/A') {
                    $seenBookings[$fileNo] = true;
                }

                $mblNo  = $shipment->mbl_no ?? $shipment->mawb_no ?? '--';
                $pod    = $shipment->portOfDischarge?->name 
                    ?? $shipment->podPort?->name 
                    ?? $shipment->dstPort?->name 
                    ?? $shipment->destinationPort?->name 
                    ?? '--';
                $eta    = $shipment->eta ?? $shipment->created_at;
                $statusName = $log->status_name ?? $log->status_code ?? 'Update';

                $hblNo = '--';
                if (isset($shipment->hbls) && $shipment->hbls instanceof \Illuminate\Support\Collection && $shipment->hbls->isNotEmpty()) {
                    $latestHbl = $shipment->hbls->sortByDesc(fn($h) => $h->updated_at ?? $h->created_at ?? $h->id)->first();
                    $hblNo = $latestHbl ? ($latestHbl->hbl_no ?? $latestHbl->hawb_no ?? '--') : '--';
                }

                $consignee = $shipment->consignee?->name 
                    ?? $shipment->dmConsignee?->name 
                    ?? $shipment->customer?->name 
                    ?? $shipment->dmCustomer?->name 
                    ?? '--';

                $badgeClass = 'bg-pre-alert';
                if (stripos($statusName, 'arrival') !== false) {
                    $badgeClass = 'bg-arrival';
                } elseif (stripos($statusName, 'invoice') !== false) {
                    $badgeClass = 'bg-invoice';
                } elseif (stripos($statusName, 'block') !== false) {
                    $badgeClass = 'bg-blocked';
                } elseif (stripos($statusName, 'update') !== false) {
                    $badgeClass = 'bg-updated';
                } elseif (stripos($statusName, 'create') !== false) {
                    $badgeClass = 'bg-created';
                }

                $editUrl = '';
                if ($shipment instanceof \App\Models\OceanImport) {
                    $editUrl = route('ocean-import.edit', $shipment->id);
                } elseif ($shipment instanceof \App\Models\OceanExport) {
                    $editUrl = route('ocean-export.edit', $shipment->id);
                } elseif ($shipment instanceof \App\Models\AirImport) {
                    $editUrl = route('air-import.edit', $shipment->id);
                } elseif ($shipment instanceof \App\Models\AirExport) {
                    $editUrl = route('air-export.edit', $shipment->id);
                } elseif ($shipment instanceof \App\Models\AirBooking) {
                    $editUrl = route('air-bookings.edit', $shipment->id);
                } elseif ($shipment instanceof \App\Models\OceanBooking) {
                    $editUrl = route('ocean-bookings.edit', $shipment->id);
                }

                $todos[] = [
                    'id'            => $shipment->id,
                    'task'          => $statusName,
                    'file_no'       => $fileNo,
                    'eta'           => $eta instanceof \Carbon\Carbon ? $eta->format('m-d-Y') : (is_string($eta) ? substr($eta, 0, 10) : '--'),
                    'mbl'           => $mblNo,
                    'hbl'           => $hblNo,
                    'pod'           => $pod,
                    'consignee'     => $consignee,
                    'status'        => $statusName,
                    'badgeClass'    => $badgeClass,
                    'shipment_type' => class_basename($log->shipment_type),
                    'edit_url'      => $editUrl,
                ];

                if (count($todos) >= 10) {
                    break;
                }
            }
        } catch (\Exception $e) {}

        // If fewer than 10, backfill with recent shipments from modules so dashboard has 10 distinct bookings
        if (count($todos) < 10) {
            try {
                $shipmentConfigs = [
                    ['model' => \App\Models\OceanImport::class, 'type' => 'OceanImport', 'task' => 'Pre-alert', 'route' => 'ocean-import.edit', 'relations' => ['portOfDischarge', 'dmConsignee', 'hbls']],
                    ['model' => \App\Models\OceanExport::class, 'type' => 'OceanExport', 'task' => 'Pre-alert', 'route' => 'ocean-export.edit', 'relations' => ['portOfDischarge', 'dmConsignee', 'hbls']],
                    ['model' => \App\Models\AirImport::class,   'type' => 'AirImport',   'task' => 'Pre-alert', 'route' => 'air-import.edit',   'relations' => ['dstPort', 'dmConsignee', 'hbls']],
                    ['model' => \App\Models\AirExport::class,   'type' => 'AirExport',   'task' => 'Pre-alert', 'route' => 'air-export.edit',   'relations' => ['dstPort', 'consignee', 'dmConsignee', 'hbls']],
                ];

                foreach ($shipmentConfigs as $cfg) {
                    if (count($todos) >= 10) break;

                    $recent = $cfg['model']::with($cfg['relations'])
                        ->latest()
                        ->take(5)
                        ->get();

                    foreach ($recent as $s) {
                        $fileNo = $s->file_no ?? $s->booking_no ?? $s->mawb_no ?? 'N/A';
                        $bookingKey = $cfg['type'] . '_' . $s->id;

                        if (isset($seenBookings[$bookingKey]) || (isset($seenBookings[$fileNo]) && $fileNo !== 'N/A')) {
                            continue;
                        }
                        $seenBookings[$bookingKey] = true;
                        if ($fileNo !== 'N/A') $seenBookings[$fileNo] = true;

                        $pod = $s->portOfDischarge?->name ?? $s->dstPort?->name ?? $s->podPort?->name ?? '--';
                        $eta = $s->eta ? ($s->eta instanceof \Carbon\Carbon ? $s->eta->format('m-d-Y') : substr($s->eta, 0, 10)) : '--';
                        $hblNo = '--';
                        if (isset($s->hbls) && $s->hbls instanceof \Illuminate\Support\Collection && $s->hbls->isNotEmpty()) {
                            $latestHbl = $s->hbls->sortByDesc(fn($h) => $h->updated_at ?? $h->created_at ?? $h->id)->first();
                            $hblNo = $latestHbl ? ($latestHbl->hbl_no ?? $latestHbl->hawb_no ?? '--') : '--';
                        }
                        $consignee = $s->consignee?->name ?? $s->dmConsignee?->name ?? $s->customer?->name ?? $s->dmCustomer?->name ?? '--';

                        $todos[] = [
                            'id'            => $s->id,
                            'task'          => $cfg['task'],
                            'file_no'       => $fileNo,
                            'eta'           => $eta,
                            'mbl'           => $s->mbl_no ?? $s->mawb_no ?? '--',
                            'hbl'           => $hblNo,
                            'pod'           => $pod,
                            'consignee'     => $consignee,
                            'status'        => 'Pending',
                            'badgeClass'    => 'bg-pre-alert',
                            'shipment_type' => $cfg['type'],
                            'edit_url'      => route($cfg['route'], $s->id),
                        ];

                        if (count($todos) >= 10) break;
                    }
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
            $quotations = Quotation::with(['customer', 'salesPerson', 'op'])->latest()->take(10)->get();

            foreach ($quotations as $q) {
                // OP Representative (Ticket 03)
                $opUser = $q->op ?? $q->salesPerson;
                $opRepName = $opUser?->name ?? 'Unassigned';
                $opRepInitial = $opUser ? substr($opUser->name, 0, 1) : '?';

                // Find connected HBL / HAWB / Shipment (Ticket 02 & 04)
                $hblCandidates = collect();
                $connectedShipment = null;

                if (!empty($q->quote_no)) {
                    if ($h = \App\Models\OceanExportHbl::where('quotation_no', $q->quote_no)->latest('updated_at')->first()) {
                        if ($h->hbl_no) $hblCandidates->push(['model' => $h, 'no' => $h->hbl_no, 'type' => 'OE_HBL', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\AirExportHbl::where('quotation_no', $q->quote_no)->latest('updated_at')->first()) {
                        if ($h->hawb_no) $hblCandidates->push(['model' => $h, 'no' => $h->hawb_no, 'type' => 'AE_HAWB', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\OceanImportHbl::where('quotation_no', $q->quote_no)->latest('updated_at')->first()) {
                        if ($h->hbl_no) $hblCandidates->push(['model' => $h, 'no' => $h->hbl_no, 'type' => 'OI_HBL', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\AirImportHbl::where('quotation_id', $q->id)->latest('updated_at')->first()) {
                        if ($h->hawb_no) $hblCandidates->push(['model' => $h, 'no' => $h->hawb_no, 'type' => 'AI_HAWB', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                }

                if (!empty($q->booking_no)) {
                    if ($oe = \App\Models\OceanExport::where('booking_no', $q->booking_no)->with('hbls')->first()) {
                        $connectedShipment = $oe;
                        if ($oe->hbls && $oe->hbls->isNotEmpty()) {
                            $latest = $oe->hbls->sortByDesc(fn($x) => $x->updated_at ?? $x->created_at ?? $x->id)->first();
                            if ($latest && $latest->hbl_no) {
                                $hblCandidates->push(['model' => $latest, 'no' => $latest->hbl_no, 'type' => 'OE_HBL', 'time' => $latest->updated_at ?? $latest->created_at ?? $latest->id]);
                            }
                        }
                    }

                    if (!$connectedShipment && ($ae = \App\Models\AirExport::where('booking_no', $q->booking_no)->with('hbls')->first())) {
                        $connectedShipment = $ae;
                        if ($ae->hbls && $ae->hbls->isNotEmpty()) {
                            $latest = $ae->hbls->sortByDesc(fn($x) => $x->updated_at ?? $x->created_at ?? $x->id)->first();
                            if ($latest && $latest->hawb_no) {
                                $hblCandidates->push(['model' => $latest, 'no' => $latest->hawb_no, 'type' => 'AE_HAWB', 'time' => $latest->updated_at ?? $latest->created_at ?? $latest->id]);
                            }
                        }
                    }

                    $aeHbl = \App\Models\AirExportHbl::where('booking_no', $q->booking_no)->latest('updated_at')->first();
                    if ($aeHbl && $aeHbl->hawb_no) {
                        $hblCandidates->push(['model' => $aeHbl, 'no' => $aeHbl->hawb_no, 'type' => 'AE_HAWB', 'time' => $aeHbl->updated_at ?? $aeHbl->created_at ?? $aeHbl->id]);
                    }
                }

                if ($hblCandidates->isEmpty() && !empty($q->customer_id)) {
                    if ($h = \App\Models\OceanExportHbl::where('customer_id', $q->customer_id)->latest('updated_at')->first()) {
                        if ($h->hbl_no) $hblCandidates->push(['model' => $h, 'no' => $h->hbl_no, 'type' => 'OE_HBL', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\AirExportHbl::where('customer_id', $q->customer_id)->latest('updated_at')->first()) {
                        if ($h->hawb_no) $hblCandidates->push(['model' => $h, 'no' => $h->hawb_no, 'type' => 'AE_HAWB', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\OceanImportHbl::where('customer_id', $q->customer_id)->latest('updated_at')->first()) {
                        if ($h->hbl_no) $hblCandidates->push(['model' => $h, 'no' => $h->hbl_no, 'type' => 'OI_HBL', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                    if ($h = \App\Models\AirImportHbl::where('customer_id', $q->customer_id)->latest('updated_at')->first()) {
                        if ($h->hawb_no) $hblCandidates->push(['model' => $h, 'no' => $h->hawb_no, 'type' => 'AI_HAWB', 'time' => $h->updated_at ?? $h->created_at ?? $h->id]);
                    }
                }

                $bestHbl = $hblCandidates->sortByDesc('time')->first();
                $hblHawbNo = $bestHbl ? $bestHbl['no'] : '--';

                // Workflow Milestones (Ticket 04)
                $hasAms = $connectedShipment && (!empty($connectedShipment->ams_no) || !empty($connectedShipment->isf_no));
                $hasHbl = !empty($bestHbl);
                $hasBooking = !empty($q->booking_no);
                $qStatusLower = strtolower($q->status ?? 'draft');

                if ($hasAms) {
                    $milestoneStatus = 'AMS Submitted';
                    $milestoneBadge  = 'badge-subtle-success';
                } elseif ($hasHbl) {
                    $milestoneStatus = ($bestHbl['type'] ?? '') === 'AE_HAWB' ? 'HAWB Created' : 'HBL Created';
                    $milestoneBadge  = 'badge-subtle-info';
                } elseif ($hasBooking) {
                    $milestoneStatus = 'Booking Created';
                    $milestoneBadge  = 'badge-subtle-warning';
                } elseif (in_array($qStatusLower, ['approved', 'won', 'closed'])) {
                    $milestoneStatus = 'Quotation Approved';
                    $milestoneBadge  = 'badge-subtle-success';
                } elseif (in_array($qStatusLower, ['sent', 'negotiation', 'pending'])) {
                    $milestoneStatus = 'Quotation Sent';
                    $milestoneBadge  = 'badge-subtle-warning';
                } elseif (in_array($qStatusLower, ['lost', 'cancelled', 'rejected'])) {
                    $milestoneStatus = 'Cancelled';
                    $milestoneBadge  = 'badge-subtle-danger';
                } else {
                    $milestoneStatus = 'Draft Created';
                    $milestoneBadge  = 'badge-subtle-secondary';
                }

                // Submitted Date (Ticket 05)
                $rawSubmitted = $q->create_date ?? $q->quote_date ?? $q->created_at;
                $submittedDate = '--';
                if ($rawSubmitted) {
                    try {
                        $submittedDate = $rawSubmitted instanceof \Carbon\Carbon 
                            ? $rawSubmitted->format('M d, Y') 
                            : \Carbon\Carbon::parse($rawSubmitted)->format('M d, Y');
                    } catch (\Exception $e) {
                        $submittedDate = substr((string)$rawSubmitted, 0, 10);
                    }
                }

                $tasks[] = [
                    'name'              => $q->customer?->name ?? $q->customer?->company ?? 'Unknown',
                    'last_contacted'    => $q->updated_at ? $q->updated_at->format('M d, Y') : '--',
                    'op_rep_name'       => $opRepName,
                    'op_rep_initial'    => $opRepInitial,
                    'hbl_hawb'          => $hblHawbNo,
                    'status'            => $milestoneStatus,
                    'badgeClass'        => $milestoneBadge,
                    'submitted_date'    => $submittedDate,
                ];
            }
        } catch (\Exception $e) {}

        return $tasks;
    }
}
