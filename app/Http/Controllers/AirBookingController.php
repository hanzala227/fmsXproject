<?php

namespace App\Http\Controllers;

use App\Models\AirBooking;
use App\Models\TradePartner;
use App\Models\Port;
use App\Models\Office;
use Illuminate\Http\Request;

class AirBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = AirBooking::with([
            'customer', 'carrier', 'depPort', 'dstPort',
            'salesPerson', 'office', 'op', 'shipper', 'overseaAgent',
        ]);

        $this->applyFiltersToQuery($request, $query);

        if ($request->export === 'csv') {
            return $this->exportCsv($request, clone $query);
        }

        $bookings = $query->latest()->paginate(20);

        $users = \App\Models\User::orderBy('name')->get();
        $tradePartners = TradePartner::orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->orderBy('name')->get();

        if ($carriers->isEmpty()) $carriers = $tradePartners;

        return view('air-export.booking-list', compact('bookings', 'users', 'tradePartners', 'offices', 'carriers'));
    }

    protected function applyFiltersToQuery(Request $request, $query)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('carrier', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('depPort', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('dstPort', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $filters = [
            'filter_booking_no' => 'booking_no',
            'filter_carrier'    => 'carrier_id',
            'filter_customer'   => 'customer_id',
            'filter_office'     => 'office_id',
            'filter_flight_no'  => 'flight_no',
            'filter_dep_port'   => 'dep_port_id',
            'filter_dst_port'   => 'dst_port_id',
            'filter_status'     => 'status',
            'filter_op'         => 'op_id',
            'filter_sales'      => 'sales_person_id',
            'filter_shipper'    => 'shipper_id',
            'filter_oversea_agent' => 'oversea_agent_id',
            'filter_etd'        => 'etd',
            'filter_eta'        => 'eta',
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

    public function exportCsv(Request $request, $query = null)
    {
        if (!$query) {
            $query = AirBooking::with(['customer', 'carrier', 'depPort', 'dstPort', 'salesPerson', 'office', 'op']);
            $this->applyFiltersToQuery($request, $query);
        }

        $bookings = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-bookings-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Booking No.', 'Booking Date', 'Customer', 'Carrier',
                'Flight No.', 'Departure Port', 'Destination Port',
                'ETD', 'ETA', 'Status', 'Office', 'OP', 'Sales',
            ]);

            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->booking_no,
                    $b->booking_date ? $b->booking_date->format('Y-m-d') : '',
                    $b->customer->name ?? '',
                    $b->carrier->name ?? '',
                    $b->flight_no,
                    $b->depPort->name ?? '',
                    $b->dstPort->name ?? '',
                    $b->etd ? $b->etd->format('Y-m-d') : '',
                    $b->eta ? $b->eta->format('Y-m-d') : '',
                    $b->status,
                    $b->office->code ?? '',
                    $b->op->name ?? '',
                    $b->salesPerson->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id']);
        AirBooking::whereIn('id', $request->ids)->update(['is_blocked' => true]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' booking(s) blocked.']);
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id']);
        AirBooking::whereIn('id', $request->ids)->update(['is_blocked' => false]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' booking(s) unblocked.']);
    }

    public function create()
    {
        $offices = Office::where('is_active', true)->get();
        $tradePartners = TradePartner::all();
        $ports = Port::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $incoterms = \App\Models\Incoterm::all();
        $airExports = \App\Models\AirExport::whereNotNull('mawb_no')->select('id', 'mawb_no', 'file_no')->get();
        $nextBookingNo = 'ABE-' . date('ymd') . '-' . str_pad((\App\Models\AirBooking::withTrashed()->max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $currentUser = auth()->user();
        $cargoTypes = ['GENERAL CARGO','DANGEROUS GOODS','PERISHABLE','VALUABLE CARGO'];
        $shipTypes = ['NORMAL','CONSOL','EXPRESS'];
        $quotations = \App\Models\Quotation::with(['customer', 'pol', 'pod', 'salesPerson', 'items', 'carrier'])->forModule('Air Export')->latest()->get();
        return view('air-export.new-booking', compact('offices', 'tradePartners', 'ports', 'users', 'packageUnits', 'incoterms', 'airExports', 'nextBookingNo', 'currentUser', 'cargoTypes', 'shipTypes', 'quotations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_no'       => 'required|string|unique:air_bookings,booking_no',
            'booking_date'     => 'required|date',
            'customer_id'      => 'nullable|exists:trade_partners,id',
            'carrier_id'       => 'nullable|exists:trade_partners,id',
            'flight_no'        => 'nullable|string|max:255',
            'dep_port_id'      => 'nullable|exists:ports,id',
            'dst_port_id'      => 'nullable|exists:ports,id',
            'etd'              => 'nullable|date',
            'eta'              => 'nullable|date',
            'status'           => 'required|in:OPEN,CONFIRMED,CANCELLED,COMPLETED',
            'office_id'        => 'nullable|exists:offices,id',
            'sales_person_id'  => 'nullable|exists:users,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'shipper_id'       => 'nullable|exists:trade_partners,id',
            'incoterms_id'     => 'nullable|exists:incoterms,id',
            'cargo_type'       => 'nullable|string|max:100',
            'ship_type'        => 'nullable|string|max:100',
            'pkg_qty'          => 'nullable|numeric',
            'pkg_unit_id'      => 'nullable|exists:package_units,id',
            'gross_weight'     => 'nullable|numeric',
            'volume'           => 'nullable|numeric',
            'chargeable_weight'=> 'nullable|numeric',
            'wt_val_payment'   => 'nullable|string|max:10',
            'other_charges_payment' => 'nullable|string|max:10',
            'stackable'        => 'nullable|boolean',
            'handling_info'    => 'nullable|string',
            'pickup_delivery_instructions' => 'nullable|string',
            'op_id'            => 'nullable|exists:users,id',
            'mawb_reference'   => 'nullable|exists:air_exports,id',
        ]);

        if (!isset($validated['pkg_qty']) || $validated['pkg_qty'] === null) $validated['pkg_qty'] = 0;
        if (!isset($validated['gross_weight']) || $validated['gross_weight'] === null) $validated['gross_weight'] = 0;
        if (!isset($validated['volume']) || $validated['volume'] === null) $validated['volume'] = 0;
        if (!isset($validated['chargeable_weight']) || $validated['chargeable_weight'] === null) $validated['chargeable_weight'] = 0;

        AirBooking::create($validated);

        return redirect()->route('air-bookings.index')
            ->with('success', 'Air Export Booking created successfully.');
    }

    public function edit($id)
    {
        $booking = AirBooking::findOrFail($id);
        $offices = Office::where('is_active', true)->get();
        $tradePartners = TradePartner::all();
        $ports = Port::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $incoterms = \App\Models\Incoterm::all();
        $airExports = \App\Models\AirExport::whereNotNull('mawb_no')->select('id', 'mawb_no', 'file_no')->get();
        $nextBookingNo = 'ABE-' . date('ymd') . '-' . str_pad((\App\Models\AirBooking::withTrashed()->max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $currentUser = auth()->user();
        $quotations = \App\Models\Quotation::with(['customer', 'pol', 'pod', 'salesPerson', 'items', 'carrier'])->forModule('Air Export')->latest()->get();
        return view('air-export.new-booking', compact('booking', 'offices', 'tradePartners', 'ports', 'users', 'packageUnits', 'incoterms', 'airExports', 'nextBookingNo', 'currentUser', 'quotations'));
    }

    public function update(Request $request, $id)
    {
        $booking = AirBooking::findOrFail($id);
        
        $validated = $request->validate([
            'booking_no'       => 'required|string|unique:air_bookings,booking_no,' . $booking->id,
            'booking_date'     => 'required|date',
            'customer_id'      => 'nullable|exists:trade_partners,id',
            'carrier_id'       => 'nullable|exists:trade_partners,id',
            'flight_no'        => 'nullable|string|max:255',
            'dep_port_id'      => 'nullable|exists:ports,id',
            'dst_port_id'      => 'nullable|exists:ports,id',
            'etd'              => 'nullable|date',
            'eta'              => 'nullable|date',
            'status'           => 'required|in:OPEN,CONFIRMED,CANCELLED,COMPLETED',
            'office_id'        => 'nullable|exists:offices,id',
            'sales_person_id'  => 'nullable|exists:users,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'shipper_id'       => 'nullable|exists:trade_partners,id',
            'incoterms_id'     => 'nullable|exists:incoterms,id',
            'cargo_type'       => 'nullable|string|max:100',
            'ship_type'        => 'nullable|string|max:100',
            'pkg_qty'          => 'nullable|numeric',
            'pkg_unit_id'      => 'nullable|exists:package_units,id',
            'gross_weight'     => 'nullable|numeric',
            'volume'           => 'nullable|numeric',
            'chargeable_weight'=> 'nullable|numeric',
            'wt_val_payment'   => 'nullable|string|max:10',
            'other_charges_payment' => 'nullable|string|max:10',
            'stackable'        => 'nullable|boolean',
            'handling_info'    => 'nullable|string',
            'pickup_delivery_instructions' => 'nullable|string',
            'op_id'            => 'nullable|exists:users,id',
            'mawb_reference'   => 'nullable|exists:air_exports,id',
        ]);

        if (!isset($validated['pkg_qty']) || $validated['pkg_qty'] === null) $validated['pkg_qty'] = 0;
        if (!isset($validated['gross_weight']) || $validated['gross_weight'] === null) $validated['gross_weight'] = 0;
        if (!isset($validated['volume']) || $validated['volume'] === null) $validated['volume'] = 0;
        if (!isset($validated['chargeable_weight']) || $validated['chargeable_weight'] === null) $validated['chargeable_weight'] = 0;

        $booking->update($validated);

        return redirect()->route('air-bookings.index')
            ->with('success', 'Air Export Booking updated successfully.');
    }

    public function destroy($id)
    {
        $booking = AirBooking::findOrFail($id);
        $booking->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking deleted.']);
        }
        return redirect()->route('air-bookings.index')
            ->with('success', 'Air Export Booking deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id']);
        AirBooking::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' booking(s) deleted.']);
    }

    public function bulkChangeSales(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id', 'sales_person_id' => 'required|exists:users,id']);
        AirBooking::whereIn('id', $data['ids'])->update(['sales_person_id' => $data['sales_person_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' booking(s) sales changed.']);
    }

    public function bulkChangeOp(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id', 'op_id' => 'required|exists:users,id']);
        AirBooking::whereIn('id', $data['ids'])->update(['op_id' => $data['op_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' booking(s) OP changed.']);
    }

    public function updateColor(Request $request, $id)
    {
        $booking = AirBooking::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $booking->update(['color' => $request->color]);
        return response()->json(['success' => true]);
    }

    public function toggleLock(Request $request, $id)
    {
        $booking = AirBooking::findOrFail($id);
        $request->validate(['is_locked' => 'required|boolean']);
        $booking->update(['is_locked' => $request->is_locked]);
        return response()->json([
            'success' => true, 
            'message' => 'Booking ' . ($request->is_locked ? 'locked' : 'unlocked') . ' successfully',
            'is_locked' => $request->is_locked
        ]);
    }

    public function bulkConvert(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_bookings,id']);
        $bookings = AirBooking::whereIn('id', $request->ids)->get();
        $converted = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($bookings as $b) {
            try {
                // Generate unique file_no based on booking_no with timestamp to avoid duplicates
                $fileNo = $b->booking_no;
                
                // Check if file_no already exists, if so add a suffix
                $suffix = 1;
                while (\App\Models\AirExport::where('file_no', $fileNo)->exists()) {
                    $fileNo = $b->booking_no . '-' . $suffix;
                    $suffix++;
                }
                
                $data = [
                    'file_no' => $fileNo,
                    'mawb_no' => $b->booking_no, // Use booking_no as MAWB
                    'booking_no' => $b->booking_no,
                    'post_date' => now(),
                    'office_id' => $b->office_id,
                    'op_id' => $b->op_id,
                    'oversea_agent_id' => $b->oversea_agent_id,
                    'carrier_id' => $b->carrier_id,
                    'flight_no' => $b->flight_no,
                    'dep_port_id' => $b->dep_port_id,
                    'dst_port_id' => $b->dst_port_id,
                    'etd' => $b->etd,
                    'eta' => $b->eta,
                    'pkg_qty' => $b->pkg_qty ?? 0,
                    'pkg_unit_id' => $b->pkg_unit_id,
                    'gross_weight' => $b->gross_weight ?? 0,
                    'chargeable_weight' => $b->chargeable_weight ?? 0,
                    'volume' => $b->volume ?? 0,
                    'shipper_id' => $b->shipper_id,
                    'freight_term' => $b->wt_val_payment ?? null,
                    'is_ecommerce' => false,
                    'cargo_type' => $b->cargo_type,
                    'ship_type' => $b->ship_type,
                ];
                
                \App\Models\AirExport::create($data);
                $converted++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Booking {$b->booking_no}: " . $e->getMessage();
            }
        }
        
        if ($converted > 0 && $failed === 0) {
            return response()->json([
                'success' => true, 
                'message' => $converted . ' booking(s) converted to shipment successfully.'
            ]);
        } elseif ($converted > 0 && $failed > 0) {
            return response()->json([
                'success' => true, 
                'message' => $converted . ' booking(s) converted, ' . $failed . ' failed. ' . implode('; ', array_slice($errors, 0, 3))
            ]);
        } else {
            return response()->json([
                'success' => false, 
                'message' => 'Conversion failed: ' . implode('; ', array_slice($errors, 0, 3))
            ], 422);
        }
    }

    // ==================== ACCOUNTING TAB ====================

    public function accounting($id)
    {
        $booking = AirBooking::findOrFail($id);
        $currencies = \App\Models\Currency::all();
        $tradePartners = \App\Models\TradePartner::all();
        $users = \App\Models\User::all();
        $chargeCodes = \App\Models\ChargeCode::all();
        $currentUser = auth()->user();
        return view('air-export.booking-accounting', compact('booking', 'currencies', 'tradePartners', 'users', 'chargeCodes', 'currentUser'));
    }

    public function getCharges($id)
    {
        $booking = AirBooking::findOrFail($id);
        $charges = $booking->charges()->with('currency')->latest()->get();
        return response()->json($charges);
    }

    public function addCharge(Request $request, $id)
    {
        $booking = AirBooking::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|in:AR,AP,DC_NOTE',
            'charge_code' => 'required|string|max:50',
            'charge_name' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric',
            'qty' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'pc' => 'nullable|string|max:20',
            'vendor_id' => 'nullable|exists:trade_partners,id',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'remark' => 'nullable|string|max:500',
        ]);
        $data['chargeable_type'] = get_class($booking);
        $data['chargeable_id'] = $booking->id;
        $charge = \App\Models\Charge::create($data);
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function updateBookingCharge(Request $request, $chargeId)
    {
        $charge = \App\Models\Charge::findOrFail($chargeId);
        $data = $request->validate([
            'charge_code' => 'nullable|string|max:50',
            'charge_name' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric',
            'qty' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'pc' => 'nullable|string|max:20',
            'vendor_id' => 'nullable|exists:trade_partners,id',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'remark' => 'nullable|string|max:500',
        ]);
        $charge->update($data);
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function deleteBookingCharge($chargeId)
    {
        $charge = \App\Models\Charge::findOrFail($chargeId);
        $charge->delete();
        return response()->json(['success' => true]);
    }

    public function deleteAllBookingCharges($id)
    {
        $booking = AirBooking::findOrFail($id);
        $booking->charges()->delete();
        return response()->json(['success' => true]);
    }

    // ==================== STATUS TAB ====================

    public function status($id)
    {
        $booking = AirBooking::findOrFail($id);
        $users = \App\Models\User::all();
        $currentUser = auth()->user();
        return view('air-export.booking-status', compact('booking', 'users', 'currentUser'));
    }

    public function getBookingHistory($id)
    {
        $booking = AirBooking::findOrFail($id);
        $logs = $booking->statusLogs()->with('user')->latest()->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->status_name ?? $log->action,
                'details' => $log->details,
                'user' => $log->user ? $log->user->name : 'System',
                'event_time' => $log->event_time ? $log->event_time->format('m-d-Y') : ($log->created_at ? $log->created_at->format('m-d-Y') : ''),
                'time' => $log->event_time ? $log->event_time->format('h:i A') : ($log->created_at ? $log->created_at->format('h:i A') : ''),
            ];
        });
        return response()->json($logs);
    }

    public function saveBookingStatus(Request $request, $id)
    {
        $booking = AirBooking::findOrFail($id);
        $user = auth()->user();

        if ($request->has('op_id')) {
            $booking->update(['op_id' => $request->op_id]);
        }
        if ($request->has('sales_person_id')) {
            $booking->update(['sales_person_id' => $request->sales_person_id]);
        }
        if ($request->has('status_code') && in_array($request->status_code, ['OPEN','CONFIRMED','CANCELLED','COMPLETED','BLOCKED'])) {
            $booking->update(['status' => $request->status_code]);
        }

        $booking->statusLogs()->create([
            'shipment_type' => get_class($booking),
            'shipment_id' => $booking->id,
            'status_code' => $request->status_code ?? 'STATUS_UPDATE',
            'status_name' => $request->status_name ?? 'Status Updated',
            'details' => $request->details ?? ($request->internal_message ?? ''),
            'user_id' => $user ? $user->id : null,
            'event_time' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}
