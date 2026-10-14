<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Charge;
use App\Models\Office;
use App\Models\User;
use App\Models\Port;
use App\Models\Vessel;
use App\Models\ActivityLog;
use App\Models\TradePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function volumeProfitChart(Request $request)
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $salesPersons = User::orderBy('name')->get(['id', 'name']);
        $shippingTypes = ['Ocean Export', 'Ocean Import', 'Air Export', 'Air Import', 'Trucking', 'Misc', 'Warehouse'];
        
        $volumeUnits = [
            ['value' => 'cbm', 'label' => 'CBM'],
            ['value' => 'cft', 'label' => 'CFT'],
            ['value' => 'teu', 'label' => 'TEU'],
            ['value' => 'bl', 'label' => 'B/L Count'],
        ];

        $chartTypes = [
            ['value' => 'month', 'label' => 'Month'],
            ['value' => 'office', 'label' => 'Office'],
            ['value' => 'sales', 'label' => 'Sales Person'],
            ['value' => 'shipping_type', 'label' => 'Shipping Type'],
        ];

        $barSegments = [
            ['value' => 'shipping_type', 'label' => 'Shipping Type'],
            ['value' => 'office', 'label' => 'Office'],
            ['value' => 'status', 'label' => 'Status'],
        ];

        $statuses = ['All', 'Open', 'Blocked'];

        return view('report.volume-profit-chart', compact('offices', 'salesPersons', 'shippingTypes', 'volumeUnits', 'chartTypes', 'barSegments', 'statuses'));
    }

   public function volumeProfitChartData(Request $request)
{
    $dateFrom = $request->input(
        'date_from',
        Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d')
    );

    $dateTo = $request->input(
        'date_to',
        Carbon::now()->endOfMonth()->format('Y-m-d')
    );

    $shippingTypes = $request->input('shipping_types', []);
    $officeId = $request->input('office_id');
    $salesPersonId = $request->input('sales_person_id');
    $volumeUnit = $request->input('volume_unit', 'cbm');
    $periodType = $request->input('period_type', 'post_date');
    $chartType = $request->input('chart_type', 'month');
    $barSegment = $request->input('bar_segment', 'shipping_type');
    $statusFilter = $request->input('status_filter', 'all');

    /*
    |--------------------------------------------------------------------------
    | Base Quotation Query
    |--------------------------------------------------------------------------
    */

    $query = Quotation::query()
        ->with([
            'items.currency',
            'customer',
            'office',
            'salesPerson',
            'pol',
            'pod'
        ])
        ->whereNull('deleted_at');

    /*
    |--------------------------------------------------------------------------
    | Date Column
    |--------------------------------------------------------------------------
    */

    $dateCol = match ($periodType) {
        'etd' => 'departure',
        'eta' => 'destination',
        'post_date' => 'quote_date',
        default => 'created_at',
    };

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */

    if ($dateFrom && $dateTo) {
        $query->whereRaw(
            "COALESCE({$dateCol}, created_at) BETWEEN ? AND ?",
            [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Other Filters
    |--------------------------------------------------------------------------
    */

    if (!empty($shippingTypes)) {
        $query->whereIn('shipping_type', $shippingTypes);
    }

    if ($officeId) {
        $query->where('office_id', $officeId);
    }

    if ($salesPersonId) {
        $query->where('sales_person_id', $salesPersonId);
    }

    if ($statusFilter === 'open') {
        $query->whereIn('status', [
            'Draft',
            'Sent',
            'Pending'
        ]);
    } elseif ($statusFilter === 'blocked') {
        $query->whereIn('status', [
            'Lost',
            'Expired',
            'Cancelled',
            'Ghosted'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Quotations
    |--------------------------------------------------------------------------
    */

    $quotations = $query->get();

    /*
    |--------------------------------------------------------------------------
    | TOTALS
    |--------------------------------------------------------------------------
    */

    $totalAr = 0;
    $totalAp = 0;
    $totalVolume = 0;
    $totalWeight = 0;

    $shipmentCount = $quotations->count();

    foreach ($quotations as $q) {

        foreach ($q->items as $item) {
            $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
            $amount = (float) ($item->total_amount ?? 0) / $rate;

            if ($item->type === 'AR') {
                $totalAr += $amount;
            }

            if ($item->type === 'AP') {
                $totalAp += $amount;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Volume
        |--------------------------------------------------------------------------
        */

        if ($volumeUnit === 'cft') {
            $totalVolume += (float) ($q->volume_cft ?? 0);
        } elseif ($volumeUnit === 'bl') {
            $totalVolume += 1;
        } elseif ($volumeUnit === 'teu') {
            $totalVolume += (float) ($q->teu ?? 0);
        } else {
            $totalVolume += (float) ($q->volume_cbm ?? 0);
        }

        $totalWeight += (float) ($q->weight_kg ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | PROFIT
    |--------------------------------------------------------------------------
    */

    $grossProfit = $totalAr - $totalAp;

    $profitPerUnit = $totalVolume > 0
        ? $grossProfit / $totalVolume
        : 0;


    /*
    |--------------------------------------------------------------------------
    | PROFIT AVERAGES
    |--------------------------------------------------------------------------
    */

    $periodStart = Carbon::parse($dateFrom)->startOfDay();
    $periodEnd = Carbon::parse($dateTo)->endOfDay();

    $totalDays = $periodStart->diffInDays($periodEnd) + 1;

    $totalWeeks = $totalDays / 7;

    $totalMonths = $periodStart
        ->copy()
        ->startOfMonth()
        ->diffInMonths(
            $periodEnd->copy()->startOfMonth()
        ) + 1;

    $totalYears = $totalDays / 365.25;

    $dailyAverage = $totalDays > 0
        ? $grossProfit / $totalDays
        : 0;

    $weeklyAverage = $totalWeeks > 0
        ? $grossProfit / $totalWeeks
        : 0;

    $monthlyAverage = $totalMonths > 0
        ? $grossProfit / $totalMonths
        : 0;

    $yearlyAverage = $totalYears > 0
        ? $grossProfit / $totalYears
        : 0;


    /*
    |--------------------------------------------------------------------------
    | MONTHLY DATA
    |--------------------------------------------------------------------------
    */

    $monthlyData = collect();

    $start = Carbon::parse($dateFrom)->startOfMonth();
    $end = Carbon::parse($dateTo)->startOfMonth();

    $current = $start->copy();

    while ($current->lte($end)) {

        $monthLabel = $current->format('M Y');

        $monthQ = $quotations->filter(function ($q) use (
            $current,
            $dateCol
        ) {

            $rawVal = $q->$dateCol ?? $q->created_at;

            if (!$rawVal) {
                return false;
            }

            return Carbon::parse($rawVal)->format('Y-m')
                === $current->format('Y-m');
        });


        $monthAr = 0;
        $monthAp = 0;
        $monthVol = 0;


        foreach ($monthQ as $q) {

            foreach ($q->items as $item) {
                $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                $amount = (float) ($item->total_amount ?? 0) / $rate;

                if ($item->type === 'AR') {
                    $monthAr += $amount;
                }

                if ($item->type === 'AP') {
                    $monthAp += $amount;
                }
            }


            if ($volumeUnit === 'cft') {

                $monthVol += (float) (
                    $q->volume_cft ?? 0
                );

            } elseif ($volumeUnit === 'bl') {

                $monthVol += 1;

            } elseif ($volumeUnit === 'teu') {

                $monthVol += (float) (
                    $q->teu ?? 0
                );

            } else {

                $monthVol += (float) (
                    $q->volume_cbm ?? 0
                );
            }
        }


        $monthProfit = $monthAr - $monthAp;


        $monthlyData->push([

            'month' => $monthLabel,

            'revenue' => round(
                $monthAr,
                2
            ),

            'cost' => round(
                $monthAp,
                2
            ),

            'profit' => round(
                $monthProfit,
                2
            ),

            'volume' => round(
                $monthVol,
                2
            ),

            'count' => $monthQ->count(),

        ]);


        $current->addMonth();
    }


    /*
    |--------------------------------------------------------------------------
    | BY SHIPPING TYPE
    |--------------------------------------------------------------------------
    */

    $byShippingType = $quotations
        ->groupBy(function ($q) {

            return $q->shipping_type
                ?: 'Unspecified';

        })
        ->map(function ($qs) {

            $ar = 0;
            $ap = 0;


            foreach ($qs as $q) {

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) ($item->total_amount ?? 0) / $rate;

                    if ($item->type === 'AR') {
                        $ar += $amount;
                    }

                    if ($item->type === 'AP') {
                        $ap += $amount;
                    }
                }
            }


            return [

                'label' => $qs->first()->shipping_type
                    ?: 'Unspecified',

                'revenue' => round(
                    $ar,
                    2
                ),

                'cost' => round(
                    $ap,
                    2
                ),

                'profit' => round(
                    $ar - $ap,
                    2
                ),

                'count' => $qs->count(),

            ];
        })
        ->values();


    /*
    |--------------------------------------------------------------------------
    | BY OFFICE
    |--------------------------------------------------------------------------
    */

    $byOffice = $quotations
        ->groupBy(function ($q) {

            return $q->office
                ? $q->office->code
                : 'N/A';

        })
        ->map(function ($qs, $key) {

            $ar = 0;
            $ap = 0;


            foreach ($qs as $q) {

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) ($item->total_amount ?? 0) / $rate;

                    if ($item->type === 'AR') {
                        $ar += $amount;
                    }

                    if ($item->type === 'AP') {
                        $ap += $amount;
                    }
                }
            }


            return [

                'label' => $key,

                'profit' => round(
                    $ar - $ap,
                    2
                ),

                'count' => $qs->count(),

            ];
        })
        ->values();


    /*
    |--------------------------------------------------------------------------
    | BY STATUS
    |--------------------------------------------------------------------------
    */

    $byStatus = $quotations
        ->groupBy('status')
        ->map(function ($qs, $status) {

            return [

                'label' => $status ?: 'Unknown',

                'count' => $qs->count(),

            ];
        })
        ->values();


    /*
    |--------------------------------------------------------------------------
    | TOP LANES
    |--------------------------------------------------------------------------
    */

    $topLanes = $quotations
        ->groupBy(function ($q) {

            $pol = $q->pol
                ? $q->pol->name
                : 'N/A';

            $pod = $q->pod
                ? $q->pod->name
                : 'N/A';

            return $pol . ' → ' . $pod;

        })
        ->map(function ($qs) {

            $ar = 0;
            $ap = 0;
            $vol = 0;


            foreach ($qs as $q) {

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) ($item->total_amount ?? 0) / $rate;

                    if ($item->type === 'AR') {
                        $ar += $amount;
                    }

                    if ($item->type === 'AP') {
                        $ap += $amount;
                    }
                }

                $vol += (float) (
                    $q->volume_cbm ?? 0
                );
            }


            $pol = $qs->first()->pol
                ? $qs->first()->pol->name
                : 'N/A';

            $pod = $qs->first()->pod
                ? $qs->first()->pod->name
                : 'N/A';


            return [

                'label' => $pol . ' → ' . $pod,

                'profit' => round(
                    $ar - $ap,
                    2
                ),

                'volume' => round(
                    $vol,
                    2
                ),

                'count' => $qs->count(),

            ];
        })
        ->values()
        ->sortByDesc('profit')
        ->take(10)
        ->values();


    /*
    |--------------------------------------------------------------------------
    | JSON RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'summary' => [

            'gross_profit' => round(
                $grossProfit,
                2
            ),

            'daily_average' => round(
                $dailyAverage,
                2
            ),

            'weekly_average' => round(
                $weeklyAverage,
                2
            ),

            'monthly_average' => round(
                $monthlyAverage,
                2
            ),

            'yearly_average' => round(
                $yearlyAverage,
                2
            ),

            'total_volume' => round(
                $totalVolume,
                2
            ),

            'profit_per_unit' => round(
                $profitPerUnit,
                2
            ),

            'shipment_count' => $shipmentCount,

            'total_revenue' => round(
                $totalAr,
                2
            ),

            'total_cost' => round(
                $totalAp,
                2
            ),

            'total_weight_kg' => round(
                $totalWeight,
                2
            ),
        ],

        'monthly' => $monthlyData,

        'by_shipping_type' => $byShippingType,

        'by_office' => $byOffice,

        'by_status' => $byStatus,

        'top_lanes' => $topLanes,

        'volume_unit' => $volumeUnit === 'cft'
            ? 'CFT'
            : 'CBM',

    ]);
}

   
    public function volumeProfit(Request $request)
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $salesPersons = User::orderBy('name')->get(['id', 'name']);
        $shippingTypes = ['Ocean Export', 'Ocean Import', 'Air Export', 'Air Import', 'Trucking', 'Misc', 'Warehouse'];

        return view('report.volume-profit', compact('offices', 'salesPersons', 'shippingTypes'));
    }

    public function volumeProfitData(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $shippingTypes = $request->input('shipping_types', []);
        $officeId = $request->input('office_id');
        $salesPersonId = $request->input('sales_person_id');
        $periodType = $request->input('period_type', 'post_date');
        $profitFilter = $request->input('profit_filter', 'all');
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'revenue');
        $sortDir = $request->input('sort_dir', 'desc');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        $query = Quotation::query()
            ->with(['items', 'customer', 'office', 'salesPerson', 'pol', 'pod'])
            ->whereNull('deleted_at');

        $dateCol = match($periodType) {
            'etd' => 'departure',
            'eta' => 'destination',
            'post_date' => 'quote_date',
            default => 'created_at',
        };

        if ($dateFrom && $dateTo) {
            $query->whereRaw("COALESCE({$dateCol}, created_at) BETWEEN ? AND ?", [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        if (!empty($shippingTypes)) {
            $query->whereIn('shipping_type', $shippingTypes);
        }
        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        if ($salesPersonId) {
            $query->where('sales_person_id', $salesPersonId);
        }

        $quotations = $query->get();

        $grouped = $quotations->groupBy(function ($q) {
            $type = $q->shipping_type ?: 'Unspecified';
            $partner = $q->customer ? $q->customer->name : 'N/A';
            return $type . '||' . $partner;
        })->map(function ($qs, $key) {
            $parts = explode('||', $key);
            $ar = 0;
            $ap = 0;
            $vol = 0;
            $weight = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
                $vol += (float) ($q->volume_cbm ?? 0);
                $weight += (float) ($q->weight_kg ?? 0);
            }
            $profit = $ar - $ap;
            return [
                'shipping_type' => $parts[0],
                'partner' => $parts[1],
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($profit, 2),
                'volume_cbm' => round($vol, 2),
                'weight_kg' => round($weight, 2),
                'count' => $qs->count(),
            ];
        })->values();

        if ($profitFilter === 'profit') {
            $grouped = $grouped->filter(fn($r) => $r['profit'] > 0);
        } elseif ($profitFilter === 'loss') {
            $grouped = $grouped->filter(fn($r) => $r['profit'] < 0);
        } elseif ($profitFilter === 'breakeven') {
            $grouped = $grouped->filter(fn($r) => $r['profit'] == 0);
        }

        if ($search) {
            $s = strtolower($search);
            $grouped = $grouped->filter(fn($r) =>
                str_contains(strtolower($r['shipping_type']), $s) ||
                str_contains(strtolower($r['partner']), $s)
            );
        }

        $totalAr = $quotations->sum(function ($q) {
            return $q->items->where('type', 'AR')->sum('total_amount');
        });
        $totalAp = $quotations->sum(function ($q) {
            return $q->items->where('type', 'AP')->sum('total_amount');
        });
        $totalVol = $quotations->sum(fn($q) => (float) ($q->volume_cbm ?? 0));
        $totalWeight = $quotations->sum(fn($q) => (float) ($q->weight_kg ?? 0));

        $sorted = match($sortBy) {
            'shipping_type' => $sorted = $grouped->sortBy('shipping_type', SORT_REGULAR, $sortDir === 'asc'),
            'partner' => $sorted = $grouped->sortBy('partner', SORT_REGULAR, $sortDir === 'asc'),
            'cost' => $sorted = $grouped->sortBy('cost', SORT_REGULAR, $sortDir === 'asc'),
            'profit' => $sorted = $grouped->sortBy('profit', SORT_REGULAR, $sortDir === 'asc'),
            'volume' => $sorted = $grouped->sortBy('volume_cbm', SORT_REGULAR, $sortDir === 'asc'),
            'count' => $sorted = $grouped->sortBy('count', SORT_REGULAR, $sortDir === 'asc'),
            default => $sorted = $grouped->sortBy('revenue', SORT_REGULAR, $sortDir === 'asc'),
        };

        $total = $sorted->count();
        $paginated = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'summary' => [
                'total_revenue' => round($totalAr, 2),
                'total_cost' => round($totalAp, 2),
                'gross_profit' => round($totalAr - $totalAp, 2),
                'margin' => $totalAr > 0 ? round((($totalAr - $totalAp) / $totalAr) * 100, 1) : 0,
                'total_volume' => round($totalVol, 2),
                'total_weight_kg' => round($totalWeight, 2),
                'shipment_count' => $quotations->count(),
                'row_count' => $total,
            ],
            'rows' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function advancedReport(Request $request)
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $currencies = \App\Models\Currency::orderBy('code')->get(['id', 'code', 'name']);
        $shippingTypes = ['Ocean Export', 'Ocean Import', 'Air Export', 'Air Import', 'Trucking', 'Warehouse'];

        return view('report.advanced', compact('offices', 'currencies', 'shippingTypes'));
    }

    public function advancedReportData(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $shippingTypes = $request->input('shipping_types', []);
        $officeId = $request->input('office_id');
        $currencyId = $request->input('currency_id');
        $includeInternal = $request->input('include_internal', false);

        $quotations = Quotation::query()
            ->with(['items', 'customer', 'office', 'salesPerson', 'pol', 'pod', 'carrier'])
            ->whereNull('deleted_at');

        if ($dateFrom && $dateTo) {
            $quotations->whereRaw("COALESCE(quote_date, created_at) BETWEEN ? AND ?", [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        $quotations = $quotations->get();

        if (!empty($shippingTypes)) {
            $quotations = $quotations->filter(fn($q) => in_array($q->shipping_type, $shippingTypes));
        }
        if ($officeId) {
            $quotations = $quotations->filter(fn($q) => $q->office_id == $officeId);
        }

        $totalAr = 0;
        $totalAp = 0;
        $totalVolume = 0;
        $totalCount = $quotations->count();

        foreach ($quotations as $q) {
            foreach ($q->items as $item) {
                if ($item->type === 'AR') $totalAr += (float) $item->total_amount;
                elseif ($item->type === 'AP') $totalAp += (float) $item->total_amount;
            }
            $totalVolume += (float) ($q->volume_cbm ?? 0);
        }

        $grossProfit = $totalAr - $totalAp;
        $margin = $totalAr > 0 ? round(($grossProfit / $totalAr) * 100, 1) : 0;

        $byShippingType = $quotations->groupBy(function ($q) {
            return $q->shipping_type ?: 'Unspecified';
        })->map(function ($qs) {
            $ar = 0;
            $ap = 0;
            $vol = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
                $vol += (float) ($q->volume_cbm ?? 0);
            }
            $profit = $ar - $ap;
            $m = $ar > 0 ? round(($profit / $ar) * 100, 1) : 0;
            return [
                'shipping_type' => $qs->first()->shipping_type ?: 'Unspecified',
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($profit, 2),
                'margin' => $m,
                'volume' => round($vol, 2),
                'count' => $qs->count(),
            ];
        })->values();

        $byOffice = $quotations->groupBy(function ($q) {
            return $q->office ? $q->office->code : 'N/A';
        })->map(function ($qs, $key) {
            $ar = 0;
            $ap = 0;
            $vol = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
                $vol += (float) ($q->volume_cbm ?? 0);
            }
            $profit = $ar - $ap;
            return [
                'office' => $key,
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($profit, 2),
                'volume' => round($vol, 2),
                'count' => $qs->count(),
            ];
        })->values();

        $byPartner = $quotations->groupBy(function ($q) {
            return $q->customer ? $q->customer->name : 'N/A';
        })->map(function ($qs, $key) {
            $ar = 0;
            $ap = 0;
            $vol = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
                $vol += (float) ($q->volume_cbm ?? 0);
            }
            $profit = $ar - $ap;
            $m = $ar > 0 ? round(($profit / $ar) * 100, 1) : 0;
            return [
                'partner' => $key,
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($profit, 2),
                'margin' => $m,
                'volume' => round($vol, 2),
                'count' => $qs->count(),
            ];
        })->values()->sortByDesc('revenue')->values();

        $bySalesPerson = $quotations->groupBy(function ($q) {
            return $q->salesPerson ? $q->salesPerson->name : 'N/A';
        })->map(function ($qs, $key) {
            $ar = 0;
            $ap = 0;
            foreach ($qs as $q) {
                foreach ($q->items as $item) {
                    if ($item->type === 'AR') $ar += (float) $item->total_amount;
                    elseif ($item->type === 'AP') $ap += (float) $item->total_amount;
                }
            }
            return [
                'sales' => $key,
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($ar - $ap, 2),
                'count' => $qs->count(),
            ];
        })->values()->sortByDesc('revenue')->values();

        $periodStart = \Carbon\Carbon::parse($request->date_from)->startOfDay();
$periodEnd = \Carbon\Carbon::parse($request->date_to)->endOfDay();

$totalDays = $periodStart->diffInDays($periodEnd) + 1;

$totalWeeks = $totalDays / 7;

$totalMonths = $periodStart->copy()
    ->startOfMonth()
    ->diffInMonths(
        $periodEnd->copy()->startOfMonth()
    ) + 1;

$totalYears = $totalDays / 365.25;

$dailyAverage = $totalDays > 0
    ? $grossProfit / $totalDays
    : 0;

$weeklyAverage = $totalWeeks > 0
    ? $grossProfit / $totalWeeks
    : 0;

$monthlyAverage = $totalMonths > 0
    ? $grossProfit / $totalMonths
    : 0;

$yearlyAverage = $totalYears > 0
    ? $grossProfit / $totalYears
    : 0;
        
    return response()->json([

    'summary' => [

        'total_revenue' => round($totalAr, 2),

        'total_cost' => round($totalAp, 2),

        'gross_profit' => round($grossProfit, 2),

        'margin' => $margin,

        'total_volume' => round($totalVolume, 2),

        'total_count' => $totalCount,

        'daily_average' => round($dailyAverage, 2),

        'weekly_average' => round($weeklyAverage, 2),

        'monthly_average' => round($monthlyAverage, 2),

        'yearly_average' => round($yearlyAverage, 2),

    ],

    'by_shipping_type' => $byShippingType,

    'by_office' => $byOffice,

    'by_partner' => $byPartner,

    'by_sales_person' => $bySalesPerson,

]);
    }

    public function employeePerformance(Request $request)
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $salesPersons = User::orderBy('name')->get(['id', 'name']);
        $shippingTypes = ['Ocean Export', 'Ocean Import', 'Air Export', 'Air Import', 'Trucking', 'Misc', 'Warehouse'];

        return view('report.employee-performance', compact('offices', 'salesPersons', 'shippingTypes'));
    }

    public function employeePerformanceData(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $shippingTypes = $request->input('shipping_types', []);
        $officeId = $request->input('office_id');
        $salesPersonId = $request->input('sales_person_id');
        $periodType = $request->input('period_type', 'post_date');
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'profit');
        $sortDir = $request->input('sort_dir', 'desc');

        $dateCol = match($periodType) {
            'etd' => 'departure',
            'eta' => 'destination',
            'create_date' => 'create_date',
            'post_date' => 'quote_date',
            default => 'created_at',
        };

        $query = Quotation::query()
            ->with(['items.currency', 'salesPerson', 'office', 'customer', 'pol', 'pod'])
            ->whereNull('deleted_at')
            ->whereNotNull('sales_person_id');

        if ($dateFrom && $dateTo) {
            $query->whereRaw("COALESCE({$dateCol}, created_at) BETWEEN ? AND ?", [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        if (!empty($shippingTypes)) {
            $query->whereIn('shipping_type', $shippingTypes);
        }
        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        if ($salesPersonId) {
            $query->where('sales_person_id', $salesPersonId);
        }

        $quotations = $query->get();

        $grouped = $quotations->groupBy('sales_person_id')->map(function ($qs, $userId) {
            $user = $qs->first()->salesPerson;
            $ar = 0;
            $ap = 0;
            $vol = 0;
            $statuses = [];
            $shippingBreakdown = [];

            foreach ($qs as $q) {
                $qAr = 0;
                $qAp = 0;
                
                // Only count revenue/cost if Quote is Verified/Accepted/Won
                $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);

                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) $item->total_amount / $rate;
                    
                    if ($isValid) {
                        if ($item->type === 'AR') {
                            $qAr += $amount;
                            $ar += $amount;
                        } elseif ($item->type === 'AP') {
                            $qAp += $amount;
                            $ap += $amount;
                        }
                    }
                }
                
                if ($isValid) {
                    $vol += (float) ($q->volume_cbm ?? 0);
                }

                $status = $q->status ?: 'Unknown';
                $statuses[$status] = ($statuses[$status] ?? 0) + 1;
                $st = $q->shipping_type ?: 'Unspecified';
                
                if (!isset($shippingBreakdown[$st])) {
                    $shippingBreakdown[$st] = ['count' => 0, 'profit' => 0];
                }
                $shippingBreakdown[$st]['count']++;
                $shippingBreakdown[$st]['profit'] += ($qAr - $qAp);
            }

            $profit = $ar - $ap;
            $margin = $ar != 0 ? round(($profit / abs($ar)) * 100, 1) : 0;

            $wonCount = ($statuses['Won'] ?? 0) + ($statuses['ACCEPTED'] ?? 0) + ($statuses['Accepted'] ?? 0);
            $totalQuotes = $qs->count();
            $winRate = $totalQuotes > 0 ? round(($wonCount / $totalQuotes) * 100, 1) : 0;

            return [
                'employee_id' => $userId,
                'employee_name' => $user?->name ?? 'Unknown',
                'email' => $user?->email ?? '',
                'total_quotes' => $totalQuotes,
                'revenue' => round($ar, 2),
                'cost' => round($ap, 2),
                'profit' => round($profit, 2),
                'margin' => $margin,
                'volume_cbm' => round($vol, 2),
                'avg_deal_size' => $totalQuotes > 0 ? round($ar / $totalQuotes, 2) : 0,
                'win_rate' => $winRate,
                'statuses' => $statuses,
                'shipping_breakdown' => $shippingBreakdown,
            ];
        })->values();

        if ($search) {
            $s = strtolower($search);
            $grouped = $grouped->filter(fn($r) => str_contains(strtolower($r['employee_name']), $s));
        }

        $sorted = match($sortBy) {
            'employee' => $grouped->sortBy('employee_name', SORT_REGULAR, $sortDir === 'asc'),
            'quotes' => $grouped->sortBy('total_quotes', SORT_REGULAR, $sortDir === 'asc'),
            'revenue' => $grouped->sortBy('revenue', SORT_REGULAR, $sortDir === 'asc'),
            'cost' => $grouped->sortBy('cost', SORT_REGULAR, $sortDir === 'asc'),
            'margin' => $grouped->sortBy('margin', SORT_REGULAR, $sortDir === 'asc'),
            'volume' => $grouped->sortBy('volume_cbm', SORT_REGULAR, $sortDir === 'asc'),
            'win_rate' => $grouped->sortBy('win_rate', SORT_REGULAR, $sortDir === 'asc'),
            'avg_deal' => $grouped->sortBy('avg_deal_size', SORT_REGULAR, $sortDir === 'asc'),
            default => $grouped->sortBy('profit', SORT_REGULAR, $sortDir === 'asc'),
        };
        $sorted = $sorted->values();

        $allAr = 0; $allAp = 0; $allVol = 0;
        foreach ($quotations as $q) {
            $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);
            if ($isValid) {
                foreach ($q->items as $item) {
                    $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                    $amount = (float) $item->total_amount / $rate;
                    if ($item->type === 'AR') $allAr += $amount;
                    elseif ($item->type === 'AP') $allAp += $amount;
                }
                $allVol += (float) ($q->volume_cbm ?? 0);
            }
        }
        $topPerformer = $sorted->sortByDesc('profit')->first();

        $byShippingType = $quotations->groupBy(fn($q) => $q->shipping_type ?: 'Unspecified')->map(function ($qs, $key) {
            $ar = 0; $ap = 0;
            foreach ($qs as $q) {
                $isValid = in_array(strtoupper($q->status), ['ACCEPTED', 'WON']);
                if ($isValid) {
                    foreach ($q->items as $item) {
                        $rate = ($item->currency && $item->currency->exchange_rate > 0) ? (float) $item->currency->exchange_rate : 1;
                        $amount = (float) $item->total_amount / $rate;
                        if ($item->type === 'AR') $ar += $amount;
                        elseif ($item->type === 'AP') $ap += $amount;
                    }
                }
            }
            return ['label' => $key, 'revenue' => round($ar, 2), 'cost' => round($ap, 2), 'profit' => round($ar - $ap, 2), 'count' => $qs->count()];
        })->values();

        return response()->json([
            'summary' => [
                'total_revenue' => round($allAr, 2),
                'total_cost' => round($allAp, 2),
                'gross_profit' => round($allAr - $allAp, 2),
                'margin' => $allAr > 0 ? round((($allAr - $allAp) / $allAr) * 100, 1) : 0,
                'total_volume' => round($allVol, 2),
                'shipment_count' => $quotations->count(),
                'employee_count' => $sorted->count(),
                'top_performer' => $topPerformer ? $topPerformer['employee_name'] : 'N/A',
            ],
            'rows' => $sorted,
            'by_shipping_type' => $byShippingType,
        ]);
    }

    public function userLog(Request $request)
    {
        $users = User::orderBy('name')->get();
        return view('report.user-log', compact('users'));
    }

    public function userLogData(Request $request)
    {
        $dateFrom = $request->input('date_from', '2026-01-01');
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');
        $sortBy = $request->input('sort_by', 'login');
        $sortDir = $request->input('sort_dir', 'desc');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        $rangeStart = Carbon::parse($dateFrom)->startOfDay();
        $rangeEnd = Carbon::parse($dateTo)->endOfDay();

        $userQuery = User::orderBy('name');
        if ($userId) {
            $userQuery->where('id', $userId);
        }
        $targetUsers = $userQuery->get();

        $logQuery = ActivityLog::whereIn('action', ['login', 'logout']);
        if ($userId) {
            $logQuery->where('user_id', $userId);
        }
        $allLogs = $logQuery->orderBy('user_id')->orderBy('created_at')->get()->groupBy('user_id');

        $sessions = [];

        foreach ($targetUsers as $user) {
            $userLogs = $allLogs->get($user->id, collect());
            $parts = explode(' ', $user->name, 2);
            $firstName = !empty($user->first_name) ? $user->first_name : ($parts[0] ?? $user->name);
            $lastName = !empty($user->last_name) ? $user->last_name : ($parts[1] ?? '');
            $office = !empty($user->office_name) ? $user->office_name : (!empty($user->office_code) ? $user->office_code : 'Head Office');

            $events = $userLogs->map(fn($log) => [
                'type' => $log->action,
                'time' => $log->created_at,
            ])->sortBy('time')->values();

            $logins = $events->filter(fn($e) => $e['type'] === 'login')->values();
            $consumedLogouts = [];

            foreach ($logins as $idx => $loginEvent) {
                $loginTime = $loginEvent['time'];
                $nextLogin = $logins->get($idx + 1);
                $nextLoginTime = is_array($nextLogin) ? ($nextLogin['time'] ?? null) : ($nextLogin->time ?? null);

                $pairedLogout = null;
                foreach ($events as $ev) {
                    if ($ev['type'] === 'logout' && $ev['time']->gt($loginTime) && !in_array($ev['time']->timestamp, $consumedLogouts)) {
                        if (!$nextLoginTime || $ev['time']->lt($nextLoginTime)) {
                            $pairedLogout = $ev;
                            $consumedLogouts[] = $ev['time']->timestamp;
                            break;
                        }
                    }
                }

                $logoutTime = $pairedLogout ? $pairedLogout['time'] : null;

                if ($logoutTime) {
                    $durationEnd = $logoutTime;
                    $durationDiff = $loginTime->diff($durationEnd);
                    $durationMins = (int) $durationDiff->totalMinutes;
                    $durationStr = $this->formatDuration($durationDiff);
                    $activeMins = $durationMins;
                    $activeStr = $durationStr;
                    $inactiveDiff = $nextLoginTime ? $logoutTime->diff($nextLoginTime) : null;
                    $inactiveMins = $inactiveDiff ? (int) $inactiveDiff->totalMinutes : 0;
                    $inactiveStr = $inactiveDiff ? $this->formatDuration($inactiveDiff) : '--';
                    $logoutDisplay = $logoutTime->format('m-d-Y H:i');
                } elseif ($nextLoginTime) {
                    $durationEnd = $nextLoginTime;
                    $durationDiff = $loginTime->diff($durationEnd);
                    $durationMins = (int) $durationDiff->totalMinutes;
                    $durationStr = $this->formatDuration($durationDiff);
                    $activeMins = $durationMins;
                    $activeStr = $durationStr;
                    $inactiveMins = 0;
                    $inactiveStr = '--';
                    $logoutDisplay = $nextLoginTime->format('m-d-Y H:i');
                } else {
                    // Ongoing active session right now
                    $durationEnd = Carbon::now();
                    $durationDiff = $loginTime->diff($durationEnd);
                    $durationMins = (int) $durationDiff->totalMinutes;
                    $durationStr = $this->formatDuration($durationDiff);
                    $activeMins = $durationMins;
                    $activeStr = $durationStr;
                    $inactiveMins = 0;
                    $inactiveStr = '--';
                    $logoutDisplay = 'Active Now';
                }

                if (!$loginTime->between($rangeStart, $rangeEnd)) {
                    continue;
                }

                $sessions[] = [
                    'user_id' => $user->id,
                    'user_code' => strtoupper(str_replace(' ', '_', $user->name)),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'office' => $office,
                    'login' => $loginTime->format('m-d-Y H:i'),
                    'logout' => $logoutDisplay,
                    'duration' => $durationStr,
                    'duration_mins' => $durationMins,
                    'active' => $activeStr,
                    'active_mins' => $activeMins,
                    'inactive' => $inactiveStr,
                    'inactive_mins' => $inactiveMins,
                    'active_duration' => $activeStr,
                    '_login_ts' => $loginTime->timestamp,
                ];
            }
        }

        $sortFieldMap = [
            'user_code' => 'user_code',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'office' => 'office',
            'login' => '_login_ts',
            'logout' => '_login_ts',
            'duration' => 'duration_mins',
            'active' => 'active_mins',
            'inactive' => 'inactive_mins',
            'active_duration' => 'active_mins',
        ];
        $sortKey = $sortFieldMap[$sortBy] ?? '_login_ts';
        $dir = $sortDir === 'asc' ? 1 : -1;
        usort($sessions, function ($a, $b) use ($sortKey, $dir) {
            $va = $a[$sortKey] ?? '';
            $vb = $b[$sortKey] ?? '';
            $cmp = is_numeric($va) && is_numeric($vb)
                ? $va <=> $vb
                : strcasecmp((string)$va, (string)$vb);
            return $cmp * $dir;
        });

        $total = count($sessions);
        $paginated = array_slice($sessions, ($page - 1) * $perPage, $perPage);

        $paginated = array_map(fn($s) => array_diff_key($s, [
            '_login_ts' => true, 'duration_mins' => true,
            'active_mins' => true, 'inactive_mins' => true,
        ]), $paginated);

        return response()->json([
            'rows' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function userLogPrint(Request $request)
    {
        // Reuse the same logic but without pagination
        $request->merge(['page' => 1, 'per_page' => 10000]);
        $resp = $this->userLogData($request);
        $data = json_decode($resp->getContent(), true);
        $rows = $data['rows'] ?? [];
        
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');
        
        $userName = 'All Users';
        if ($userId) {
            $user = User::find($userId);
            $userName = $user ? $user->name : 'All Users';
        }
        
        return view('report.user-log-print', [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'userName' => $userName,
            'totalRecords' => count($rows)
        ]);
    }

    public function shipmentReportDownload(Request $request)
    {
        $request->merge(['sort_by' => 'post_date', 'sort_dir' => 'desc', 'page' => '1', 'per_page' => '10000']);
        $resp = $this->shipmentReportData($request);
        $data = json_decode($resp->getContent(), true);
        $rows = $data['rows'] ?? [];

        $officeNames = Office::pluck('name', 'id')->toArray();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shipment-report-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'File No', 'MBL/MAWB', 'Ship Mode', 'Office', 'Post Date', 'ETD', 'ETA', 'POL', 'POD', 'Vessel', 'Voyage', 'Customer', 'Consignee', 'Freight Term']);

            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['type'] ?? '',
                    $r['file_no'] ?? '',
                    $r['mbl_no'] ?? '',
                    $r['ship_mode'] ?? '',
                    $r['office_name'] ?? '',
                    $r['post_date'] ?? '',
                    $r['etd'] ?? '',
                    $r['eta'] ?? '',
                    $r['pol_name'] ?? '',
                    $r['pod_name'] ?? '',
                    $r['vessel_name'] ?? '',
                    $r['voyage'] ?? '',
                    $r['customer'] ?? '',
                    $r['consignee'] ?? '',
                    $r['freight_term'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function formatDuration($diff)
    {
        $hours = $diff->h + ($diff->d * 24);
        $minutes = $diff->i;
        return sprintf('%02d hours %02d mins', $hours, $minutes);
    }

    public function shipmentReport()
    {
        $offices = Office::orderBy('name')->get();
        $partners = TradePartner::orderBy('name')->select('id', 'name', 'type')->get();
        return view('report.shipment', compact('offices', 'partners'));
    }

    public function shipmentReportData(Request $request)
    {
        $search = $request->input('search', '');
        $shipType = $request->input('ship_type', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $dateField = $request->input('date_field', 'post_date');
        $officeId = $request->input('office_id', '');
        $tradePartnerId = $request->input('trade_partner_id', '');
        $sortBy = $request->input('sort_by', 'post_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        $companyId = auth()->user()?->company_id ?? 1;

        // If trade partner filter is set, find matching shipment IDs via HBLs
        $tpShipIds = null;
        if ($tradePartnerId) {
            $tpShipIds = [
                'ocean_import' => DB::table('ocean_import_hbls')->where('company_id', $companyId)->where('customer_id', $tradePartnerId)->pluck('ocean_import_id')->toArray(),
                'ocean_export' => DB::table('ocean_export_hbls')->where('company_id', $companyId)->where('customer_id', $tradePartnerId)->pluck('ocean_export_id')->toArray(),
                'air_import' => DB::table('air_import_hbls')->where('company_id', $companyId)->where('customer_id', $tradePartnerId)->pluck('air_import_id')->toArray(),
                'air_export' => DB::table('air_export_hbls')->where('company_id', $companyId)->where('customer_id', $tradePartnerId)->pluck('air_export_id')->toArray(),
                'truck' => DB::table('truck_shipments')->where('company_id', $companyId)->where('customer_id', $tradePartnerId)->pluck('id')->toArray(),
            ];
        }

        $allRows = [];

        $loadOcean = empty($shipType) || $shipType === 'ocean_import';
        $loadExport = empty($shipType) || $shipType === 'ocean_export';
        $loadAirImp = empty($shipType) || $shipType === 'air_import';
        $loadAirExp = empty($shipType) || $shipType === 'air_export';
        $loadTruck = empty($shipType) || $shipType === 'truck';

        if ($loadOcean) {
            $q = DB::table('ocean_imports')->where('company_id', $companyId)->whereNull('deleted_at');
            if ($officeId) $q->where('office_id', $officeId);
            if ($tradePartnerId && isset($tpShipIds['ocean_import'])) {
                $q->whereIn('id', $tpShipIds['ocean_import'] ?: [0]);
            }
            if ($dateFrom && $dateTo) {
                $col = in_array($dateField, ['post_date','etd','eta']) ? $dateField : 'post_date';
                $q->whereBetween($col, [$dateFrom, $dateTo]);
            }
            foreach ($q->get() as $r) {
                $allRows[] = [
                    'id' => $r->id,
                    'type' => 'Ocean Import',
                    'type_key' => 'ocean_import',
                    'file_no' => $r->file_no,
                    'mbl_no' => $r->mbl_no,
                    'ship_mode' => $r->ship_mode,
                    'office_id' => $r->office_id,
                    'post_date' => $r->post_date,
                    'etd' => $r->etd,
                    'eta' => $r->eta,
                    'pol_id' => $r->pol_id,
                    'pod_id' => $r->pod_id,
                    'vessel_id' => $r->vessel_id,
                    'voyage' => $r->voyage,
                    'freight_term' => $r->freight_term,
                    'color' => $r->color,
                    'is_blocked' => $r->is_blocked,
                    'is_hold' => $r->is_hold,
                    'customer' => '',
                    'consignee' => '',
                    'container_info' => '',
                    'pkg_qty' => '',
                    'weight' => '',
                    'volume' => '',
                    'created_at' => $r->created_at,
                ];
            }
        }

        if ($loadExport) {
            $q = DB::table('ocean_exports')->where('company_id', $companyId)->whereNull('deleted_at');
            if ($officeId) $q->where('office_id', $officeId);
            if ($tradePartnerId && isset($tpShipIds['ocean_export'])) {
                $q->whereIn('id', $tpShipIds['ocean_export'] ?: [0]);
            }
            if ($dateFrom && $dateTo) {
                $col = in_array($dateField, ['post_date','etd','eta']) ? $dateField : 'post_date';
                $q->whereBetween($col, [$dateFrom, $dateTo]);
            }
            foreach ($q->get() as $r) {
                $allRows[] = [
                    'id' => $r->id,
                    'type' => 'Ocean Export',
                    'type_key' => 'ocean_export',
                    'file_no' => $r->file_no,
                    'mbl_no' => $r->mbl_no,
                    'ship_mode' => $r->ship_mode,
                    'office_id' => $r->office_id,
                    'post_date' => $r->post_date,
                    'etd' => $r->etd,
                    'eta' => $r->eta,
                    'pol_id' => $r->pol_id,
                    'pod_id' => $r->pod_id,
                    'vessel_id' => $r->vessel_id,
                    'voyage' => $r->voyage,
                    'freight_term' => $r->freight_term,
                    'color' => $r->color,
                    'is_blocked' => $r->is_blocked,
                    'is_hold' => 0,
                    'customer' => '',
                    'consignee' => '',
                    'container_info' => '',
                    'pkg_qty' => '',
                    'weight' => '',
                    'volume' => '',
                    'created_at' => $r->created_at,
                ];
            }
        }

        if ($loadAirImp) {
            $q = DB::table('air_imports')->where('company_id', $companyId)->whereNull('deleted_at');
            if ($officeId) $q->where('office_id', $officeId);
            if ($tradePartnerId && isset($tpShipIds['air_import'])) {
                $q->whereIn('id', $tpShipIds['air_import'] ?: [0]);
            }
            if ($dateFrom && $dateTo) {
                $col = in_array($dateField, ['post_date','etd','eta']) ? $dateField : 'post_date';
                $q->whereBetween($col, [$dateFrom, $dateTo]);
            }
            foreach ($q->get() as $r) {
                $allRows[] = [
                    'id' => $r->id,
                    'type' => 'Air Import',
                    'type_key' => 'air_import',
                    'file_no' => $r->file_no,
                    'mbl_no' => $r->mawb_no,
                    'ship_mode' => $r->ship_mode,
                    'office_id' => $r->office_id,
                    'post_date' => $r->post_date,
                    'etd' => $r->etd,
                    'eta' => $r->eta,
                    'pol_id' => $r->dep_port_id,
                    'pod_id' => $r->dst_port_id,
                    'vessel_id' => null,
                    'voyage' => $r->flight_no,
                    'freight_term' => $r->freight_term,
                    'color' => $r->color,
                    'is_blocked' => $r->is_blocked,
                    'is_hold' => 0,
                    'customer' => '',
                    'consignee' => '',
                    'container_info' => '',
                    'pkg_qty' => $r->pkg_qty,
                    'weight' => $r->gross_weight,
                    'volume' => $r->chargeable_weight,
                    'created_at' => $r->created_at,
                ];
            }
        }

        if ($loadAirExp) {
            $q = DB::table('air_exports')->where('company_id', $companyId)->whereNull('deleted_at');
            if ($officeId) $q->where('office_id', $officeId);
            if ($tradePartnerId && isset($tpShipIds['air_export'])) {
                $q->whereIn('id', $tpShipIds['air_export'] ?: [0]);
            }
            if ($dateFrom && $dateTo) {
                $col = in_array($dateField, ['post_date','etd','eta']) ? $dateField : 'post_date';
                $q->whereBetween($col, [$dateFrom, $dateTo]);
            }
            foreach ($q->get() as $r) {
                $allRows[] = [
                    'id' => $r->id,
                    'type' => 'Air Export',
                    'type_key' => 'air_export',
                    'file_no' => $r->file_no,
                    'mbl_no' => $r->mawb_no,
                    'ship_mode' => '',
                    'office_id' => $r->office_id,
                    'post_date' => $r->post_date,
                    'etd' => $r->etd,
                    'eta' => $r->eta,
                    'pol_id' => $r->dep_port_id,
                    'pod_id' => $r->dst_port_id,
                    'vessel_id' => null,
                    'voyage' => $r->flight_no,
                    'freight_term' => $r->freight_term,
                    'color' => $r->color,
                    'is_blocked' => $r->is_blocked,
                    'is_hold' => 0,
                    'customer' => '',
                    'consignee' => '',
                    'container_info' => '',
                    'pkg_qty' => $r->pkg_qty,
                    'weight' => $r->gross_weight,
                    'volume' => $r->chargeable_weight,
                    'created_at' => $r->created_at,
                ];
            }
        }

        if ($loadTruck) {
            $q = DB::table('truck_shipments')->where('company_id', $companyId)->whereNull('deleted_at');
            if ($officeId) $q->where('office_id', $officeId);
            if ($tradePartnerId && isset($tpShipIds['truck'])) {
                $q->whereIn('id', $tpShipIds['truck'] ?: [0]);
            }
            if ($dateFrom && $dateTo) {
                $col = in_array($dateField, ['post_date','etd','eta']) ? $dateField : 'post_date';
                $q->whereBetween($col, [$dateFrom, $dateTo]);
            }
            foreach ($q->get() as $r) {
                $allRows[] = [
                    'id' => $r->id,
                    'type' => 'Truck',
                    'type_key' => 'truck',
                    'file_no' => $r->file_no,
                    'mbl_no' => $r->mbl_no,
                    'ship_mode' => $r->ship_type,
                    'office_id' => $r->office_id,
                    'post_date' => $r->post_date,
                    'etd' => $r->etd,
                    'eta' => $r->eta,
                    'pol_id' => $r->pol_id,
                    'pod_id' => $r->pod_id,
                    'vessel_id' => null,
                    'voyage' => $r->vessel_flight_no,
                    'freight_term' => '',
                    'color' => $r->color,
                    'is_blocked' => $r->is_blocked,
                    'is_hold' => 0,
                    'customer' => '',
                    'consignee' => '',
                    'container_info' => '',
                    'pkg_qty' => $r->pkg_qty,
                    'weight' => $r->weight_kg,
                    'volume' => $r->volume_cbm,
                    'created_at' => $r->created_at,
                ];
            }
        }

        // Resolve related names
        $officeNames = Office::pluck('name', 'id')->toArray();
        $portNames = Port::pluck('name', 'id')->toArray();
        $vesselNames = Vessel::pluck('name', 'id')->toArray();

        // Load HBL customer/consignee for ocean imports
        $oceanHbls = DB::table('ocean_import_hbls')
            ->select('ocean_import_id', 'customer_id', 'consignee_id')
            ->get()->groupBy('ocean_import_id');
        $oceanCustomer = [];
        $oceanConsignee = [];
        foreach ($oceanHbls as $oiId => $hbls) {
            $oceanCustomer[$oiId] = $hbls->pluck('customer_id')->filter()->implode(', ');
            $oceanConsignee[$oiId] = $hbls->pluck('consignee_id')->filter()->implode(', ');
        }

        // Resolve customer/consignee names
        $tpNames = TradePartner::pluck('name', 'id')->toArray();
        foreach ($allRows as &$row) {
            $row['office_name'] = $officeNames[$row['office_id']] ?? '';
            $row['pol_name'] = $portNames[$row['pol_id'] ?? ''] ?? '';
            $row['pod_name'] = $portNames[$row['pod_id'] ?? ''] ?? '';
            $row['vessel_name'] = $row['vessel_id'] ? ($vesselNames[$row['vessel_id']] ?? '') : '';

            if ($row['type_key'] === 'ocean_import') {
                $custIds = $oceanCustomer[$row['id']] ?? '';
                $conIds = $oceanConsignee[$row['id']] ?? '';
                if ($custIds) {
                    $names = [];
                    foreach (explode(', ', $custIds) as $cid) {
                        $names[] = $tpNames[$cid] ?? "ID:$cid";
                    }
                    $row['customer'] = implode(', ', $names);
                }
                if ($conIds) {
                    $names = [];
                    foreach (explode(', ', $conIds) as $cid) {
                        $names[] = $tpNames[$cid] ?? "ID:$cid";
                    }
                    $row['consignee'] = implode(', ', $names);
                }
            }
        }
        unset($row);

        // Search
        if ($search) {
            $s = strtolower($search);
            $allRows = array_filter($allRows, function ($r) use ($s, $tpNames, $officeNames, $portNames) {
                return str_contains(strtolower($r['file_no'] ?? ''), $s)
                    || str_contains(strtolower($r['mbl_no'] ?? ''), $s)
                    || str_contains(strtolower($r['type'] ?? ''), $s)
                    || str_contains(strtolower($r['ship_mode'] ?? ''), $s)
                    || str_contains(strtolower($r['freight_term'] ?? ''), $s)
                    || str_contains(strtolower($r['voyage'] ?? ''), $s)
                    || str_contains(strtolower($r['customer'] ?? ''), $s)
                    || str_contains(strtolower($r['consignee'] ?? ''), $s)
                    || str_contains(strtolower($officeNames[$r['office_id']] ?? ''), $s)
                    || str_contains(strtolower($portNames[$r['pol_id'] ?? ''] ?? ''), $s)
                    || str_contains(strtolower($portNames[$r['pod_id'] ?? ''] ?? ''), $s);
            });
            $allRows = array_values($allRows);
        }

        // Sort
        $sortCol = $sortBy;
        usort($allRows, function ($a, $b) use ($sortCol, $sortDir) {
            $va = $a[$sortCol] ?? '';
            $vb = $b[$sortCol] ?? '';
            if (is_null($va)) $va = '';
            if (is_null($vb)) $vb = '';
            $cmp = strcasecmp((string) $va, (string) $vb);
            return $sortDir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($allRows);
        $paginated = array_slice($allRows, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'rows' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function containerStorage(Request $request)
    {
        return view('report.container-storage');
    }

    public function containerStoragePrint(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $departments = json_decode($request->input('departments', '[]'), true);
        $officeId = $request->input('office_id');
        $partyType = $request->input('party_type', 'customer');
        $partyId = $request->input('party_id');
        $showWithoutStartDate = $request->input('show_without_start_date', false);

        $containers = [];
        $totalDays = 0;

        // Get party name
        $party = TradePartner::find($partyId);
        $partyName = $party ? $party->name : 'N/A';

        // Get office name
        $office = $officeId ? Office::find($officeId) : null;
        $officeName = $office ? $office->name : 'All Offices';

        // Department types display
        $departmentTypes = implode(', ', $departments);

        // Query containers based on departments
        foreach ($departments as $dept) {
            $deptContainers = [];

            switch ($dept) {
                case 'Ocean Import':
                    $query = DB::table('ocean_import_containers as c')
                        ->join('ocean_imports as oi', 'c.ocean_import_id', '=', 'oi.id')
                        ->leftJoin('container_types as ct', 'c.container_type_id', '=', 'ct.id')
                        ->select(
                            'c.container_no',
                            'ct.code as tp_sz',
                            'oi.file_no',
                            'oi.mbl_no',
                            DB::raw('NULL as hbl_no'),
                            'c.storage_start_date as start_date',
                            'c.storage_end_date as end_date'
                        );

                    if ($partyType === 'customer') {
                        $query->where('oi.dm_customer_id', $partyId);
                    } else {
                        $query->where('oi.oversea_agent_id', $partyId);
                    }

                    if ($officeId) {
                        $query->where('oi.office_id', $officeId);
                    }

                    if ($showWithoutStartDate) {
                        $query->where(function ($q) use ($dateFrom, $dateTo) {
                            $q->whereNull('c.storage_start_date')
                              ->orWhereBetween('c.storage_start_date', [$dateFrom, $dateTo]);
                        });
                    } else {
                        $query->whereBetween('c.storage_start_date', [$dateFrom, $dateTo]);
                    }

                    $deptContainers = $query->get();
                    break;

                case 'Ocean Export':
                    $query = DB::table('ocean_export_containers as c')
                        ->join('ocean_exports as oe', 'c.ocean_export_id', '=', 'oe.id')
                        ->leftJoin('container_types as ct', 'c.container_type_id', '=', 'ct.id')
                        ->select(
                            'c.container_no',
                            'ct.code as tp_sz',
                            'oe.file_no',
                            'oe.mbl_no',
                            DB::raw('NULL as hbl_no'),
                            'c.storage_start_date as start_date',
                            'c.storage_end_date as end_date'
                        );

                    if ($partyType === 'customer') {
                        $query->where('oe.dm_customer_id', $partyId);
                    } else {
                        $query->where('oe.oversea_agent_id', $partyId);
                    }

                    if ($officeId) {
                        $query->where('oe.office_id', $officeId);
                    }

                    if ($showWithoutStartDate) {
                        $query->where(function ($q) use ($dateFrom, $dateTo) {
                            $q->whereNull('c.storage_start_date')
                              ->orWhereBetween('c.storage_start_date', [$dateFrom, $dateTo]);
                        });
                    } else {
                        $query->whereBetween('c.storage_start_date', [$dateFrom, $dateTo]);
                    }

                    $deptContainers = $query->get();
                    break;

                case 'Trucker':
                    $query = DB::table('truck_shipment_containers as c')
                        ->join('truck_shipments as ts', 'c.truck_shipment_id', '=', 'ts.id')
                        ->leftJoin('container_types as ct', 'c.container_type_id', '=', 'ct.id')
                        ->select(
                            'c.container_no',
                            'ct.code as tp_sz',
                            'ts.file_no',
                            DB::raw('NULL as mbl_no'),
                            DB::raw('NULL as hbl_no'),
                            'c.pickup_date as start_date',
                            'c.empty_return_date as end_date'
                        );

                    if ($partyType === 'customer') {
                        $query->where('ts.customer_id', $partyId);
                    }

                    if ($officeId) {
                        $query->where('ts.office_id', $officeId);
                    }

                    if ($showWithoutStartDate) {
                        $query->where(function ($q) use ($dateFrom, $dateTo) {
                            $q->whereNull('c.pickup_date')
                              ->orWhereBetween('c.pickup_date', [$dateFrom, $dateTo]);
                        });
                    } else {
                        $query->whereBetween('c.pickup_date', [$dateFrom, $dateTo]);
                    }

                    $deptContainers = $query->get();
                    break;
            }

            // Calculate storage days and add to main collection
            foreach ($deptContainers as $container) {
                $containerData = (array) $container;
                
                if ($containerData['start_date'] && $containerData['end_date']) {
                    $start = Carbon::parse($containerData['start_date']);
                    $end = Carbon::parse($containerData['end_date']);
                    $days = $start->diffInDays($end);
                    $containerData['storage_days'] = $days;
                    $totalDays += $days;
                } else {
                    $containerData['storage_days'] = 0;
                }

                $containers[] = $containerData;
            }
        }

        return view('report.container-storage-print', [
            'containers' => $containers,
            'totalDays' => $totalDays,
            'partyName' => $partyName,
            'officeName' => $officeName,
            'departmentTypes' => $departmentTypes
        ]);
    }


}