<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgentLocalStatementController extends Controller
{
    public function index()
    {
        $offices       = Office::where('is_active', true)->orderBy('name')->get();
        $users         = User::orderBy('name')->get();
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies    = Currency::orderBy('name')->get();
        $accountGroups = TradePartner::whereNotNull('credit_limit_group_name')
            ->where('credit_limit_group_name', '!=', '')
            ->distinct()
            ->pluck('credit_limit_group_name')
            ->sort()
            ->values();

        return view('accounting.agent-local-statement', compact('offices', 'users', 'tradePartners', 'currencies', 'accountGroups'));
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date'         => 'required|date',
            'office_id'          => 'nullable|exists:offices,id',
            'partner_type'       => 'nullable|in:agent_customer,account_group',
            'bill_to_id'         => 'nullable|exists:trade_partners,id',
            'account_group'      => 'nullable|string',
            'display_currency'   => 'nullable|string',
            'freight_currency'   => 'nullable|string',
            'payment_status'     => 'nullable|in:all,open,paid',
            'trans_type'         => 'nullable|string',
            'sales_person_id'    => 'nullable|exists:users,id',
            'show_aging'         => 'nullable|in:0,1',
            'show_payment'       => 'nullable|in:0,1',
            'hide_overpaid'      => 'nullable|in:0,1',
            'show_balanced'      => 'nullable|in:0,1',
            'dc_format'          => 'nullable|in:combined,separated',
            'acct_mode'          => 'nullable|in:invoice,shipment',
            'amount_confirmed'   => 'nullable|in:all,yes,no',
            'block'              => 'nullable|in:0,1',
            'unblock'            => 'nullable|in:0,1',
            'show_etd'           => 'nullable|in:0,1',
            'show_eta'           => 'nullable|in:0,1',
            'group_department'   => 'nullable|in:0,1',
            'show_credit_limit'  => 'nullable|in:0,1',
            'invoice_local_received' => 'nullable|in:0,1',
            'partner_name'       => 'nullable|string',
            'filter_partner'     => 'nullable|string',
            'filter_office'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $asOfDate          = $request->input('as_of_date');
        $officeId          = $request->input('office_id');
        $billToId          = $request->input('bill_to_id');
        $partnerType       = $request->input('partner_type', 'agent_customer');
        $accountGroup      = $request->input('account_group', '');
        $paymentStatus     = $request->input('payment_status', 'all');
        $transType         = $request->input('trans_type', 'all');
        $salesPersonId     = $request->input('sales_person_id');
        $displayCurrency   = $request->input('display_currency', 'all');
        $freightCurrency   = $request->input('freight_currency', '');
        $amountConfirmed   = $request->input('amount_confirmed', 'all');
        $hideOverpaid      = $request->input('hide_overpaid', '0');
        $showBalanced      = $request->input('show_balanced', '1');
        $dcFormat          = $request->input('dc_format', 'combined');
        $acctMode          = $request->input('acct_mode', 'invoice');
        $showAging         = $request->input('show_aging', '0');
        $showPayment       = $request->input('show_payment', '0');
        $block             = $request->input('block', '1');
        $unblock           = $request->input('unblock', '1');
        $showEtd           = $request->input('show_etd', '0');
        $showEta           = $request->input('show_eta', '0');
        $groupDepartment   = $request->input('group_department', '0');
        $showCreditLimit   = $request->input('show_credit_limit', '1');
        $invoiceLocalReceived = $request->input('invoice_local_received', '0');
        $filterPartner        = $request->input('filter_partner') ?: $request->input('partner_name', '');
        $filterOffice         = $request->input('filter_office', '');

        $asOfCarbon = Carbon::parse($asOfDate);

        $query = Invoice::where('invoice_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->when($salesPersonId, fn($q) => $q->where('issued_by', $salesPersonId))
            ->with(['billTo', 'currency', 'office', 'payments', 'invoiceable']);

        // Filter by partner (filter_partner / partner_name, account_group, or bill_to_id)
        if ($filterPartner) {
            $query->whereHas('billTo', fn($q) => $q->where('name', 'like', "%{$filterPartner}%"));
        } elseif ($partnerType === 'account_group') {
            if ($accountGroup) {
                $query->whereHas('billTo', fn($q) => $q->where('credit_limit_group_name', $accountGroup));
            }
        } else {
            if ($billToId) {
                $query->where('bill_to_id', $billToId);
            }
        }

        // Filter by block/unblock (TradePartner status: INACTIVE = blocked)
        if ($block === '1' && $unblock === '0') {
            $query->whereHas('billTo', fn($q) => $q->where('status', 'INACTIVE'));
        } elseif ($block === '0' && $unblock === '1') {
            $query->whereHas('billTo', fn($q) => $q->where('status', '!=', 'INACTIVE'));
        }

        // Filter by amount confirmed (invoice status: POSTED/PARTIAL/PAID = confirmed, DRAFT = unconfirmed)
        if ($amountConfirmed === 'yes') {
            $query->whereIn('status', ['POSTED', 'PARTIAL', 'PAID']);
        } elseif ($amountConfirmed === 'no') {
            $query->where('status', 'DRAFT');
        }

        // Filter by Invoice(Local A/R) Received Only
        if ($invoiceLocalReceived === '1') {
            $query->where('type', 'AR')
                  ->whereHas('billTo', fn($q) => $q->where('type', 'CLIENT'));
        }

        // Filter by display currency
        if ($displayCurrency && $displayCurrency !== 'all') {
            $query->whereHas('currency', fn($q) => $q->where('code', $displayCurrency));
        }

        // Filter by freight currency
        if ($freightCurrency) {
            $query->whereHas('currency', fn($q) => $q->where('code', $freightCurrency));
        }

        // Filter by transaction type
        if ($transType !== 'all') {
            $transMap = [
                'debit' => ['AR'],
                'credit' => ['AP'],
                'ar'    => ['AR'],
                'ap'    => ['AP'],
                'ga_ar' => ['AR'],
                'ga_ap' => ['AP'],
            ];
            $rawTypes = array_map('strtoupper', array_map('trim', explode(',', $transType)));
            $allowedTypes = [];
            foreach ($rawTypes as $rt) {
                if (isset($transMap[strtolower($rt)])) {
                    $allowedTypes = array_merge($allowedTypes, $transMap[strtolower($rt)]);
                } else {
                    $allowedTypes[] = $rt;
                }
            }
            $allowedTypes = array_unique($allowedTypes);
            $query->whereIn('type', $allowedTypes);
        }

        $invoices = $query->get();

        $results = [];
        foreach ($invoices as $invoice) {
            $totalPaid = (float) $invoice->payments->sum('amount');
            $balance   = (float) $invoice->total_amount - $totalPaid;

            $skip = false;

            if ($paymentStatus === 'paid' && abs($balance) > 0.01) $skip = true;
            if ($paymentStatus === 'open' && abs($balance) < 0.01) $skip = true;

            if ($showBalanced === '1' && abs($balance) < 0.01) $skip = false;

            if ($hideOverpaid === '1' && $balance < 0) $skip = true;

            if ($skip) continue;

            $invType     = strtoupper($invoice->type ?? '');
            $partnerName = $invoice->billTo?->name ?? 'N/A';
            $officeName  = $invoice->office?->name ?? 'N/A';
            $currCode    = $invoice->currency?->code ?? $invoice->currency?->name ?? 'USD';
            $salesPerson   = $invoice->issuer?->name ?? 'N/A';

            $mblNo = '';
            $hblNo = '';
            $pol   = '';
            $pod   = '';
            $etd   = '';
            $eta   = '';
            $shipmentOffice = $officeName;
            $shipmentMode   = '';

            if ($invoice->invoiceable) {
                $mblNo = $invoice->invoiceable->mbl_no ?? $invoice->invoiceable->booking_no ?? '';
                $hblNo = $invoice->invoiceable->hbl_no ?? '';
                $pol   = $invoice->invoiceable->pol?->name ?? ($invoice->invoiceable->port_of_loading ?? '');
                $pod   = $invoice->invoiceable->pod?->name ?? ($invoice->invoiceable->port_of_discharge ?? '');
                $etd   = isset($invoice->invoiceable->etd) ? Carbon::parse($invoice->invoiceable->etd)->format('Y-m-d') : '';
                $eta   = isset($invoice->invoiceable->eta) ? Carbon::parse($invoice->invoiceable->eta)->format('Y-m-d') : '';
                $shipmentMode = $invoice->invoiceable->ship_mode 
                    ?? ($invoice->invoiceable->shipment_mode 
                    ?? (class_basename($invoice->invoiceable_type) ? preg_replace('/(?<!^)[A-Z]/', ' $0', class_basename($invoice->invoiceable_type)) : ''));
            }

            $daysOverdue = 0;
            if ($invoice->due_date) {
                $daysOverdue = max(0, $asOfCarbon->diffInDays(Carbon::parse($invoice->due_date), false));
            }

            $current = 0;
            $over1_30 = 0;
            $over31_60 = 0;
            $over61_90 = 0;
            $over90 = 0;

            if ($balance > 0) {
                if ($daysOverdue <= 0) {
                    $current = $balance;
                } elseif ($daysOverdue <= 30) {
                    $over1_30 = $balance;
                } elseif ($daysOverdue <= 60) {
                    $over31_60 = $balance;
                } elseif ($daysOverdue <= 90) {
                    $over61_90 = $balance;
                } else {
                    $over90 = $balance;
                }
            }

            $lastPaid = $invoice->payments->sortByDesc('payment_date')->first();
            $lastPaidDate = $lastPaid?->payment_date?->format('Y-m-d') ?? '';
            $drAmount = $invType === 'AR' ? (float) $invoice->total_amount : 0;
            $crAmount = $invType === 'AP' ? (float) $invoice->total_amount : 0;

            $results[] = [
                'invoice_no'     => $invoice->invoice_no ?? '',
                'invoice_date'   => $invoice->invoice_date?->format('Y-m-d') ?? '',
                'due_date'       => $invoice->due_date?->format('Y-m-d') ?? '',
                'partner_name'   => $partnerName,
                'office'         => $officeName,
                'shipment_mode'  => $shipmentMode ?: '--',
                'currency'       => $currCode,
                'type'           => $invType,
                'sales_person'   => $salesPerson,
                'mbl_no'         => $mblNo,
                'hbl_no'         => $hblNo,
                'pol'            => $pol,
                'pod'            => $pod,
                'etd'            => $etd,
                'eta'            => $eta,
                'total_amount'   => (float) $invoice->total_amount,
                'dr_amount'      => $drAmount,
                'cr_amount'      => $crAmount,
                'paid_amount'    => $totalPaid,
                'balance'        => $balance,
                'last_paid_date' => $lastPaidDate,
                'current'        => $current,
                'over1_30'       => $over1_30,
                'over31_60'      => $over31_60,
                'over61_90'      => $over61_90,
                'over90'         => $over90,
            ];
        }

        usort($results, fn($a, $b) => strcasecmp($a['partner_name'], $b['partner_name']));

        // Filter by partner_name and office for row-level actions (Download PDF / Reload Report)
        if ($filterPartner) {
            $results = array_filter($results, fn($r) => stripos($r['partner_name'], $filterPartner) !== false || strcasecmp($r['partner_name'], $filterPartner) === 0);
        }
        if ($filterOffice) {
            $results = array_filter($results, fn($r) => strcasecmp($r['office'], $filterOffice) === 0);
        }
        $results = array_values($results);

        $totalDR        = array_sum(array_column($results, 'dr_amount'));
        $totalCR        = array_sum(array_column($results, 'cr_amount'));
        $totalPaid      = array_sum(array_column($results, 'paid_amount'));
        $totalBalance   = array_sum(array_column($results, 'balance'));
        $totalCurrent   = array_sum(array_column($results, 'current'));
        $totalOver1_30  = array_sum(array_column($results, 'over1_30'));
        $totalOver31_60 = array_sum(array_column($results, 'over31_60'));
        $totalOver61_90 = array_sum(array_column($results, 'over61_90'));
        $totalOver90    = array_sum(array_column($results, 'over90'));

        $generatedList = $this->buildGeneratedDocumentList($results, $request);

        return response()->json([
            'success'        => true,
            'as_of_date'     => $asOfDate,
            'results'        => $results,
            'generated_list' => $generatedList,
            'options'        => [
                'show_aging'         => $showAging,
                'show_payment'       => $showPayment,
                'dc_format'          => $dcFormat,
                'acct_mode'          => $acctMode,
                'show_etd'           => $showEtd,
                'show_eta'           => $showEta,
                'group_department'   => $groupDepartment,
                'show_credit_limit'  => $showCreditLimit,
            ],
            'summary'        => [
                'total_dr'        => $totalDR,
                'total_cr'        => $totalCR,
                'total_paid'      => $totalPaid,
                'total_balance'   => $totalBalance,
                'count'           => count($results),
                'total_current'   => $totalCurrent,
                'total_over1_30'  => $totalOver1_30,
                'total_over31_60' => $totalOver31_60,
                'total_over61_90' => $totalOver61_90,
                'total_over90'    => $totalOver90,
            ],
        ]);
    }

    private function buildGeneratedDocumentList(array $results, Request $request)
    {
        $grouped = [];
        foreach ($results as $r) {
            $key = $r['partner_name'] . '|' . $r['office'] . '|' . $r['type'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'report_date'        => $request->as_of_date,
                    'document_id'        => '',
                    'operation'          => $r['sales_person'],
                    'partner'            => $r['partner_name'],
                    'data_type'          => 'Post Date',
                    'period'             => 'All',
                    'invoice_office'     => $r['office'],
                    'transaction_type'   => [],
                    'payment_status'     => 'Open',
                    'invoice_count'      => 0,
                    'total_amounts'      => [],
                    'balance_amounts'    => [],
                    'amount_confirmed'   => false,
                ];
            }
            $g = &$grouped[$key];
            $g['invoice_count']++;
            if (!in_array($r['type'], $g['transaction_type'])) {
                $g['transaction_type'][] = $r['type'];
            }
            $curr = $r['currency'];
            if (!isset($g['total_amounts'][$curr])) {
                $g['total_amounts'][$curr] = 0;
                $g['balance_amounts'][$curr] = 0;
            }
            $g['total_amounts'][$curr]  += $r['total_amount'];
            $g['balance_amounts'][$curr] += $r['balance'];
            if ($r['balance'] > 0.01) {
                $g['payment_status'] = 'Open';
            }
        }

        $list = array_values($grouped);
        $asOfStr = date('ymd', strtotime($request->as_of_date ?? now()));
        foreach ($list as $idx => &$item) {
            $item['document_id'] = 'DOC-' . $asOfStr . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $item['transaction_type'] = implode(', ', $item['transaction_type']);
            $item['total_amount_str'] = '';
            $item['balance_str'] = '';
            foreach ($item['total_amounts'] as $curr => $amt) {
                $item['total_amount_str'] .= ($item['total_amount_str'] ? ', ' : '') . $curr . ' ' . number_format($amt, 2);
            }
            foreach ($item['balance_amounts'] as $curr => $amt) {
                $item['balance_str'] .= ($item['balance_str'] ? ', ' : '') . $curr . ' ' . number_format($amt, 2);
            }
        }
        unset($item);

        return $list;
    }

    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.agent-local-statement-print', [
            'results'      => $data['results'] ?? [],
            'generatedList'=> $data['generated_list'] ?? [],
            'summary'      => $data['summary'] ?? [],
            'asOfDate'     => $request->as_of_date ?? date('Y-m-d'),
            'showAging'    => $request->show_aging ?? '0',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data    = $this->view($request)->getData(true);
        $results = $data['results'] ?? [];
        $summary = $data['summary'] ?? [];

        $filename = 'agent-local-statement-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($results, $summary, $request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Agent / Local Statement']);
            fputcsv($handle, ['As of Date', $request->as_of_date ?? '']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Invoice No', 'Invoice Date', 'Due Date', 'Partner', 'Office', 'Shipment Mode',
                'Cur.', 'Type', 'MB/L No.', 'HB/L No.', 'POL', 'POD',
                'DR/AR (+)', 'CR/AP (-)', 'Paid Amount', 'Balance', 'Last Paid Date',
            ]);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['invoice_no'], $row['invoice_date'], $row['due_date'],
                    $row['partner_name'], $row['office'], $row['shipment_mode'] ?? '--', $row['currency'], $row['type'],
                    $row['mbl_no'], $row['hbl_no'], $row['pol'], $row['pod'],
                    number_format($row['dr_amount'], 2),
                    number_format($row['cr_amount'], 2),
                    number_format($row['paid_amount'], 2),
                    number_format($row['balance'], 2),
                    $row['last_paid_date'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL', '', '', '', '', '', '', '', '', '', '',
                number_format($summary['total_dr'] ?? 0, 2),
                number_format($summary['total_cr'] ?? 0, 2),
                number_format($summary['total_paid'] ?? 0, 2),
                number_format($summary['total_balance'] ?? 0, 2),
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
