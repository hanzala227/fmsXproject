<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Currency;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RevenueCostController extends Controller
{
    private $shippingTypeMap = [
        'App\\Models\\OceanImport'       => 'Ocean Import',
        'App\\Models\\OceanExport'       => 'Ocean Export',
        'App\\Models\\AirImport'         => 'Air Import',
        'App\\Models\\AirExport'         => 'Air Export',
        'App\\Models\\TruckShipment'     => 'Truck Operation',
        'App\\Models\\WarehouseShipping' => 'Warehouse Operation',
        'App\\Models\\WarehouseReceiving'=> 'Warehouse Operation',
        'App\\Models\\WorkOrder'         => 'Misc. Operation',
    ];

    public function index()
    {
        $companyId     = auth()->user()?->company_id;
        $offices       = Office::where('is_active', true)->orderBy('name')->get();
        $users         = User::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();
        $tradePartners = TradePartner::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();

        return view('accounting.revenue-cost', compact('offices', 'users', 'tradePartners'));
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'office_id'       => 'nullable|exists:offices,id',
            'type'            => 'nullable|in:revenue,cost,all',
            'shipping_types'  => 'nullable|string',
            'payment_status'  => 'nullable|in:all,paid,not_paid,partial',
            'period_type'     => 'nullable|in:post_date,paid_date,invoice_date',
            'bill_to_id'      => 'nullable|exists:trade_partners,id',
            'sales_person_id' => 'nullable|exists:users,id',
            'report_type'     => 'nullable|in:summary,detail',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $officeId      = $request->input('office_id');
        $type          = $request->input('type', 'revenue');
        $shippingTypes = $request->input('shipping_types', 'all');
        $paymentStatus = $request->input('payment_status', 'all');
        $periodType    = $request->input('period_type', 'invoice_date');
        $billToId      = $request->input('bill_to_id');
        $salesPersonId = $request->input('sales_person_id');
        $reportType    = $request->input('report_type', 'summary');
        $companyId     = auth()->user()->company_id ?? 1;

        // Currency conversion helper to base USD
        $toUSD = function ($amount, $currCode, $modelRate = null) {
            if ($modelRate && (float) $modelRate > 0) {
                return ((float) $modelRate != 1.0) ? round(((float) $amount) / (float) $modelRate, 2) : (float) $amount;
            }
            $currCode = $currCode ?: 'USD';
            if ($currCode === 'USD') return (float) $amount;

            $rate = 1.0;
            $rateRecord = DB::table('currency_rates')
                ->where('from_currency', $currCode)
                ->where('to_currency', 'USD')
                ->orderByDesc('as_of_date')
                ->first();
            if ($rateRecord && (float) $rateRecord->rate_internal > 0) {
                $rate = (float) $rateRecord->rate_internal;
            } else {
                $curr = Currency::where('code', $currCode)->first();
                if ($curr && (float) $curr->exchange_rate > 0) {
                    $rate = (float) $curr->exchange_rate;
                }
            }
            return ($rate > 0 && $rate != 1.0) ? round(((float) $amount) / $rate, 2) : (float) $amount;
        };

        if ($periodType === 'paid_date') {
            $paidInvoiceIds = AccountingPayment::where('company_id', $companyId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->whereNull('void_date')
                ->whereNotNull('invoice_id')
                ->pluck('invoice_id')
                ->unique();

            $query = Invoice::where('company_id', $companyId)->whereIn('id', $paidInvoiceIds);
        } else {
            $query = Invoice::where('company_id', $companyId)->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        // Exclude unverified, DRAFT, VOID, Cancelled, and Rejected documents
        $query->reportable();

        if ($officeId) {
            $query->where('office_id', $officeId);
        }

        if ($type === 'revenue') {
            $query->where('type', 'AR');
        } elseif ($type === 'cost') {
            $query->where('type', 'AP');
        }

        if ($shippingTypes !== 'all' && !empty($shippingTypes)) {
            $allowedTypes = array_map('trim', explode(',', $shippingTypes));
            $modelTypes   = [];
            foreach ($this->shippingTypeMap as $model => $label) {
                if (in_array($label, $allowedTypes)) {
                    $modelTypes[] = $model;
                }
            }
            if (!empty($modelTypes)) {
                $query->whereIn('invoiceable_type', $modelTypes);
            }
        }

        if ($billToId) {
            $query->where('bill_to_id', $billToId);
        }

        if ($salesPersonId) {
            $query->where('issued_by', $salesPersonId);
        }

        $invoices = $query->with([
                'billTo',
                'currency',
                'office',
                'issuer',
                'payments' => fn($p) => $p->whereNull('void_date')->whereNull('cancelled_at'),
            ])->orderBy('invoice_date')->get();

        $results = [];
        foreach ($invoices as $invoice) {
            $activePays = $invoice->payments->whereNull('void_date')->whereNull('cancelled_at');
            $totalPaid  = (float) ($activePays->sum('amount') + $activePays->sum('tds_tax') + $activePays->sum('discount'));
            $rawAmount  = (float) $invoice->total_amount;
            $invType    = strtoupper($invoice->type ?? '');
            $docType    = strtoupper($invoice->document_type ?? '');
            $isCN       = $docType === 'CN';
            $isDN       = $docType === 'DN';
            $currCode   = $invoice->currency?->code ?? 'USD';

            $baseAmount = $toUSD($rawAmount, $currCode, $invoice->exchange_rate);
            $basePaid   = $toUSD($totalPaid, $currCode, $invoice->exchange_rate);

            // In Revenue (AR), CN is negative; In Cost (AP), DN/CN is negative
            if ($invType === 'AR') {
                $netAmount = $isCN ? -$baseAmount : $baseAmount;
            } else {
                $netAmount = ($isDN || $isCN) ? -$baseAmount : $baseAmount;
            }
            $balance = $netAmount - $basePaid;
            if (strtoupper($invoice->status ?? '') === 'PAID' || abs($balance) <= 0.01) {
                $balance = 0.0;
            }

            if ($paymentStatus === 'paid' && abs($balance) > 0.01) continue;
            if ($paymentStatus === 'not_paid' && $basePaid > 0.01) continue;
            if ($paymentStatus === 'partial' && ($basePaid <= 0.01 || abs($balance) <= 0.01)) continue;

            $shippingLabel = $this->shippingTypeMap[$invoice->invoiceable_type ?? ''] ?? 'Other Operation';
            $partnerName   = $invoice->billTo?->name ?? 'N/A';
            $salesPerson   = $invoice->issuer?->name ?? 'N/A';
            $officeName    = $invoice->office?->name ?? 'HQ';

            $status = 'Not Paid';
            if (abs($balance) <= 0.01) {
                $status = 'Paid';
            } elseif ($basePaid > 0.01) {
                $status = 'Partial';
            }

            $results[] = [
                'invoice_no'    => $invoice->invoice_no ?? '',
                'invoice_date'  => $invoice->invoice_date?->format('Y-m-d') ?? '',
                'due_date'      => $invoice->due_date?->format('Y-m-d') ?? '',
                'partner_name'  => $partnerName,
                'sales_person'  => $salesPerson,
                'office'        => $officeName,
                'currency'      => 'USD',
                'type'          => $invType,
                'shipping_type' => $shippingLabel,
                'total_amount'  => round($netAmount, 2),
                'paid_amount'   => round($basePaid, 2),
                'balance'       => round($balance, 2),
                'status'        => $status,
            ];
        }

        if ($reportType === 'summary') {
            $grouped = [];
            foreach ($results as $r) {
                $key = $r['shipping_type'] . '|' . $r['currency'];
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'shipping_type' => $r['shipping_type'],
                        'currency'      => $r['currency'],
                        'total_amount'  => 0,
                        'paid_amount'   => 0,
                        'balance'       => 0,
                        'count'         => 0,
                    ];
                }
                $grouped[$key]['total_amount'] += $r['total_amount'];
                $grouped[$key]['paid_amount']  += $r['paid_amount'];
                $grouped[$key]['balance']      += $r['balance'];
                $grouped[$key]['count']++;
            }
            $results = array_values($grouped);
        }

        $totalAmount = round(array_sum(array_column($results, 'total_amount')), 2);
        $totalPaid   = round(array_sum(array_column($results, 'paid_amount')), 2);
        $totalBal    = round(array_sum(array_column($results, 'balance')), 2);

        return response()->json([
            'success'      => true,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'type'         => $type,
            'report_type'  => $reportType,
            'results'      => $results,
            'summary'      => [
                'total_amount'  => $totalAmount,
                'total_paid'    => $totalPaid,
                'total_balance' => $totalBal,
                'count'         => count($results),
            ],
        ]);
    }

    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.revenue-cost-print', [
            'results'    => $data['results'] ?? [],
            'summary'    => $data['summary'] ?? [],
            'startDate'  => $request->start_date ?? date('Y-m-d'),
            'endDate'    => $request->end_date ?? date('Y-m-d'),
            'type'       => $request->type ?? 'revenue',
            'reportType' => $request->report_type ?? 'summary',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data    = $this->view($request)->getData(true);
        $results = $data['results'] ?? [];
        $summary = $data['summary'] ?? [];
        $type    = $request->type ?? 'revenue';

        $filename = 'revenue-cost-report-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($results, $summary, $type, $request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $typeLabel = match($type) { 'revenue' => 'Revenue', 'cost' => 'Cost', default => 'Revenue / Cost' };
            fputcsv($handle, [$typeLabel . ' Report']);
            fputcsv($handle, ['Period', ($request->start_date ?? '') . ' ~ ' . ($request->end_date ?? '')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Shipping Type',
                'Currency',
                'Total Amount',
                'Paid Amount',
                'Balance',
                'Count',
            ]);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['shipping_type'] ?? $row['partner_name'] ?? '',
                    $row['currency'],
                    number_format($row['total_amount'], 2),
                    number_format($row['paid_amount'], 2),
                    number_format($row['balance'], 2),
                    $row['count'] ?? 1,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                '',
                number_format($summary['total_amount'] ?? 0, 2),
                number_format($summary['total_paid'] ?? 0, 2),
                number_format($summary['total_balance'] ?? 0, 2),
                $summary['count'] ?? 0,
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
