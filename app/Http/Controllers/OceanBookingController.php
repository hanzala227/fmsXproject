<?php

namespace App\Http\Controllers;

use App\Models\OceanBooking;
use App\Models\TradePartner;
use App\Models\Vessel;
use App\Models\Port;
use App\Models\Office;
use App\Models\User;
use App\Models\ServiceTerm;
use App\Models\Quotation;
use App\Models\ContainerType;
use App\Models\Incoterm;
use App\Models\OceanExport;
use Illuminate\Http\Request;

class OceanBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = OceanBooking::with([
            'customer', 'carrier', 'vessel', 'pol', 'pod',
            'op', 'salesPerson', 'office', 'por', 'del',
            'hblAgent', 'actualShipper', 'consignee', 'notify',
            'svcTermFrom', 'svcTermTo', 'forwardingAgent', 'coLoader',
            'trucker', 'fdest',
        ]);

        $this->applyFiltersToQuery($request, $query);

        $bookings = $query->latest()->paginate(20)->withQueryString();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $html = view('ocean-export.partials.booking-list-rows', compact('bookings'))->render();
                $pagination = view('vendor.pagination.custom', ['paginator' => $bookings])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'first' => $bookings->firstItem() ?? 0,
                    'last' => $bookings->lastItem() ?? 0,
                    'total' => $bookings->total(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $offices       = Office::where('is_active', true)->get();
        $tradePartners = TradePartner::all();
        $customers     = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $carriers      = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        $users         = User::all();

        if ($customers->isEmpty()) $customers = $tradePartners;
        if ($carriers->isEmpty())  $carriers  = $tradePartners;

        return view('ocean-export.booking-list', compact(
            'bookings', 'offices', 'tradePartners', 'customers', 'carriers', 'users'
        ));
    }

    protected function applyFiltersToQuery(Request $request, $query)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('carrier', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhere('carrier_bkg_no', 'like', "%{$search}%")
                  ->orWhereHas('vessel', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $filters = [
            'filter_booking_no' => 'booking_no',
            'filter_customer'   => 'customer_id',
            'filter_office'     => 'office_id',
            'filter_carrier'    => 'carrier_id',
            'filter_carrier_bkg_no' => 'carrier_bkg_no',
            'filter_agent'      => 'hbl_agent_id',
            'filter_vessel'     => 'vessel_id',
            'filter_voyage'     => 'voyage',
            'filter_etd'        => 'etd',
            'filter_eta'        => 'eta',
            'filter_pol'        => 'pol_id',
            'filter_pod'        => 'pod_id',
            'filter_por'        => 'por_id',
            'filter_del'        => 'del_id',
            'filter_op'         => 'op_id',
            'filter_sales'      => 'sales_person_id',
            'filter_status'     => 'status',
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
        $offices = Office::where('is_active', true)->get();
        $tradePartners = TradePartner::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $truckers = TradePartner::whereIn('type', ['TR', 'TRUCKER'])->get();
        $vessels = Vessel::all();
        $ports = Port::all();
        $users = User::all();
        $serviceTerms = ServiceTerm::all();
        $containerTypes = ContainerType::all();
        $incoterms = Incoterm::all();
        $quote = $request->quote_id ? Quotation::with(['customer', 'pol', 'pod', 'salesPerson', 'items'])->find($request->quote_id) : null;
        return view('ocean-export.booking', compact('offices', 'tradePartners', 'carriers', 'customers', 'truckers', 'vessels', 'ports', 'users', 'serviceTerms', 'containerTypes', 'incoterms', 'quote'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_no'          => 'required|string|unique:ocean_bookings,booking_no',
            'booking_date'        => 'required|date',
            'quotation_no'        => 'nullable|string',
            'itn_no'              => 'nullable|string',
            'sales_person_id'     => 'nullable|exists:users,id',
            'op_id'               => 'nullable|exists:users,id',
            'carrier_bkg_no'      => 'nullable|string',
            'customer_id'         => 'nullable|exists:trade_partners,id',
            'carrier_id'          => 'nullable|exists:trade_partners,id',
            'ship_mode'           => 'nullable|string|max:50',
            'svc_term_from_id'    => 'nullable|exists:service_terms,id',
            'svc_term_to_id'      => 'nullable|exists:service_terms,id',
            'incoterms'           => 'nullable|string|max:10',
            'actual_shipper_id'   => 'nullable|exists:trade_partners,id',
            'bill_to_id'          => 'nullable|exists:trade_partners,id',
            'consignee_id'        => 'nullable|exists:trade_partners,id',
            'notify_id'           => 'nullable|exists:trade_partners,id',
            'shipping_agent'      => 'nullable|string|max:100',
            'hbl_agent_id'        => 'nullable|exists:trade_partners,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'co_loader_id'        => 'nullable|exists:trade_partners,id',
            'vessel_id'           => 'nullable|exists:vessels,id',
            'voyage'              => 'nullable|string|max:255',
            'por_id'              => 'nullable|exists:ports,id',
            'pol_id'              => 'nullable|exists:ports,id',
            'pod_id'              => 'nullable|exists:ports,id',
            'del_id'              => 'nullable|exists:ports,id',
            'fdest_id'            => 'nullable|exists:ports,id',
            'etd'                 => 'nullable|date',
            'eta'                 => 'nullable|date',
            'office_id'           => 'nullable|exists:offices,id',
            'cargo_type'          => 'nullable|string|max:50',
            'trucker_id'          => 'nullable|exists:trade_partners,id',
            'container_no'        => 'nullable|string',
            'marks'               => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:1000',
            'remarks'             => 'nullable|string|max:1000',
            'pkg_qty'             => 'nullable|numeric',
            'weight_kg'           => 'nullable|numeric',
            'measure_cbm'         => 'nullable|numeric',
            'status'              => 'required|in:OPEN,CONFIRMED,CANCELLED,COMPLETED',
            'is_hold'             => 'nullable|boolean',
            'hold_by_id'          => 'nullable|exists:users,id',
            'is_freight_released' => 'nullable|boolean',
            'freight_released_by_id' => 'nullable|exists:users,id',
            'buying_freight'      => 'nullable|string|max:50',
            'selling_freight'     => 'nullable|string|max:50',
            'customs_broker_id'   => 'nullable|exists:trade_partners,id',
            'por_etd'             => 'nullable|date',
            'sales_type'          => 'nullable|string|max:50',
            'on_board_date'       => 'nullable|date',
            'lc_no'               => 'nullable|string|max:100',
            'lc_issue_bank'       => 'nullable|string|max:255',
            'lc_issue_date'       => 'nullable|date',
            'is_express_bl'       => 'nullable|boolean',
            'show_preferences'    => 'nullable|boolean',
        ]);

        $validated['is_hold'] = $request->boolean('is_hold');
        $validated['is_freight_released'] = $request->boolean('is_freight_released');
        $validated['is_express_bl'] = $request->boolean('is_express_bl');
        $validated['show_preferences'] = $request->boolean('show_preferences');

        $booking = OceanBooking::create($validated);

        return redirect()->route('ocean-bookings.edit', $booking->id)
            ->with('success', 'Ocean Export Booking created successfully.');
    }

    public function edit($id)
    {
        $booking = OceanBooking::with('invoices.billTo')->findOrFail($id);
        $offices = Office::where('is_active', true)->get();
        $tradePartners = TradePartner::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $truckers = TradePartner::whereIn('type', ['TR', 'TRUCKER'])->get();
        $vessels = Vessel::all();
        $ports = Port::all();
        $users = User::all();
        $serviceTerms = ServiceTerm::all();
        $containerTypes = ContainerType::all();
        $incoterms = Incoterm::all();
        return view('ocean-export.booking', compact('booking', 'offices', 'tradePartners', 'carriers', 'customers', 'truckers', 'vessels', 'ports', 'users', 'serviceTerms', 'containerTypes', 'incoterms'));
    }

    public function update(Request $request, $id)
    {
        $booking = OceanBooking::findOrFail($id);

        $validated = $request->validate([
            'booking_no'          => 'required|string|unique:ocean_bookings,booking_no,' . $booking->id,
            'booking_date'        => 'required|date',
            'quotation_no'        => 'nullable|string',
            'itn_no'              => 'nullable|string',
            'sales_person_id'     => 'nullable|exists:users,id',
            'op_id'               => 'nullable|exists:users,id',
            'carrier_bkg_no'      => 'nullable|string',
            'customer_id'         => 'nullable|exists:trade_partners,id',
            'carrier_id'          => 'nullable|exists:trade_partners,id',
            'ship_mode'           => 'nullable|string|max:50',
            'svc_term_from_id'    => 'nullable|exists:service_terms,id',
            'svc_term_to_id'      => 'nullable|exists:service_terms,id',
            'incoterms'           => 'nullable|string|max:10',
            'actual_shipper_id'   => 'nullable|exists:trade_partners,id',
            'bill_to_id'          => 'nullable|exists:trade_partners,id',
            'consignee_id'        => 'nullable|exists:trade_partners,id',
            'notify_id'           => 'nullable|exists:trade_partners,id',
            'shipping_agent'      => 'nullable|string|max:100',
            'hbl_agent_id'        => 'nullable|exists:trade_partners,id',
            'forwarding_agent_id' => 'nullable|exists:trade_partners,id',
            'co_loader_id'        => 'nullable|exists:trade_partners,id',
            'vessel_id'           => 'nullable|exists:vessels,id',
            'voyage'              => 'nullable|string|max:255',
            'por_id'              => 'nullable|exists:ports,id',
            'pol_id'              => 'nullable|exists:ports,id',
            'pod_id'              => 'nullable|exists:ports,id',
            'del_id'              => 'nullable|exists:ports,id',
            'fdest_id'            => 'nullable|exists:ports,id',
            'etd'                 => 'nullable|date',
            'eta'                 => 'nullable|date',
            'office_id'           => 'nullable|exists:offices,id',
            'cargo_type'          => 'nullable|string|max:50',
            'trucker_id'          => 'nullable|exists:trade_partners,id',
            'container_no'        => 'nullable|string',
            'marks'               => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:1000',
            'remarks'             => 'nullable|string|max:1000',
            'pkg_qty'             => 'nullable|numeric',
            'weight_kg'           => 'nullable|numeric',
            'measure_cbm'         => 'nullable|numeric',
            'status'              => 'required|in:OPEN,CONFIRMED,CANCELLED,COMPLETED',
            'is_hold'             => 'nullable|boolean',
            'hold_by_id'          => 'nullable|exists:users,id',
            'is_freight_released' => 'nullable|boolean',
            'freight_released_by_id' => 'nullable|exists:users,id',
            'buying_freight'      => 'nullable|string|max:50',
            'selling_freight'     => 'nullable|string|max:50',
            'customs_broker_id'   => 'nullable|exists:trade_partners,id',
            'por_etd'             => 'nullable|date',
            'sales_type'          => 'nullable|string|max:50',
            'on_board_date'       => 'nullable|date',
            'lc_no'               => 'nullable|string|max:100',
            'lc_issue_bank'       => 'nullable|string|max:255',
            'lc_issue_date'       => 'nullable|date',
            'is_express_bl'       => 'nullable|boolean',
            'show_preferences'    => 'nullable|boolean',
        ]);

        $validated['is_hold'] = $request->boolean('is_hold');
        $validated['is_freight_released'] = $request->boolean('is_freight_released');
        $validated['is_express_bl'] = $request->boolean('is_express_bl');
        $validated['show_preferences'] = $request->boolean('show_preferences');

        $booking->update($validated);

        return redirect()->route('ocean-bookings.index')
            ->with('success', 'Ocean Export Booking updated successfully.');
    }

    public function destroy($id)
    {
        $booking = OceanBooking::findOrFail($id);
        $booking->delete();
        return redirect()->route('ocean-bookings.index')
            ->with('success', 'Ocean Export Booking deleted successfully.');
    }

    public function updateColor(Request $request, $id)
    {
        $booking = OceanBooking::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $booking->update(['color' => $request->color]);
        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:ocean_bookings,id']);
        OceanBooking::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' booking(s) deleted.']);
    }

    public function bulkChangeSales(Request $request)
    {
        $request->validate([
            'ids'     => 'required|array',
            'ids.*'   => 'exists:ocean_bookings,id',
            'user_id' => 'required|exists:users,id',
        ]);
        OceanBooking::whereIn('id', $request->ids)->update(['sales_person_id' => $request->user_id]);
        return response()->json(['success' => true, 'message' => 'Sales person updated for ' . count($request->ids) . ' booking(s).']);
    }

    public function bulkChangeOp(Request $request)
    {
        $request->validate([
            'ids'     => 'required|array',
            'ids.*'   => 'exists:ocean_bookings,id',
            'user_id' => 'required|exists:users,id',
        ]);
        OceanBooking::whereIn('id', $request->ids)->update(['op_id' => $request->user_id]);
        return response()->json(['success' => true, 'message' => 'OP updated for ' . count($request->ids) . ' booking(s).']);
    }

    public function convertToShipment(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:ocean_bookings,id']);

        $created = [];
        foreach ($request->ids as $id) {
            $booking = OceanBooking::findOrFail($id);
            $export = OceanExport::create([
                'file_no'              => $booking->booking_no,
                'booking_no'           => $booking->booking_no,
                'carrier_id'           => $booking->carrier_id,
                'vessel_id'            => $booking->vessel_id,
                'voyage'               => $booking->voyage,
                'pol_id'               => $booking->pol_id,
                'pod_id'               => $booking->pod_id,
                'etd'                  => $booking->etd,
                'eta'                  => $booking->eta,
                'del_id'               => $booking->del_id,
                'fdest_id'             => $booking->fdest_id,
                'service_term_from_id' => $booking->svc_term_from_id,
                'service_term_to_id'   => $booking->svc_term_to_id,
                'office_id'            => $booking->office_id,
                'op_id'                => $booking->op_id,
                'forwarding_agent_id'  => $booking->forwarding_agent_id,
                'co_loader_id'         => $booking->co_loader_id,
            ]);
            $created[] = $export->id;
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' booking(s) converted to shipment.',
            'ids'     => $created,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = OceanBooking::with([
            'customer', 'carrier', 'vessel', 'pol', 'pod',
            'op', 'salesPerson', 'office', 'por', 'del',
            'hblAgent'
        ]);

        $this->applyFiltersToQuery($request, $query);

        $bookings = $query->latest()->get();

        $filename = 'ocean-bookings-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Booking No.',
                'Customer',
                'Office',
                'Carrier',
                'Carrier Bkg No.',
                'Agent',
                'Vessel',
                'Voyage',
                'ETD',
                'ETA',
                'POL',
                'POD',
                'POR',
                'DEL',
                'OP',
                'Sales',
                'Status',
                'Booking Date',
                'Incoterms',
                'Container',
                'Pkg Qty',
                'Weight (KG)',
                'Measure (CBM)',
            ]);

            // Data Rows
            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->booking_no,
                    $b->customer->name ?? '',
                    $b->office->code ?? '',
                    $b->carrier->name ?? '',
                    $b->carrier_bkg_no ?? '',
                    $b->hblAgent->name ?? $b->shipping_agent ?? '',
                    $b->vessel->name ?? '',
                    $b->voyage ?? '',
                    $b->etd ? $b->etd->format('m-d-Y') : '',
                    $b->eta ? $b->eta->format('m-d-Y') : '',
                    $b->pol->name ?? '',
                    $b->pod->name ?? '',
                    $b->por->name ?? '',
                    $b->del->name ?? '',
                    $b->op->name ?? '',
                    $b->salesPerson->name ?? '',
                    $b->status,
                    $b->booking_date ? $b->booking_date->format('m-d-Y') : '',
                    $b->incoterms ?? '',
                    $b->container_no ?? '',
                    $b->pkg_qty ?? '',
                    $b->weight_kg ?? '',
                    $b->measure_cbm ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
