<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\TradePartner;
use App\Models\Vessel;
use App\Models\Port;
use App\Models\Office;
use App\Models\User;
use App\Models\ServiceTerm;
use App\Models\OceanBooking;
use App\Models\Charge;
use App\Models\Document;
use App\Models\Currency;
use App\Models\ShipmentStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VesselScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with([
            'vessel', 'pol', 'pod', 'op', 'office',
            'carrier', 'overseaAgent', 'notify', 'forwardingAgent',
            'por', 'del', 'fdest', 'customer', 'actualShipper',
            'billTo', 'consignee', 'trucker', 'referredBy',
            'svcTermFrom', 'svcTermTo',
        ]);

        $this->applyFiltersToQuery($request, $query);

        $schedules = $query->latest()->paginate(20)->withQueryString();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $html = view('ocean-export.partials.vessel-schedule-list-rows', compact('schedules'))->render();
                $pagination = view('vendor.pagination.custom', ['paginator' => $schedules])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'first' => $schedules->firstItem() ?? 0,
                    'last' => $schedules->lastItem() ?? 0,
                    'total' => $schedules->total(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $users = User::all();

        return view('ocean-export.vessel-schedule-list', compact('schedules', 'users'));
    }

    protected function applyFiltersToQuery(Request $request, $query)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('schedule_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('vessel', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhere('carrier_bkg_no', 'like', "%{$search}%");
            });
        }

        $filters = [
            'filter_schedule_no' => 'schedule_no',
            'filter_customer'    => 'customer_id',
            'filter_office'      => 'office_id',
            'filter_vessel'      => 'vessel_id',
            'filter_voyage'      => 'voyage',
            'filter_etd'         => 'etd',
            'filter_eta'         => 'eta',
            'filter_pol'         => 'pol_id',
            'filter_pod'         => 'pod_id',
            'filter_fdest'       => 'fdest_id',
            'filter_por'         => 'por_id',
            'filter_del'         => 'del_id',
            'filter_carrier_bkg' => 'carrier_bkg_no',
            'filter_carrier'     => 'carrier_id',
            'filter_oversea_agent' => 'oversea_agent_id',
            'filter_fwd_agent'   => 'forwarding_agent_id',
            'filter_op'          => 'op_id',
            'filter_svc_from'    => 'svc_term_from_id',
            'filter_svc_to'      => 'svc_term_to_id',
            'filter_cargo_type'  => 'cargo_type',
            'filter_ship_mode'   => 'ship_mode',
        ];

        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $val = $request->get($param);
                if (str_contains($column, '_id')) {
                    $query->where($column, $val);
                } else {
                    $query->where($column, 'like', "%{$val}%");
                }
            }
        }
    }

    public function create(Request $request)
    {
        $schedule = null;
        if ($request->copy) {
            $schedule = Schedule::with([
                'vessel', 'pol', 'pod', 'op', 'office',
                'carrier', 'overseaAgent', 'notify', 'forwardingAgent',
                'por', 'del', 'fdest', 'customer', 'actualShipper',
                'billTo', 'consignee', 'trucker', 'referredBy',
                'svcTermFrom', 'svcTermTo',
            ])->find($request->copy);
        }
        return $this->formView($schedule);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        if (empty($validated['schedule_no']) || $validated['schedule_no'] === 'AUTO') {
            $validated['schedule_no'] = 'VS-' . now()->format('ymd') . '-' . str_pad(Schedule::max('id') + 1, 4, '0', STR_PAD_LEFT);
        }
        $schedule = Schedule::create($validated);

        $this->saveBookings($request, $schedule->id);
        $this->saveContainers($request, $schedule);
        $this->saveMemos($request, $schedule);

        return redirect()->route('vessel-schedules.edit', $schedule->id)
            ->with('success', 'Vessel Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        return $this->formView($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate($this->rules($schedule->id));
        $schedule->update($validated);

        $this->saveBookings($request, $schedule->id);
        $this->saveContainers($request, $schedule);
        $this->saveMemos($request, $schedule);

        return redirect()->route('vessel-schedules.edit', $schedule->id)
            ->with('success', 'Vessel Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('vessel-schedules.index')
            ->with('success', 'Vessel Schedule deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:schedules,id']);
        Schedule::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' schedule(s) deleted.']);
    }

    public function updateColor(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $schedule->update(['color' => $request->color]);
        return response()->json(['success' => true]);
    }

    protected function formView($schedule = null)
    {
        $vessels       = Vessel::all();
        $ports         = Port::all();
        $offices       = Office::where('is_active', true)->get();
        $users         = User::all();
        $serviceTerms  = ServiceTerm::all();
        $tradePartners = TradePartner::all();
        $carriers      = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        $customers     = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $truckers      = TradePartner::whereIn('type', ['TR', 'TRUCKER'])->get();
        $agents        = TradePartner::whereIn('type', ['PR', 'AGENT', 'FR'])->get();
        $currencies    = Currency::all();
        $loggedUser    = auth()->user();

        if ($carriers->isEmpty())  $carriers  = $tradePartners;
        if ($customers->isEmpty()) $customers = $tradePartners;
        if ($truckers->isEmpty())  $truckers  = $tradePartners;
        if ($agents->isEmpty())    $agents    = $tradePartners;

        return view('ocean-export.vessel-schedule', compact(
            'schedule', 'vessels', 'ports', 'offices', 'users', 'serviceTerms',
            'tradePartners', 'carriers', 'customers', 'truckers', 'agents', 'currencies', 'loggedUser'
        ));
    }

    protected function saveBookings(Request $request, $scheduleId = null)
    {
        if ($request->filled('bookings_json')) {
            $bookings = json_decode($request->bookings_json, true);
            if (is_array($bookings)) {
                $allowed = (new OceanBooking)->getFillable();
                foreach ($bookings as $data) {
                    $data = array_intersect_key($data, array_flip($allowed));
                    
                    // Handle empty foreign key fields
                    $foreignKeys = [
                        'sales_person_id', 'carrier_id', 'actual_shipper_id', 'customer_id', 
                        'bill_to_id', 'consignee_id', 'notify_id', 'trucker_id', 'office_id', 'op_id',
                        'por_id', 'pol_id', 'pod_id', 'del_id', 'fdest_id', 'svc_term_from_id', 'svc_term_to_id'
                    ];
                    
                    foreach ($foreignKeys as $key) {
                        if (array_key_exists($key, $data) && (empty($data[$key]) || !is_numeric($data[$key]))) {
                            $data[$key] = null;
                        }
                    }
                    
                    // Handle empty date fields (convert empty strings to null to prevent SQL date errors)
                    $dateFields = ['booking_date', 'etd', 'eta', 'final_eta', 'cargo_ready', 'por_etd', 'del_eta', 'wh_cutoff', 'doc_cutoff', 'port_cutoff', 'vgm_cutoff'];
                    foreach ($dateFields as $key) {
                        if (array_key_exists($key, $data) && ($data[$key] === '' || $data[$key] === null)) {
                            $data[$key] = null;
                        }
                    }
                    
                    // Ensure op_id has a valid value
                    $data['op_id'] = !empty($data['op_id']) ? (int)$data['op_id'] : auth()->id();
                    
                    if (!empty($data['booking_no'])) {
                        OceanBooking::create($data);
                    }
                }
            }
        }
    }

    protected function saveContainers(Request $request, Schedule $schedule)
    {
        if ($request->filled('containers_json')) {
            $containers = json_decode($request->containers_json, true);
            $schedule->containers_data = is_array($containers) ? $containers : [];
            $schedule->save();
        } else {
            $schedule->containers_data = [];
            $schedule->save();
        }
    }

    protected function saveMemos(Request $request, Schedule $schedule)
    {
        if ($request->filled('memos_json')) {
            $memos = json_decode($request->memos_json, true);
            $schedule->memos_data = is_array($memos) ? $memos : [];
            $schedule->save();
        } else {
            $schedule->memos_data = [];
            $schedule->save();
        }
    }

    protected function rules($id = null)
    {
        return [
            'schedule_no'          => 'nullable|string|max:255',
            'vessel_name'          => 'nullable|string|max:255',
            'voyage'               => 'nullable|string|max:255',
            'pol_name'             => 'nullable|string|max:255',
            'pod_name'             => 'nullable|string|max:255',
            'etd'                  => 'nullable|date',
            'eta'                  => 'nullable|date',
            'carrier_bkg_no'       => 'nullable|string|max:255',
            'shipping_agent'       => 'nullable|string|max:255',
            'office_id'            => 'nullable|exists:offices,id',
            'itn_no'               => 'nullable|string|max:255',
            'oversea_agent_id'     => 'nullable|exists:trade_partners,id',
            'bl_type'              => 'nullable|string|max:50',
            'notify_id'            => 'nullable|exists:trade_partners,id',
            'op_id'                => 'nullable|exists:users,id',
            'post_date'            => 'nullable|date',
            'forwarding_agent_id'  => 'nullable|exists:trade_partners,id',
            'vessel_id'            => 'nullable|exists:vessels,id',
            'pol_id'               => 'nullable|exists:ports,id',
            'pod_id'               => 'nullable|exists:ports,id',
            'fdest_id'             => 'nullable|exists:ports,id',
            'final_eta'            => 'nullable|date',
            'delivery_to_pier'     => 'nullable|string|max:255',
            'por_id'               => 'nullable|exists:ports,id',
            'del_id'               => 'nullable|exists:ports,id',
            'empty_pickup'         => 'nullable|string|max:255',
            'por_etd'              => 'nullable|date',
            'del_eta'              => 'nullable|date',
            'freight'              => 'nullable|string|max:50',
            'obl_type'             => 'nullable|string|max:50',
            'on_board_date'        => 'nullable|date',
            'ship_mode'            => 'nullable|string|max:50',
            'doc_cutoff'           => 'nullable|date',
            'svc_term_from_id'     => 'nullable|exists:service_terms,id',
            'svc_term_to_id'       => 'nullable|exists:service_terms,id',
            'port_cutoff'          => 'nullable|date',
            'rail_cutoff'          => 'nullable|date',
            'carrier_id'           => 'nullable|exists:trade_partners,id',
            'actual_shipper_id'    => 'nullable|exists:trade_partners,id',
            'customer_id'          => 'nullable|exists:trade_partners,id',
            'bill_to_id'           => 'nullable|exists:trade_partners,id',
            'consignee_id'         => 'nullable|exists:trade_partners,id',
            'trucker_id'           => 'nullable|exists:trade_partners,id',
            'referred_by_id'       => 'nullable|exists:trade_partners,id',
            'cargo_type'           => 'nullable|string|max:50',
            'cargo_pickup'         => 'nullable|string|max:255',
            'cargo_ready'          => 'nullable|date',
            'wh_cutoff'            => 'nullable|date',
            'vgm_cutoff'           => 'nullable|date',
        ];
    }

    // ======== CHARGE METHODS ========

    public function listCharges($scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $charges = $schedule->charges()->with(['currency', 'billTo', 'vendor'])->orderBy('id')->get();
        return response()->json(['success' => true, 'charges' => $charges]);
    }

    public function storeCharge(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $data = $request->validate([
            'type'         => 'nullable|string|max:10',
            'charge_code'  => 'nullable|string|max:50',
            'charge_name'  => 'nullable|string|max:255',
            'bill_to_id'   => 'nullable|exists:trade_partners,id',
            'vendor_id'    => 'nullable|exists:trade_partners,id',
            'pc'           => 'nullable|string|max:20',
            'qty'          => 'nullable|numeric',
            'unit'         => 'nullable|string|max:20',
            'currency_id'  => 'nullable|exists:currencies,id',
            'rate'         => 'nullable|numeric',
            'amount'       => 'nullable|numeric',
            'tax_percent'  => 'nullable|numeric',
            'tax_amount'   => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'remark'       => 'nullable|string|max:500',
        ]);
        $data['chargeable_type'] = get_class($schedule);
        $data['chargeable_id'] = $schedule->id;
        $charge = $schedule->charges()->create($data);
        $charge->load(['currency', 'billTo', 'vendor']);
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function updateCharge(Request $request, $chargeId)
    {
        $charge = Charge::findOrFail($chargeId);
        $data = $request->validate([
            'type'         => 'nullable|string|max:10',
            'charge_code'  => 'nullable|string|max:50',
            'charge_name'  => 'nullable|string|max:255',
            'bill_to_id'   => 'nullable|exists:trade_partners,id',
            'vendor_id'    => 'nullable|exists:trade_partners,id',
            'pc'           => 'nullable|string|max:20',
            'qty'          => 'nullable|numeric',
            'unit'         => 'nullable|string|max:20',
            'currency_id'  => 'nullable|exists:currencies,id',
            'rate'         => 'nullable|numeric',
            'amount'       => 'nullable|numeric',
            'tax_percent'  => 'nullable|numeric',
            'tax_amount'   => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'remark'       => 'nullable|string|max:500',
        ]);
        $charge->update($data);
        $charge->load(['currency', 'billTo', 'vendor']);
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function destroyCharge($chargeId)
    {
        $charge = Charge::findOrFail($chargeId);
        $charge->delete();
        return response()->json(['success' => true]);
    }

    public function deleteAllCharges($scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->charges()->delete();
        return response()->json(['success' => true]);
    }

    public function duplicateCharges(Request $request, $scheduleId)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:charges,id']);
        $schedule = Schedule::findOrFail($scheduleId);
        foreach ($request->ids as $id) {
            $charge = Charge::findOrFail($id);
            if ($charge->chargeable_id != $scheduleId) continue;
            $clone = $charge->replicate();
            $clone->invoice_no = null;
            $clone->invoice_date = null;
            $clone->is_invoiced = false;
            $clone->save();
        }
        return response()->json(['success' => true]);
    }

    public function bulkUpdateCurrency(Request $request, $scheduleId)
    {
        $request->validate(['currency' => 'required|string|max:10']);
        $currency = Currency::where('code', $request->currency)->first();
        if (!$currency) {
            return response()->json(['success' => false, 'message' => 'Currency not found.'], 404);
        }
        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->charges()->update(['currency_id' => $currency->id]);
        return response()->json(['success' => true]);
    }

    public function applyVatToAll(Request $request, $scheduleId)
    {
        $request->validate(['vat' => 'required|numeric|min:0|max:100']);
        $schedule = Schedule::findOrFail($scheduleId);
        $charges = $schedule->charges()->get();
        foreach ($charges as $charge) {
            $taxAmount = floatval($charge->amount) * (floatval($request->vat) / 100);
            $charge->update([
                'tax_percent' => $request->vat,
                'tax_amount' => $taxAmount,
                'total_amount' => floatval($charge->amount) + $taxAmount,
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function applyChargeTemplate(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $currency = Currency::where('code', 'USD')->first();
        $templates = [
            ['code' => 'OFT', 'name' => 'Ocean Freight', 'rate' => 1200, 'qty' => 1],
            ['code' => 'THC', 'name' => 'Terminal Handling Charge', 'rate' => 350, 'qty' => 1],
            ['code' => 'DOC', 'name' => 'Documentation Fee', 'rate' => 75, 'qty' => 1],
        ];
        $existingCodes = $schedule->charges()->pluck('charge_code')->toArray();
        foreach ($templates as $tc) {
            if (in_array($tc['code'], $existingCodes)) continue;
            $amount = $tc['rate'] * $tc['qty'];
            $schedule->charges()->create([
                'type' => 'AR',
                'charge_code' => $tc['code'],
                'charge_name' => $tc['name'],
                'pc' => 'COLLECT',
                'qty' => $tc['qty'],
                'unit' => 'UNIT',
                'currency_id' => $currency->id ?? null,
                'rate' => $tc['rate'],
                'amount' => $amount,
                'total_amount' => $amount,
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function createInvoiceFromCharges(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $charges = $schedule->charges()->where('is_invoiced', false)->get();
        if ($charges->count() == 0) {
            return response()->json(['success' => false, 'message' => 'No uninvoiced charges found.'], 400);
        }
        $invNo = 'INV-' . strtoupper(uniqid());
        foreach ($charges as $charge) {
            $charge->update([
                'is_invoiced' => true,
                'invoice_no' => $invNo,
                'invoice_date' => now(),
            ]);
        }
        return response()->json(['success' => true, 'invoice_no' => $invNo]);
    }

    public function exportChargesToExcel($scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $charges = $schedule->charges()->get();
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="charges-' . $scheduleId . '-' . now()->format('Y-m-d') . '.csv"',
        ];
        $callback = function () use ($charges) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type', 'Code', 'Name', 'P/C', 'Rate', 'Qty', 'Unit', 'Amount', 'Tax %', 'Tax Amt', 'Total', 'Invoice No', 'Remark']);
            foreach ($charges as $c) {
                fputcsv($file, [$c->type, $c->charge_code, $c->charge_name, $c->pc, $c->rate, $c->qty, $c->unit, $c->amount, $c->tax_percent, $c->tax_amount, $c->total_amount, $c->invoice_no, $c->remark]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function printCharges($scheduleId)
    {
        $schedule = Schedule::with('charges.currency', 'charges.billTo', 'vessel', 'pol', 'pod')->findOrFail($scheduleId);
        return view('ocean-export.print-charges', compact('schedule'));
    }

    // ======== DOCUMENT METHODS ========

    public function uploadDocument(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $request->validate([
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:255',
        ]);
        $file = $request->file('file');
        $path = $file->store('shipments/documents', 'public');
        $doc = Document::create([
            'documentable_type' => get_class($schedule),
            'documentable_id' => $schedule->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);
        $doc->load('uploader');
        return response()->json(['success' => true, 'document' => $doc]);
    }

    public function deleteDocument($documentId)
    {
        $doc = Document::findOrFail($documentId);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return response()->json(['success' => true]);
    }

    public function downloadDocument($documentId)
    {
        $doc = Document::findOrFail($documentId);
        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    // ======== STATUS METHODS ========

    public function getStatusLogs($scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        
        $logs = ShipmentStatusLog::where('shipment_type', get_class($schedule))
            ->where('shipment_id', $schedule->id)
            ->with('user')
            ->orderBy('event_time', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'status_code' => $log->status_code,
                    'status_name' => $log->status_name,
                    'details' => $log->details,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'event_time' => $log->event_time->format('Y-m-d H:i:s'),
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        return response()->json($logs);
    }

    public function saveStatus(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $data = $request->validate([
            'internal_message' => 'nullable|string',
            'op_id' => 'nullable|exists:users,id',
        ]);
        if (isset($data['op_id'])) {
            $schedule->op_id = $data['op_id'];
        }
        if (isset($data['internal_message'])) {
            $schedule->internal_message = $data['internal_message'];
        }
        $schedule->save();

        ShipmentStatusLog::create([
            'shipment_type' => get_class($schedule),
            'shipment_id' => $schedule->id,
            'status_code' => 'STATUS_UPDATE',
            'status_name' => 'Status Updated',
            'details' => $request->input('internal_message', 'Message saved'),
            'user_id' => auth()->id(),
            'event_time' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function exportCsv(Request $request)
    {
        $query = Schedule::with([
            'vessel', 'pol', 'pod', 'op', 'office',
            'carrier', 'overseaAgent', 'notify', 'forwardingAgent',
            'por', 'del', 'fdest', 'customer', 'actualShipper',
            'billTo', 'consignee', 'trucker', 'referredBy',
            'svcTermFrom', 'svcTermTo',
        ]);

        $this->applyFiltersToQuery($request, $query);

        $schedules = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vessel-schedules-' . now()->format('Y-m-d-His') . '.csv"',
        ];

        $callback = function() use ($schedules) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Schedule No.', 'Office', 'Vessel Name', 'Voyage', 'ETD', 'ETA',
                'Port of Loading', 'Port of Discharge', 'Final Destination',
                'Place of Receipt', 'Place of Delivery', 'Carrier Bkg. No.',
                'Carrier', 'Oversea Agent', 'Fwd. Agent', 'OP',
                'Svc From', 'Svc To', 'Cargo Type', 'Ship Mode', 'Customer', 'Post Date'
            ]);

            // CSV Rows
            foreach ($schedules as $s) {
                fputcsv($file, [
                    $s->schedule_no ?? 'VS-' . $s->id,
                    $s->office->code ?? '--',
                    $s->vessel->name ?? ($s->vessel_name ?? '--'),
                    $s->voyage ?? '--',
                    $s->etd ? $s->etd->format('m-d-Y') : '--',
                    $s->eta ? $s->eta->format('m-d-Y') : '--',
                    $s->pol->name ?? ($s->pol_name ?? '--'),
                    $s->pod->name ?? ($s->pod_name ?? '--'),
                    $s->fdest->name ?? '--',
                    $s->por->name ?? '--',
                    $s->del->name ?? '--',
                    $s->carrier_bkg_no ?? '--',
                    $s->carrier->name ?? '--',
                    $s->overseaAgent->name ?? '--',
                    $s->forwardingAgent->name ?? ($s->shipping_agent ?? '--'),
                    $s->op->name ?? '--',
                    $s->svcTermFrom->code ?? '--',
                    $s->svcTermTo->code ?? '--',
                    $s->cargo_type ?? '--',
                    $s->ship_mode ?? '--',
                    $s->customer->name ?? '--',
                    $s->post_date ? $s->post_date->format('m-d-Y') : '--',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
