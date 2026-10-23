<?php

namespace App\Http\Controllers;

use App\Models\OceanImport;
use App\Models\ShipmentTracking;
use App\Models\TrackingEvent;
use App\Services\Tracking\TrackingManager;
use Illuminate\Http\Request;
use Throwable;

class TrackingController extends Controller
{
    /**
     * Admin Tracking Center view.
     */
    public function adminIndex(Request $request)
    {
        $query = ShipmentTracking::with(['events'])
            ->latest('last_polled_at');

        if (auth()->check() && !auth()->user()->isSuperAdmin() && auth()->user()->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('vessel_name', 'like', "%{$search}%")
                  ->orWhere('origin_name', 'like', "%{$search}%")
                  ->orWhere('destination_name', 'like', "%{$search}%");
            });
        }

        if ($carrier = $request->get('carrier')) {
            $query->where('carrier_code', $carrier);
        }

        if ($status = $request->get('status')) {
            $query->where('current_status', $status);
        }

        $trackings = $query->paginate(20)->withQueryString();

        return view('tracking.index', compact('trackings'));
    }

    /**
     * Live AJAX Tracking Sync from Carrier.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|min:3',
            'type' => 'nullable|string|in:container,mbl,booking,bol',
            'carrier_code' => 'nullable|string',
            'shipment_id' => 'nullable|integer',
            'shipment_type' => 'nullable|string',
        ]);

        try {
            $number = strtoupper(trim($request->tracking_number));
            $type = $request->type ?: 'container';
            $carrierCode = $request->carrier_code;

            $shipment = null;
            $options = [];

            if ($request->shipment_id) {
                $modelClass = $request->shipment_type ?: OceanImport::class;
                if (class_exists($modelClass)) {
                    $shipment = $modelClass::find($request->shipment_id);
                    if ($shipment) {
                        $options = [
                            'origin_name' => $shipment->portOfLoading->name ?? ($shipment->pol_name ?? null),
                            'origin_locode' => $shipment->portOfLoading->code ?? null,
                            'destination_name' => $shipment->portOfDischarge->name ?? ($shipment->pod_name ?? null),
                            'destination_locode' => $shipment->portOfDischarge->code ?? null,
                            'vessel_name' => $shipment->vessel->name ?? ($shipment->vessel_name ?? null),
                            'voyage_no' => $shipment->voyage ?? null,
                            'etd' => $shipment->etd ?? null,
                            'eta' => $shipment->eta ?? null,
                        ];
                    }
                }
            }

            $tracking = TrackingManager::syncTracking(
                trackingNumber: $number,
                type: $type,
                options: $options,
                shipment: $shipment,
                carrierCode: $carrierCode
            );

            return response()->json([
                'success' => true,
                'message' => 'Tracking synchronized successfully with carrier.',
                'tracking' => $tracking,
                'status_badge' => $tracking->status_badge,
                'events' => $tracking->events,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize tracking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tracking details and event history via AJAX.
     */
    public function getDetails(Request $request)
    {
        $number = strtoupper(trim((string) $request->get('tracking_number')));
        $shipmentId = $request->get('shipment_id');

        $query = ShipmentTracking::with(['events']);

        if ($number) {
            $query->where('tracking_number', $number);
        } elseif ($shipmentId) {
            $modelClass = $request->get('shipment_type') ?: OceanImport::class;
            $query->where('trackable_type', $modelClass)
                  ->where('trackable_id', $shipmentId);
        } else {
            return response()->json(['success' => false, 'message' => 'Tracking number or shipment ID required.'], 400);
        }

        $tracking = $query->latest('last_polled_at')->first();

        if (!$tracking && $number) {
            // Auto-fetch if not in database yet
            try {
                $tracking = TrackingManager::syncTracking(
                    trackingNumber: $number,
                    type: $request->get('type', 'container')
                );
            } catch (Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Tracking record not found.'], 404);
            }
        }

        if (!$tracking) {
            return response()->json(['success' => false, 'message' => 'Tracking record not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'tracking' => $tracking,
            'status_badge' => $tracking->status_badge,
            'events' => $tracking->events,
        ]);
    }

    /**
     * Add manual milestone / operator note.
     */
    public function addManualEvent(Request $request)
    {
        $request->validate([
            'shipment_tracking_id' => 'required|exists:shipment_trackings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date_time' => 'required|date',
            'location_name' => 'nullable|string|max:255',
            'event_classifier' => 'required|in:ACT,EST,PLN',
        ]);

        $tracking = ShipmentTracking::findOrFail($request->shipment_tracking_id);

        $event = TrackingEvent::create([
            'shipment_tracking_id' => $tracking->id,
            'event_id' => 'MAN-' . strtoupper(uniqid()),
            'event_type' => 'SHIPMENT',
            'event_classifier' => $request->event_classifier,
            'event_code' => 'NOTE',
            'event_date_time' => $request->event_date_time,
            'title' => $request->title,
            'description' => $request->description,
            'location_name' => $request->location_name,
            'equipment_reference' => $tracking->tracking_number,
            'is_manual' => true,
            'is_current' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Manual event logged successfully.',
            'event' => $event,
            'events' => $tracking->fresh()->events,
        ]);
    }
}

