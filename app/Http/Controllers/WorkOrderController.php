<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\TradePartner;
use App\Models\OceanBooking;
use App\Models\AirBooking;
use App\Models\Port;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkOrderController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with(['workable', 'vendor'])
            ->latest()
            ->paginate(20);
        return view('ocean-export.work-order-list', compact('workOrders'));
    }

    public function apiIndex(Request $request)
    {
        $request->validate([
            'workable_type' => 'required|string',
            'workable_id'   => 'required|integer',
        ]);

        $workOrders = WorkOrder::where('workable_type', $request->workable_type)
            ->where('workable_id', $request->workable_id)
            ->with(['vendor', 'freightPickupLocation', 'emptyPickupLocation'])
            ->latest()
            ->get()
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'work_order_no' => $wo->work_order_no,
                    'subject' => $wo->subject ?? 'PICKUP & DELIVERY ORDER',
                    'vendor_name' => $wo->vendor->name ?? 'N/A',
                    'issue_date' => $wo->issue_date ? $wo->issue_date->format('m/d/Y') : null,
                    'freight_pickup_location_name' => $wo->freightPickupLocation->name ?? null,
                    'freight_pickup_date' => $wo->freight_pickup_date,
                    'empty_return_location_name' => $wo->emptyPickupLocation->name ?? null,
                    'empty_return_date' => $wo->empty_pickup_date,
                    'updated_at' => $wo->updated_at ? $wo->updated_at->format('m/d/Y H:i') : null,
                    'created_at' => $wo->created_at ? $wo->created_at->format('m/d/Y H:i') : null,
                ];
            });

        return response()->json($workOrders);
    }

    public function create(Request $request)
    {
        $workableType = $request->query('workable_type');
        $workableId = $request->query('workable_id');
        
        // Store source information for redirect after save
        $source = $request->query('source'); // e.g., 'air_export'
        $sourceId = $request->query('source_id'); // e.g., shipment ID

        $workable = null;
        $prefilledData = [];
        
        // Handle different mbl_no parameter names
        $mblNo = $request->query('mbl_no') ?? $request->query('mawb_no') ?? '';
        $fileNo = $request->query('file_no') ?? '';

        if ($workableType && $workableId) {
            if ($workableType === 'App\Models\OceanBooking' || $workableType === 'OceanBooking') {
                $workableType = \App\Models\OceanBooking::class;
                $workable = OceanBooking::with(['carrier', 'vessel', 'pol', 'pod'])->find($workableId);
                
                if ($workable) {
                    $prefilledData = [
                        'booking_no'       => $workable->booking_no,
                        'carrier_id'       => $workable->carrier_id,
                        'carrier_name'     => $workable->carrier->name ?? '',
                        'carrier_bkg_no'   => $workable->booking_no,
                        'vessel_info'      => ($workable->vessel->name ?? '') . ($workable->voyage ? ' / ' . $workable->voyage : ''),
                        'place_of_receipt' => $workable->pol->name ?? '',
                        'etd'              => $workable->etd ? $workable->etd->format('Y-m-d') : '',
                    ];
                }
            } elseif ($workableType === 'App\Models\AirBooking' || $workableType === 'AirBooking') {
                $workableType = \App\Models\AirBooking::class;
                $workable = AirBooking::find($workableId);
                if ($workable) {
                    $prefilledData = [
                        'booking_no'       => $workable->booking_no,
                        'carrier_bkg_no'   => $workable->booking_no,
                        'etd'              => $workable->etd ? $workable->etd->format('Y-m-d') : '',
                    ];
                }
            } elseif ($workableType === 'App\Models\AirExport' || $workableType === 'AirExport') {
                // Air Export handling
                $workableType = \App\Models\AirExport::class;
                $workable = \App\Models\AirExport::find($workableId);
                if ($workable) {
                    $prefilledData = [
                        'mbl_no'           => $workable->mawb_no ?? $mblNo,
                        'file_no'          => $workable->file_no ?? $fileNo,
                        'carrier_bkg_no'   => $workable->mawb_no ?? '',
                        'etd'              => $workable->etd ? $workable->etd->format('Y-m-d') : '',
                    ];
                }
            } elseif ($workableType === 'App\Models\AirImport' || $workableType === 'AirImport') {
                // Air Import handling
                $workableType = \App\Models\AirImport::class;
                $workable = \App\Models\AirImport::find($workableId);
                if ($workable) {
                    $prefilledData = [
                        'mbl_no'           => $workable->mawb_no ?? $mblNo,
                        'file_no'          => $workable->file_no ?? $fileNo,
                        'carrier_bkg_no'   => $workable->mawb_no ?? '',
                        'etd'              => $workable->etd ? $workable->etd->format('Y-m-d') : '',
                    ];
                }
            }
        }
        
        // Add mbl_no and file_no to prefilled data if provided
        if ($mblNo && !isset($prefilledData['mbl_no'])) {
            $prefilledData['mbl_no'] = $mblNo;
        }
        if ($fileNo && !isset($prefilledData['file_no'])) {
            $prefilledData['file_no'] = $fileNo;
        }

        // Generate work order number if none exists
        $workOrderNo = 'WO-' . date('Ymd') . '-' . rand(1000, 9999);

        $tradePartners = TradePartner::orderBy('name')->get();
        $ports = Port::orderBy('name')->get();
        $vessels = Vessel::orderBy('name')->get();

        return view('ocean-export.work-order-form', compact(
            'workableType',
            'workableId',
            'workable',
            'prefilledData',
            'workOrderNo',
            'tradePartners',
            'ports',
            'vessels',
            'source',
            'sourceId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_no'              => 'required|string|unique:work_orders,work_order_no',
            'workable_type'              => 'required|string',
            'workable_id'                => 'required|integer',
            'vendor_id'                  => 'nullable|exists:trade_partners,id',
            'issue_date'                 => 'nullable|date',
            'due_date'                   => 'nullable|date',
            'subject'                    => 'nullable|string|max:255',
            'instructions'               => 'nullable|string',
            'status'                     => 'required|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            
            'carrier_id'                 => 'nullable|exists:trade_partners,id',
            'carrier_bkg_no'             => 'nullable|string|max:255',
            'place_of_receipt'           => 'nullable|string|max:255',
            'vessel_info'                => 'nullable|string|max:255',
            'etd'                        => 'nullable|string|max:255',
            
            'empty_pickup_location_id'   => 'nullable|exists:trade_partners,id',
            'empty_pickup_address'       => 'nullable|string',
            'empty_pickup_ref'           => 'nullable|string|max:255',
            'empty_pickup_date'          => 'nullable|string|max:255',
            
            'freight_pickup_location_id' => 'nullable|exists:trade_partners,id',
            'freight_pickup_address'     => 'nullable|string',
            'freight_pickup_ref'         => 'nullable|string|max:255',
            'freight_pickup_date'        => 'nullable|string|max:255',
            
            'total_packages'             => 'nullable|integer',
            'package_unit'               => 'nullable|string|max:255',
            'container_qty'              => 'nullable|string|max:255',
            'gross_weight_kgs'           => 'nullable|string|max:255',
            'gross_weight_lbs'           => 'nullable|string|max:255',
            
            'show_bill_to'               => 'nullable|boolean',
            'bill_to_id'                 => 'nullable|exists:trade_partners,id',
            'bill_to_address'            => 'nullable|string',
            'bill_to_ref'                => 'nullable|string|max:255',
            
            'do_not_break_down_pallet'   => 'nullable|boolean',
            'extra_data'                 => 'nullable|array',
        ]);

        // Fix boolean inputs
        $validated['show_bill_to'] = $request->has('show_bill_to') || $request->input('show_bill_to') == '1';
        $validated['do_not_break_down_pallet'] = $request->has('do_not_break_down_pallet') || $request->input('do_not_break_down_pallet') == '1';
        $validated['created_by'] = auth()->id() ?? 1;

        DB::beginTransaction();
        try {
            $workOrder = WorkOrder::create($validated);
            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'id' => $workOrder->id, 'work_order' => $workOrder]);
            }

            // Check if we have source information to redirect back
            $source = $request->input('source');
            $sourceId = $request->input('source_id');
            
            \Log::info('Work Order Redirect Debug', [
                'source' => $source,
                'sourceId' => $sourceId,
                'workOrderId' => $workOrder->id
            ]);
            
            if ($source && $sourceId) {
                // Redirect to source page with success message and active tab parameter
                $redirectMap = [
                    'air_export' => ['route' => 'air-export.edit', 'param' => 'air_export'],
                    'air_import' => ['route' => 'air-import.edit', 'param' => 'air_import'],
                    'ocean_export' => ['route' => 'ocean-export.edit', 'param' => 'ocean_export'],
                    'ocean_import' => ['route' => 'ocean-import.edit', 'param' => 'ocean_import'],
                    'ocean_booking' => ['route' => 'ocean-bookings.edit', 'param' => 'id'],
                ];
                
                if (isset($redirectMap[$source])) {
                    $routeInfo = $redirectMap[$source];
                    \Log::info('Redirecting to source', [
                        'route' => $routeInfo['route'],
                        'param' => $routeInfo['param'],
                        'id' => $sourceId
                    ]);
                    
                    return redirect()->route($routeInfo['route'], [
                        $routeInfo['param'] => $sourceId,
                        'tab' => 'work'
                    ])->with('success', 'Work order created successfully');
                }
            }

            \Log::info('No source, redirecting to edit');
            return redirect()->route('ocean-export.work-order.edit', $workOrder->id)
                ->with('success', 'Work Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Work Order Store Error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', 'Failed to save Work Order: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workable = $workOrder->workable;
        $workableType = $workOrder->workable_type;
        $workableId = $workOrder->workable_id;
        
        // Get source information from URL (for redirect after update)
        $source = request()->query('source');
        $sourceId = request()->query('source_id');

        $tradePartners = TradePartner::orderBy('name')->get();
        $ports = Port::orderBy('name')->get();
        $vessels = Vessel::orderBy('name')->get();

        return view('ocean-export.work-order-form', compact(
            'workOrder',
            'workableType',
            'workableId',
            'workable',
            'tradePartners',
            'ports',
            'vessels',
            'source',
            'sourceId'
        ));
    }

    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'work_order_no'              => 'required|string|unique:work_orders,work_order_no,' . $workOrder->id,
            'workable_type'              => 'required|string',
            'workable_id'                => 'required|integer',
            'vendor_id'                  => 'nullable|exists:trade_partners,id',
            'issue_date'                 => 'nullable|date',
            'due_date'                   => 'nullable|date',
            'subject'                    => 'nullable|string|max:255',
            'instructions'               => 'nullable|string',
            'status'                     => 'required|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            
            'carrier_id'                 => 'nullable|exists:trade_partners,id',
            'carrier_bkg_no'             => 'nullable|string|max:255',
            'place_of_receipt'           => 'nullable|string|max:255',
            'vessel_info'                => 'nullable|string|max:255',
            'etd'                        => 'nullable|string|max:255',
            
            'empty_pickup_location_id'   => 'nullable|exists:trade_partners,id',
            'empty_pickup_address'       => 'nullable|string',
            'empty_pickup_ref'           => 'nullable|string|max:255',
            'empty_pickup_date'          => 'nullable|string|max:255',
            
            'freight_pickup_location_id' => 'nullable|exists:trade_partners,id',
            'freight_pickup_address'     => 'nullable|string',
            'freight_pickup_ref'         => 'nullable|string|max:255',
            'freight_pickup_date'        => 'nullable|string|max:255',
            
            'total_packages'             => 'nullable|integer',
            'package_unit'               => 'nullable|string|max:255',
            'container_qty'              => 'nullable|string|max:255',
            'gross_weight_kgs'           => 'nullable|string|max:255',
            'gross_weight_lbs'           => 'nullable|string|max:255',
            
            'show_bill_to'               => 'nullable|boolean',
            'bill_to_id'                 => 'nullable|exists:trade_partners,id',
            'bill_to_address'            => 'nullable|string',
            'bill_to_ref'                => 'nullable|string|max:255',
            
            'do_not_break_down_pallet'   => 'nullable|boolean',
            'extra_data'                 => 'nullable|array',
        ]);

        $validated['show_bill_to'] = $request->has('show_bill_to') || $request->input('show_bill_to') == '1';
        $validated['do_not_break_down_pallet'] = $request->has('do_not_break_down_pallet') || $request->input('do_not_break_down_pallet') == '1';

        DB::beginTransaction();
        try {
            $workOrder->update($validated);
            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'id' => $workOrder->id, 'work_order' => $workOrder]);
            }

            // Check if we have source information to redirect back
            $source = $request->input('source');
            $sourceId = $request->input('source_id');
            
            \Log::info('Work Order Update Redirect Debug', [
                'source' => $source,
                'sourceId' => $sourceId,
                'workOrderId' => $workOrder->id
            ]);
            
            if ($source && $sourceId) {
                // Redirect to source page with success message and active tab parameter
                $redirectMap = [
                    'air_export' => ['route' => 'air-export.edit', 'param' => 'air_export'],
                    'air_import' => ['route' => 'air-import.edit', 'param' => 'air_import'],
                    'ocean_export' => ['route' => 'ocean-export.edit', 'param' => 'ocean_export'],
                    'ocean_import' => ['route' => 'ocean-import.edit', 'param' => 'ocean_import'],
                    'ocean_booking' => ['route' => 'ocean-bookings.edit', 'param' => 'id'],
                ];
                
                if (isset($redirectMap[$source])) {
                    $routeInfo = $redirectMap[$source];
                    \Log::info('Redirecting to source after update', [
                        'route' => $routeInfo['route'],
                        'param' => $routeInfo['param'],
                        'id' => $sourceId
                    ]);
                    
                    return redirect()->route($routeInfo['route'], [
                        $routeInfo['param'] => $sourceId,
                        'tab' => 'work'
                    ])->with('success', 'Work order updated successfully');
                }
            }

            \Log::info('No source, redirecting to edit after update');
            return redirect()->route('ocean-export.work-order.edit', $workOrder->id)
                ->with('success', 'Work Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Work Order Update Error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', 'Failed to update Work Order: ' . $e->getMessage());
        }
    }

    public function exportExcel($id)
    {
        $workOrder = WorkOrder::with(['vendor', 'freightPickupLocation', 'emptyPickupLocation'])->findOrFail($id);

        $filename = "WorkOrder_{$workOrder->work_order_no}.csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = [
            'Work Order No', 'Subject', 'Vendor', 'Issue Date', 'Due Date', 
            'Freight Pickup Location', 'Freight Pickup Date', 
            'Empty Return Location', 'Empty Return Date', 'Status'
        ];
        
        $callback = function() use($workOrder, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            fputcsv($file, [
                $workOrder->work_order_no,
                $workOrder->subject,
                $workOrder->vendor ? $workOrder->vendor->name : '',
                $workOrder->issue_date ? $workOrder->issue_date->format('Y-m-d') : '',
                $workOrder->due_date ? $workOrder->due_date->format('Y-m-d') : '',
                $workOrder->freightPickupLocation ? $workOrder->freightPickupLocation->name : '',
                $workOrder->freight_pickup_date,
                $workOrder->emptyPickupLocation ? $workOrder->emptyPickupLocation->name : '',
                $workOrder->empty_pickup_date,
                $workOrder->status
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function destroy($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->delete();
        return response()->json(['success' => true]);
    }
}
