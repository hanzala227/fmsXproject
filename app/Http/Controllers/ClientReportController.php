<?php

namespace App\Http\Controllers;

use App\Models\AirExport;
use App\Models\AirImport;
use App\Models\Invoice;
use App\Models\OceanExport;
use App\Models\OceanImport;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
| Routes (auth middleware ke andar):
|   Route::get('/reports',     [ClientReportController::class, 'index'])->name('cp.reports.index');
|   Route::get('/reports/pdf', [ClientReportController::class, 'exportPdf'])->name('cp.reports.pdf');
|
| Install:  composer require barryvdh/laravel-dompdf
*/
class ClientReportController extends Controller
{
    /**
     * Page aur PDF dono isi se data lete hain.
     */
    private function reportData(Request $request, int $limit): array
    {
        $loggedInUser = auth()->user();

        // Agar trade_partner_id khali ho to "where dm_customer_id IS NULL" ban jata
        // aur doosron ka data aa sakta tha, isliye pehle hi rok dete hain.
        abort_if(!$loggedInUser, 403);
        abort_if(!$loggedInUser->company_id, 403);
        abort_if(!$loggedInUser->trade_partner_id, 403);

        $companyId  = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;
        $reportType = $request->input('report_type');
        $status     = strtolower((string) $request->input('status'));

        /* ================= Shipments ================= */

        $allShipments = collect();

        $sources = [
            'Air Import'   => AirImport::class,
            'Air Export'   => AirExport::class,
            'Ocean Import' => OceanImport::class,
            'Ocean Export' => OceanExport::class,
        ];

        foreach ($sources as $label => $model) {
            $rows = $model::where('company_id', $companyId)
                ->where(function ($q) use ($customerId) {
                    $q->where('dm_customer_id', $customerId)
                      ->orWhere('dm_shipper_id', $customerId)
                      ->orWhere('dm_consignee_id', $customerId);
                })
                ->with(['vessel' => function($q) { $q->select('id', 'name'); }])
                ->get();

            foreach ($rows as $shipment) {
                $shipment->shipment_type = $label;
                if (empty($shipment->status)) {
                    $shipment->status = 'In Transit';
                }
                $allShipments->push($shipment);
            }
        }

        if ($request->filled('from_date')) {
            $from = $request->from_date;
            $allShipments = $allShipments->filter(fn ($s) => $s->created_at && $s->created_at->format('Y-m-d') >= $from);
        }

        if ($request->filled('to_date')) {
            $to = $request->to_date;
            $allShipments = $allShipments->filter(fn ($s) => $s->created_at && $s->created_at->format('Y-m-d') <= $to);
        }

        $transitSet   = ['in transit', 'transit', 'in_transit'];
        $deliveredSet = ['delivered', 'complete', 'completed'];
        $pendingSet   = ['pending', 'pending booking', 'pending_documents', 'pending documents'];

        if ($status) {
            $allShipments = $allShipments->filter(function ($s) use ($status, $transitSet) {
                $st = strtolower($s->status ?? '');
                return $status === 'in_transit' ? in_array($st, $transitSet) : $st === $status;
            });
        }

        $allShipments = $allShipments->values();

        $countIn = fn (array $set) => $allShipments
            ->filter(fn ($s) => in_array(strtolower($s->status ?? ''), $set))->count();

        $totalShipments = $allShipments->count();
        $inTransit      = $countIn($transitSet);
        $arrived        = $countIn(['arrived']);
        $delivered      = $countIn($deliveredSet);
        $pending        = $countIn($pendingSet);

        // Page / PDF ke liye simple rows (port ids ko port code mein badalte hain)
        $listed  = $allShipments->take($limit);
        $portIds = $listed->flatMap(function ($s) {
            $a = $s->getAttributes();
            return [$a['dep_port_id'] ?? $a['pol_id'] ?? null, $a['dst_port_id'] ?? $a['pod_id'] ?? null];
        })->filter()->unique()->values();

        $ports = $portIds->isEmpty()
            ? collect()
            : DB::table('ports')->whereIn('id', $portIds)->pluck('code', 'id');

        $fmt = fn ($v) => $v ? Carbon::parse($v)->format('d M Y') : null;

        $shipments = $listed->map(function ($s) use ($ports, $fmt) {
            $a    = $s->getAttributes();
            $orig = $a['dep_port_id'] ?? $a['pol_id'] ?? null;
            $dest = $a['dst_port_id'] ?? $a['pod_id'] ?? null;
            $vesselName = $s->vessel->name ?? null;

            return (object) [
                'file_no'     => $a['file_no'] ?? null,
                'type'        => $s->shipment_type,
                'reference'   => $a['mawb_no'] ?? $a['mbl_no'] ?? null,
                'carrier_ref' => $vesselName ?: ($a['flight_no'] ?? '-'),
                'voyage'      => $a['voyage'] ?? null,
                'origin'      => $orig ? ($ports[$orig] ?? $orig) : null,
                'destination' => $dest ? ($ports[$dest] ?? $dest) : null,
                'etd'         => $fmt($a['etd'] ?? null),
                'eta'         => $fmt($a['eta'] ?? null),
                'status'      => $s->status ?: 'In Transit',
            ];
        })->values();

        /* ================= Invoices ================= */

        $invoiceQuery = Invoice::where('company_id', $companyId)
            ->where('bill_to_id', $customerId)
            ->where('type', 'AR')
            ->whereNotIn('status', ['DRAFT', 'VOID'])   // client ko draft/void nahi dikhane
            ->whereNull('deleted_at');

        if ($request->filled('from_date')) {
            $invoiceQuery->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $invoiceQuery->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Invoices par sirf "paid" aur "overdue" filter lagta hai.
        // Shipment wale status (pending/in_transit/arrived/delivered) invoices par lagane se list khali ho jati thi.
        if ($status === 'paid') {
            $invoiceQuery->whereRaw('LOWER(status) = ?', ['paid']);
        }

        $isOverdue = fn ($i) => strtolower($i->status ?? '') !== 'paid'
            && $i->due_date
            && Carbon::parse($i->due_date)->lt(today())
            && (float) $i->balance_amount > 0;

        $invoices = $invoiceQuery->latest()->get();

        if ($status === 'overdue') {
            $invoices = $invoices->filter($isOverdue)->values();
        }

        $totalInvoices            = $invoices->count();
        $totalInvoiceAmount       = $invoices->sum('total_amount');
        $paidInvoiceAmount        = $invoices->sum('paid_amount');
        $outstandingInvoiceAmount = $invoices->sum('balance_amount');
        // Pehle overdue sirf status == 'overdue' se nikalta tha, jo aam taur par kabhi nahi hota. Ab due_date se.
        $overdueInvoiceAmount     = $invoices->filter($isOverdue)->sum('balance_amount');

        /* ================= Quotations ================= */

        $quotationQuery = Quotation::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'Draft');           // customer ko draft nahi dikhana

        if ($request->filled('from_date')) {
            $quotationQuery->whereDate('quote_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $quotationQuery->whereDate('quote_date', '<=', $request->to_date);
        }

        $quotations = $quotationQuery->latest()->get();

        return compact(
            'loggedInUser', 'allShipments', 'shipments',
            'totalShipments', 'inTransit', 'arrived', 'delivered', 'pending',
            'invoices', 'totalInvoices', 'totalInvoiceAmount', 'paidInvoiceAmount',
            'outstandingInvoiceAmount', 'overdueInvoiceAmount',
            'quotations', 'reportType'
        );
    }

    public function index(Request $request)
    {
        return view('client.report', $this->reportData($request, 100));
    }

    public function pdf(Request $request)
    {
        $data = $this->reportData($request, 1000);

        $pdf = Pdf::loadView('client.pdf', $data + [
            'filters'     => $request->only(['report_type', 'from_date', 'to_date', 'status']),
            'userName'    => auth()->user()->name,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('report-' . now()->format('Ymd-His') . '.pdf');
    }
}