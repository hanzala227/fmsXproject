<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TradePartner;
use App\Models\Port;
use App\Models\Office;
use App\Models\User;
use App\Models\Location;
use App\Models\PackageUnit;
use App\Models\ContainerType;
use App\Models\Warehouse;
use App\Models\Currency;
use App\Models\Quotation;
use Illuminate\Http\Request;

class DropdownOptionsController extends Controller
{
    public function agents(Request $request)
    {
        try {
            $agents = TradePartner::orderBy('name')
                ->select('id', 'name', 'type')
                ->get()
                ->map(function ($agent) {
                    // Determine roles based on type - using actual type codes from the codebase
                    $type = strtoupper($agent->type ?? '');
                    return [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'company_name' => $agent->name, // For compatibility
                        // Customer types: CS, CLIENT, CUSTOMER
                        'is_customer' => in_array($type, ['CS', 'CLIENT', 'CUSTOMER']),
                        // Shipper types: SH, KS, SHIPPER_KNOWN, SHIPPER_UNKNOWN
                        'is_shipper' => in_array($type, ['SH', 'KS', 'SHIPPER_KNOWN', 'SHIPPER_UNKNOWN']),
                        // Consignee types: CN, CONSIGNEE
                        'is_consignee' => in_array($type, ['CN', 'CONSIGNEE']),
                        // Oversea Agent types: PR, AGENT, FR, FORWARDER, FW, AG, OA
                        'is_oversea_agent' => in_array($type, ['PR', 'AGENT', 'FR', 'FORWARDER', 'FW', 'AG', 'OA']),
                        // Trucker types: TK, TRUCKER, TR
                        'is_trucker' => in_array($type, ['TK', 'TRUCKER', 'TR']),
                        // Vendor types: VR, VENDOR
                        'is_vendor' => in_array($type, ['VR', 'VENDOR']),
                    ];
                });

            return response()->json(['data' => $agents]);
        } catch (\Exception $e) {
            \Log::error("Error in agents endpoint: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage(), 'data' => []], 500);
        }
    }

    public function ports(Request $request)
    {
        $ports = Port::orderBy('name')
            ->select('id', 'name', 'code', 'country')
            ->get();

        return response()->json(['data' => $ports]);
    }

    public function offices(Request $request)
    {
        $offices = Office::where('is_active', true)
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        return response()->json(['data' => $offices]);
    }

    public function users(Request $request)
    {
        $users = User::orderBy('name')
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['data' => $users]);
    }

    public function locations(Request $request)
    {
        $locations = Location::orderBy('name')
            ->select('id', 'name', 'code', 'address')
            ->get();

        return response()->json(['data' => $locations]);
    }

    public function packageUnits(Request $request)
    {
        $units = PackageUnit::orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        return response()->json(['data' => $units]);
    }

    public function containerTypes(Request $request)
    {
        $types = ContainerType::orderBy('code')
            ->select('id', 'code', 'name', 'description')
            ->get();

        return response()->json(['data' => $types]);
    }

    public function warehouses(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')
            ->select('id', 'name', 'code', 'address')
            ->get();

        return response()->json(['data' => $warehouses]);
    }

    public function currencies(Request $request)
    {
        $currencies = Currency::orderBy('code')
            ->select('id', 'code', 'name', 'symbol')
            ->get();

        return response()->json(['data' => $currencies]);
    }

    public function quotations(Request $request)
    {
        $query = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'items.currency']);

        $moduleFilter = $request->input('module') 
                     ?? $request->input('transport_mode') 
                     ?? $request->input('shipping_type') 
                     ?? $request->input('type');

        if ($moduleFilter) {
            $query->forModule($moduleFilter);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('quote_no')) {
            $query->where('quote_no', 'like', '%' . $request->quote_no . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('pol_id')) {
            $query->where('pol_id', $request->pol_id);
        }
        if ($request->filled('pod_id')) {
            $query->where('pod_id', $request->pod_id);
        }
        if ($request->filled('sales_person_id')) {
            $query->where('sales_person_id', $request->sales_person_id);
        }
        if ($request->filled('commodity')) {
            $query->where('commodity', 'like', '%' . $request->commodity . '%');
        }

        $quotations = $query->orderBy('quote_no', 'desc')->limit(100)->get()->map(function($q) {
            return [
                'id' => $q->id,
                'quote_no' => $q->quote_no,
                'customer_id' => $q->customer_id,
                'customer_name' => $q->customer ? ($q->customer->company_name ?? $q->customer->name) : 'N/A',
                'pol_id' => $q->pol_id,
                'pol_name' => $q->pol ? $q->pol->name : 'N/A',
                'pod_id' => $q->pod_id,
                'pod_name' => $q->pod ? $q->pod->name : 'N/A',
                'sales_person_id' => $q->sales_person_id,
                'sales_person_name' => $q->salesPerson ? $q->salesPerson->name : 'N/A',
                'status' => $q->status,
                'commodity' => $q->commodity,
                'expiry_date' => $q->expiry_date ? (is_string($q->expiry_date) ? substr($q->expiry_date, 0, 10) : $q->expiry_date->format('Y-m-d')) : '',
                'created_at' => $q->created_at ? $q->created_at->format('Y-m-d') : '',
                'items' => $q->items ? $q->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'charge_code' => $item->charge_code ?? $item->code ?? '',
                        'charge_name' => $item->charge_name ?? $item->description ?? '',
                        'currency' => $item->currency ? $item->currency->code : 'USD',
                        'rate' => (float)($item->rate ?? 0),
                        'qty' => (float)($item->qty ?? $item->quantity ?? 1),
                        'unit' => $item->unit ?? 'UNIT',
                        'amount' => (float)($item->amount ?? ($item->rate * $item->qty ?? 0)),
                        'selected' => true,
                    ];
                }) : [],
            ];
        });

        return response()->json(['data' => $quotations]);
    }

    public function truckers(Request $request)
    {
        $truckers = TradePartner::where('is_trucker', true)
            ->orderBy('company_name')
            ->select('id', 'company_name', 'name')
            ->get()
            ->map(function ($trucker) {
                return [
                    'id' => $trucker->id,
                    'name' => $trucker->company_name ?? $trucker->name,
                ];
            });

        return response()->json(['data' => $truckers]);
    }

    public function vendors(Request $request)
    {
        $vendors = TradePartner::where('is_vendor', true)
            ->orderBy('company_name')
            ->select('id', 'company_name', 'name')
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->company_name ?? $vendor->name,
                ];
            });

        return response()->json(['data' => $vendors]);
    }
}
