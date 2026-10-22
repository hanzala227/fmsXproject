<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\TradePartner;
use App\Models\Port;
use App\Models\Currency;
use App\Models\Office;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    private function getStatuses()
    {
        return [
            ['label' => 'Draft', 'color' => '#888', 'icon' => 'fa-check-circle'],
            ['label' => 'Sent', 'color' => '#3498db', 'icon' => 'fa-circle'],
            ['label' => 'Pending', 'color' => '#f1c40f', 'icon' => 'fa-circle'],
            ['label' => 'Won', 'color' => '#1abc9c', 'icon' => 'fa-circle'],
            ['label' => 'Lost', 'color' => '#e74c3c', 'icon' => 'fa-circle'],
            ['label' => 'Expired', 'color' => '#e74c3c', 'icon' => 'fa-circle'],
            ['label' => 'Cancelled', 'color' => '#e85a5a', 'icon' => 'fa-circle'],
            ['label' => 'Ghosted', 'color' => '#e85a5a', 'icon' => 'fa-circle'],
        ];
    }

    private function getFormData()
    {
        $customers = TradePartner::where('type', 'CLIENT')->orderBy('name')->get(['id', 'name', 'type']);
        $ports = Port::orderBy('name')->latest()->take(10)->get(['id', 'name']);
        $currencies = Currency::orderBy('code')->get(['id', 'code', 'name']);
        $carriers = TradePartner::where('type', 'CARRIER')->orderBy('name')->get(['id', 'name']);
        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $salesPersons = $users;
        $agents = TradePartner::where('type', 'AGENT')->orderBy('name')->get(['id', 'name']);
        $schedules = Schedule::orderBy('schedule_no')->get(['id', 'schedule_no', 'vessel_name', 'voyage', 'etd', 'eta']);
        $statuses = $this->getStatuses();
        $statusColors = array_combine(array_column($statuses, 'label'), array_column($statuses, 'color'));

        return compact('customers', 'ports', 'currencies', 'carriers', 'offices', 'users', 'salesPersons', 'agents', 'schedules', 'statuses', 'statusColors');
    }

    private function getServiceTermParts($serviceTerm)
    {
        if (!$serviceTerm) return ['CY', 'CY'];
        $parts = explode('~', $serviceTerm);
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function buildChargeJson($items, $type)
    {
        if ($type === 'AR') {
            return $items->where('type', 'AR')->values()->map(function ($item) {
                $remark = json_decode($item->remark, true) ?: [];
                return [
                    'selected' => false,
                    'pol' => (string)($remark['pol'] ?? ''),
                    'pod' => (string)($remark['pod'] ?? ''),
                    'carrier' => (string)($item->vendor_id ?? ''),
                    'currency_id' => (string)($item->currency_id ?? ''),
                    'rate_20gp' => (string)($remark['rate_20gp'] ?? ''),
                    'rate_40gp' => (string)($remark['rate_40gp'] ?? ''),
                    'rate_40hc' => (string)($remark['rate_40hc'] ?? ''),
                ];
            })->toJson();
        }

        return $items->where('type', 'DC_NOTE')->values()->map(function ($item) {
            $remark = json_decode($item->remark, true) ?: [];
            return [
                'show' => true,
                'freight_code' => (string)($item->charge_code ?? ''),
                'unit' => (string)($item->unit ?? ''),
                'currency_id' => (string)($item->currency_id ?? ''),
                'all_qty' => (string)($remark['all_qty'] ?? '1'),
                'all_rate' => (string)($remark['all_rate'] ?? ''),
                'separate_qty' => (string)($remark['separate_qty'] ?? '1'),
                'separate_rate' => (string)($remark['separate_rate'] ?? ''),
                'rate_20gp_qty' => (string)($remark['rate_20gp_qty'] ?? '1'),
                'rate_20gp' => (string)($remark['rate_20gp'] ?? ''),
                'rate_40gp_qty' => (string)($remark['rate_40gp_qty'] ?? '1'),
                'rate_40gp' => (string)($remark['rate_40gp'] ?? ''),
                'rate_40hc_qty' => (string)($remark['rate_40hc_qty'] ?? '1'),
                'rate_40hc' => (string)($remark['rate_40hc'] ?? ''),
                'remark' => (string)($remark['row_remark'] ?? ''),
            ];
        })->toJson();
    }

    public function create(Request $request)
    {
        extract($this->getFormData());

        $isEdit = false;
        $isCopy = false;
        $q = null;
        $freightsJson = '[]';
        $destJson = '[]';
        $serviceTermParts = ['', ''];

        if ($request->filled('copy')) {
            $q = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'items.currency', 'agent', 'carrier', 'office', 'createdBy', 'op', 'schedule', 'documents'])->find($request->copy);
            if ($q) {
                $isCopy = true;
                $q->quote_no = null;
                $freightsJson = $this->buildChargeJson($q->items, 'AR');
                $destJson = $this->buildChargeJson($q->items, 'DC_NOTE');
                $serviceTermParts = $this->getServiceTermParts($q->service_term);
            }
        }

        return view('sales.quotation.create', compact(
            'customers', 'ports', 'currencies', 'carriers', 'offices',
            'users', 'salesPersons', 'agents', 'schedules', 'statuses', 'statusColors',
            'isEdit', 'isCopy', 'q', 'freightsJson', 'destJson', 'serviceTermParts'
        ));
    }

    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'items.currency', 'agent', 'carrier', 'office', 'createdBy', 'op', 'schedule'])
            ->orderBy('created_at', 'desc');

        $moduleFilter = $request->input('module') 
                     ?? $request->input('transport_mode') 
                     ?? $request->input('shipping_type') 
                     ?? $request->input('type');

        if ($moduleFilter) {
            $query->forModule($moduleFilter);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('quote_no', 'like', "%{$s}%")
                  ->orWhere('commodity', 'like', "%{$s}%")
                  ->orWhere('transport_mode', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%")
                  ->orWhere('service_term', 'like', "%{$s}%")
                  ->orWhere('liner_code', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('agent', fn($aq) => $aq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('carrier', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('pol', fn($pq) => $pq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('pod', fn($pq) => $pq->where('name', 'like', "%{$s}%"));
            });
        }
        if ($request->filled('quote_no')) $query->where('quote_no', 'like', '%'.$request->quote_no.'%');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer')) $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%'.$request->customer.'%'));
        if ($request->filled('agent')) $query->whereHas('agent', fn($q) => $q->where('name', 'like', '%'.$request->agent.'%'));
        if ($request->filled('term')) $query->where('service_term', 'like', '%'.$request->term.'%');
        if ($request->filled('pol')) $query->whereHas('pol', fn($q) => $q->where('name', 'like', '%'.$request->pol.'%'));
        if ($request->filled('pod')) $query->whereHas('pod', fn($q) => $q->where('name', 'like', '%'.$request->pod.'%'));
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);

        if ($request->export === 'csv') {
            $rows = $query->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="quotations.csv"',
            ];
            $callback = function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Quote No.', 'Date', 'Status', 'Customer', 'Agent', 'Service Term', 'Shipping Type', 'POL', 'POD', 'Commodity', 'Carrier', 'Ship Mode']);
                foreach ($rows as $q) {
                    fputcsv($handle, [
                        $q->quote_no, $q->created_at?->format('Y-m-d'), $q->status,
                        $q->customer?->name, $q->agent?->name, $q->service_term,
                        $q->transport_mode, $q->pol?->name, $q->pod?->name,
                        $q->commodity, $q->carrier?->name, $q->ship_mode,
                    ]);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        if ($request->is('api/*')) {
            $quotations = $query->get()->map(function ($q) {
                return [
                    'id' => $q->id,
                    'quote_no' => $q->quote_no,
                    'expiry_date' => $q->expiry_date ? $q->expiry_date->format('Y-m-d') : null,
                    'status' => $q->status,
                    'created_at' => $q->created_at ? $q->created_at->format('Y-m-d') : null,
                    'customer' => $q->customer ? ['id' => $q->customer->id, 'name' => $q->customer->name] : null,
                    'pol_name' => $q->pol?->name,
                    'pod_name' => $q->pod?->name,
                    'commodity' => $q->commodity,
                    'service_term' => $q->service_term,
                    'items' => $q->items->map(fn($item) => [
                    'type' => $item->type, 'rate' => $item->rate,
                    'currency_code' => $item->currency?->code, 'unit' => $item->unit,
                    ]),
                ];
            });
            return response()->json($quotations);
        }

        $quotations = $query->paginate(25);
        $statusColors = array_combine(array_column($this->getStatuses(), 'label'), array_column($this->getStatuses(), 'color'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('sales.quotation.partials.list-rows', compact('quotations', 'statusColors'))->render(),
                'pagination' => (string) $quotations->withQueryString()->links(),
                'first' => $quotations->firstItem() ?? 0,
                'last' => $quotations->lastItem() ?? 0,
                'total' => $quotations->total(),
            ]);
        }

        return view('sales.quotation.list', compact('quotations', 'statusColors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:trade_partners,id',
            'shipping_type' => 'nullable|string',
            'quote_no' => 'nullable|string|max:50',
            'valid_date' => 'nullable|date',
            'create_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'agent_id' => 'nullable|exists:trade_partners,id',
            'ship_mode' => 'nullable|string|max:20',
            'service_term_origin' => 'nullable|string|max:10',
            'service_term_dest' => 'nullable|string|max:10',
            'incoterms_id' => 'nullable|string|max:50',
            'country_of_origin' => 'nullable|string|max:100',
            'sales_person_id' => 'nullable|exists:users,id',
            'op_id' => 'nullable|exists:users,id',
            'booking_no' => 'nullable|string|max:50',
            'po_no' => 'nullable|string|max:50',
            'commodity' => 'nullable|string|max:255',
            'hts_code' => 'nullable|string|max:50',
            'pkg_qty' => 'nullable|numeric',
            'pkg_unit' => 'nullable|string|max:20',
            'weight_kg' => 'nullable|numeric',
            'weight_lb' => 'nullable|numeric',
            'volume_cbm' => 'nullable|numeric',
            'volume_cft' => 'nullable|numeric',
            'description' => 'nullable|string',
            'remark' => 'nullable|string',
            'quotation_remark' => 'nullable|string',
            'internal_remark' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'via' => 'nullable|string|max:100',
            'tt' => 'nullable|string|max:50',
            'departure' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'liner_code' => 'nullable|string|max:50',
            'final_destination' => 'nullable|string|max:100',
            'place_of_receipt' => 'nullable|string|max:100',
            'place_of_delivery' => 'nullable|string|max:100',
            'schedule_id' => 'nullable|exists:schedules,id',
            'pol_id' => 'nullable|exists:ports,id',
            'pod_id' => 'nullable|exists:ports,id',
            'freight_rows' => 'nullable|string',
            'dest_rows' => 'nullable|string',
        ]);

        $data = $this->mapFormToDb($validated);

        if (empty($data['quote_no'])) {
            $data['quote_no'] = 'QT-' . date('ym') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        }
        if (empty($data['status'])) {
            $data['status'] = 'Draft';
        }

        $data['created_by_id'] = auth()->id();

        $quote = Quotation::create($data);

        $this->syncCharges($quote, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Quotation created successfully.', 'id' => $quote->id]);
        }

        return redirect()->route('sales.quotations.edit', $quote->id);
    }

    public function edit($id)
    {
        $q = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'items.currency', 'agent', 'carrier', 'office', 'createdBy', 'op', 'schedule', 'documents'])->findOrFail($id);

        extract($this->getFormData());

        $isEdit = true;
        $isCopy = false;
        $freightsJson = $this->buildChargeJson($q->items, 'AR');
        $destJson = $this->buildChargeJson($q->items, 'DC_NOTE');
        $serviceTermParts = $this->getServiceTermParts($q->service_term);

        return view('sales.quotation.create', compact(
            'q', 'customers', 'ports', 'currencies', 'carriers', 'offices',
            'users', 'salesPersons', 'agents', 'schedules', 'statuses', 'statusColors',
            'isEdit', 'isCopy', 'freightsJson', 'destJson', 'serviceTermParts'
        ));
    }

    public function update(Request $request, $id)
    {
        $quote = Quotation::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:trade_partners,id',
            'shipping_type' => 'nullable|string',
            'quote_no' => 'nullable|string|max:50',
            'valid_date' => 'nullable|date',
            'create_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'agent_id' => 'nullable|exists:trade_partners,id',
            'ship_mode' => 'nullable|string|max:20',
            'service_term_origin' => 'nullable|string|max:10',
            'service_term_dest' => 'nullable|string|max:10',
            'incoterms_id' => 'nullable|string|max:50',
            'country_of_origin' => 'nullable|string|max:100',
            'sales_person_id' => 'nullable|exists:users,id',
            'op_id' => 'nullable|exists:users,id',
            'booking_no' => 'nullable|string|max:50',
            'po_no' => 'nullable|string|max:50',
            'commodity' => 'nullable|string|max:255',
            'hts_code' => 'nullable|string|max:50',
            'pkg_qty' => 'nullable|numeric',
            'pkg_unit' => 'nullable|string|max:20',
            'weight_kg' => 'nullable|numeric',
            'weight_lb' => 'nullable|numeric',
            'volume_cbm' => 'nullable|numeric',
            'volume_cft' => 'nullable|numeric',
            'description' => 'nullable|string',
            'remark' => 'nullable|string',
            'quotation_remark' => 'nullable|string',
            'internal_remark' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'carrier_id' => 'nullable|exists:trade_partners,id',
            'via' => 'nullable|string|max:100',
            'tt' => 'nullable|string|max:50',
            'departure' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'liner_code' => 'nullable|string|max:50',
            'final_destination' => 'nullable|string|max:100',
            'place_of_receipt' => 'nullable|string|max:100',
            'place_of_delivery' => 'nullable|string|max:100',
            'schedule_id' => 'nullable|exists:schedules,id',
            'pol_id' => 'nullable|exists:ports,id',
            'pod_id' => 'nullable|exists:ports,id',
            'freight_rows' => 'nullable|string',
            'dest_rows' => 'nullable|string',
        ]);

        $data = $this->mapFormToDb($validated);
        if (empty($data['quote_no']) && !empty($quote->quote_no)) {
            $data['quote_no'] = $quote->quote_no;
        }
        $quote->update($data);

        $this->syncCharges($quote, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Quotation updated successfully.', 'id' => $quote->id]);
        }

        return redirect()->back()->with('success', 'Quotation updated successfully.');
    }

    public function destroy($id)
    {
        $quote = Quotation::findOrFail($id);
        $quote->delete();

        if (request()->ajax() || request()->wantsJson() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Quotation deleted successfully.']);
        }

        return redirect()->route('sales.quotations.list')->with('success', 'Quotation deleted successfully.');
    }

    private function mapFormToDb(array $validated): array
    {
        $data = [];

        $data['customer_id'] = $validated['customer_id'] ?? null;
        $data['quote_no'] = $validated['quote_no'] ?? null;
        $data['office_id'] = $validated['office_id'] ?? null;
        $data['agent_id'] = $validated['agent_id'] ?? null;
        $data['ship_mode'] = $validated['ship_mode'] ?? null;
        $data['incoterms_id'] = $validated['incoterms_id'] ?? null;
        $data['country_of_origin'] = $validated['country_of_origin'] ?? null;
        $data['sales_person_id'] = $validated['sales_person_id'] ?? null;
        $data['op_id'] = $validated['op_id'] ?? null;
        $data['booking_no'] = $validated['booking_no'] ?? null;
        $data['po_no'] = $validated['po_no'] ?? null;
        $data['commodity'] = $validated['commodity'] ?? null;
        $data['hts_code'] = $validated['hts_code'] ?? null;
        $data['pkg_qty'] = $validated['pkg_qty'] ?? null;
        $data['pkg_unit'] = $validated['pkg_unit'] ?? null;
        $data['weight_kg'] = $validated['weight_kg'] ?? null;
        $data['weight_lb'] = $validated['weight_lb'] ?? null;
        $data['volume_cbm'] = $validated['volume_cbm'] ?? null;
        $data['volume_cft'] = $validated['volume_cft'] ?? null;
        $data['description'] = $validated['description'] ?? null;
        $data['status'] = $validated['status'] ?? 'Draft';
        $data['internal_remark'] = $validated['internal_remark'] ?? null;
        $data['carrier_id'] = $validated['carrier_id'] ?? null;
        $data['via'] = $validated['via'] ?? null;
        $data['tt'] = $validated['tt'] ?? null;
        $data['departure'] = $validated['departure'] ?? null;
        $data['destination'] = $validated['destination'] ?? null;
        $data['liner_code'] = $validated['liner_code'] ?? null;
        $data['final_destination'] = $validated['final_destination'] ?? null;
        $data['place_of_receipt'] = $validated['place_of_receipt'] ?? null;
        $data['place_of_delivery'] = $validated['place_of_delivery'] ?? null;
        $data['schedule_id'] = $validated['schedule_id'] ?? null;
        $data['pol_id'] = $validated['pol_id'] ?? null;
        $data['pod_id'] = $validated['pod_id'] ?? null;

        $shippingType = $validated['shipping_type'] ?? null;
        $data['transport_mode'] = $shippingType;
        $data['shipping_type'] = $shippingType;

        $createDate = $validated['create_date'] ?? null;
        $data['quote_date'] = $createDate;
        $data['create_date'] = $createDate;

        $validDate = $validated['valid_date'] ?? null;
        $data['expiry_date'] = $validDate;
        $data['valid_date'] = $validDate;

        $remarkVal = $validated['remark'] ?? $validated['quotation_remark'] ?? null;
        $data['quotation_remark'] = $remarkVal;
        $data['remark'] = $remarkVal;

        $origin = $validated['service_term_origin'] ?? '';
        $dest = $validated['service_term_dest'] ?? '';
        $data['service_term'] = ($origin && $dest) ? $origin . '~' . $dest : ($origin ?: $dest);
        $data['service_term_origin'] = $origin ?: null;
        $data['service_term_dest'] = $dest ?: null;

        return $data;
    }

    private function syncCharges(Quotation $quote, Request $request)
    {
        $rawFreight = $request->input('freight_rows');
        $arRows = is_array($rawFreight) ? $rawFreight : (json_decode($rawFreight ?? '[]', true) ?: []);

        $rawDest = $request->input('dest_rows');
        $dcRows = is_array($rawDest) ? $rawDest : (json_decode($rawDest ?? '[]', true) ?: []);

        $quote->items()->where('type', 'AR')->delete();
        $quote->items()->where('type', 'DC_NOTE')->delete();

        foreach ($arRows as $row) {
            $hasContent = !empty($row['pol']) || !empty($row['pod']) || !empty($row['carrier']) || 
                          !empty($row['currency_id']) || !empty($row['rate_20gp']) || 
                          !empty($row['rate_40gp']) || !empty($row['rate_40hc']);
            if (!$hasContent) {
                continue;
            }
            $rate = (float)($row['rate_20gp'] ?? 0) + (float)($row['rate_40gp'] ?? 0) + (float)($row['rate_40hc'] ?? 0);
            $amount = $rate;

            $quote->items()->create([
                'type' => 'AR',
                'charge_code' => 'FREIGHT',
                'charge_name' => 'Freight',
                'currency_id' => !empty($row['currency_id']) ? $row['currency_id'] : null,
                'qty' => 1,
                'unit' => 'SET',
                'rate' => $rate,
                'amount' => $amount,
                'vendor_id' => !empty($row['carrier']) ? $row['carrier'] : null,
                'remark' => json_encode([
                    'pol' => $row['pol'] ?? '',
                    'pod' => $row['pod'] ?? '',
                    'rate_20gp' => $row['rate_20gp'] ?? '',
                    'rate_40gp' => $row['rate_40gp'] ?? '',
                    'rate_40hc' => $row['rate_40hc'] ?? '',
                ]),
            ]);
        }

        foreach ($dcRows as $row) {
            $hasContent = !empty($row['freight_code']) || !empty($row['unit']) || !empty($row['currency_id']) ||
                          !empty($row['all_rate']) || !empty($row['separate_rate']) || !empty($row['rate_20gp']) ||
                          !empty($row['rate_40gp']) || !empty($row['rate_40hc']) || !empty($row['remark']);
            if (!$hasContent) {
                continue;
            }
            $allAmt = (float)($row['all_rate'] ?? 0) * (float)($row['all_qty'] ?? 1);
            $sepAmt = (float)($row['separate_rate'] ?? 0) * (float)($row['separate_qty'] ?? 1);
            $g20Amt = (float)($row['rate_20gp'] ?? 0) * (float)($row['rate_20gp_qty'] ?? 1);
            $g40Amt = (float)($row['rate_40gp'] ?? 0) * (float)($row['rate_40gp_qty'] ?? 1);
            $hcAmt = (float)($row['rate_40hc'] ?? 0) * (float)($row['rate_40hc_qty'] ?? 1);
            $total = $allAmt + $sepAmt + $g20Amt + $g40Amt + $hcAmt;

            $chargeName = match($row['freight_code'] ?? '') {
                'THC' => 'THC', 'DOC' => 'Documentation', 'CUSTOMS' => 'Customs Clearance',
                'CLEANING' => 'Cleaning', 'SEAL' => 'Seal Fee', 'CHASSIS' => 'Chassis',
                'STORAGE' => 'Storage', default => ($row['freight_code'] ?? '') ?: 'Other',
            };

            $quote->items()->create([
                'type' => 'DC_NOTE',
                'charge_code' => $row['freight_code'] ?? '',
                'charge_name' => $chargeName,
                'currency_id' => !empty($row['currency_id']) ? $row['currency_id'] : null,
                'qty' => 1,
                'unit' => $row['unit'] ?? '',
                'rate' => $total,
                'amount' => $total,
                'remark' => json_encode([
                    'all_qty' => $row['all_qty'] ?? '', 'all_rate' => $row['all_rate'] ?? '',
                    'separate_qty' => $row['separate_qty'] ?? '', 'separate_rate' => $row['separate_rate'] ?? '',
                    'rate_20gp_qty' => $row['rate_20gp_qty'] ?? '', 'rate_20gp' => $row['rate_20gp'] ?? '',
                    'rate_40gp_qty' => $row['rate_40gp_qty'] ?? '', 'rate_40gp' => $row['rate_40gp'] ?? '',
                    'rate_40hc_qty' => $row['rate_40hc_qty'] ?? '', 'rate_40hc' => $row['rate_40hc'] ?? '',
                    'row_remark' => $row['remark'] ?? '',
                ]),
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No quotations selected.']);
        }
        Quotation::whereIn('id', $ids)->delete();
        return response()->json(['success' => true, 'message' => 'Quotations deleted successfully.']);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status');
        if (empty($ids) || !$status) {
            return response()->json(['success' => false, 'message' => 'Missing selection or status.']);
        }
        Quotation::whereIn('id', $ids)->update(['status' => $status]);
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function updateSingleStatus(Request $request, $id)
    {
        $quote = Quotation::findOrFail($id);
        $quote->status = $request->input('status', 'Draft');
        $quote->save();
        return response()->json(['success' => true, 'message' => 'Status updated to ' . $quote->status . '.']);
    }

    public function uploadDocument(Request $request, $quotationId)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('quotations/documents', 'public');

        $doc = Document::create([
            'documentable_type' => Quotation::class,
            'documentable_id' => $quotationId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'document' => $doc]);
    }

    public function deleteDocument($id)
    {
        $doc = Document::findOrFail($id);
        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
        return response()->json(['success' => true]);
    }

    public function downloadDocument($id)
    {
        $doc = Document::findOrFail($id);
        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }
}
