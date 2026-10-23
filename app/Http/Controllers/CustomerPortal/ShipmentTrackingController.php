<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\OceanImport;
use App\Models\ShipmentTracking;
use App\Services\Tracking\TrackingManager;
use Illuminate\Http\Request;

class ShipmentTrackingController extends Controller
{
    public function index(Request $request)
    {
        $tpId = auth()->user()->trade_partner_id;
        $shipments = OceanImport::with(['portOfLoading', 'portOfDischarge', 'vessel'])
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($q) use ($tpId) {
                $q->where('dm_shipper_id', $tpId)
                  ->orWhere('dm_consignee_id', $tpId)
                  ->orWhere('dm_customer_id', $tpId);
            })->latest()->paginate(15);

        if ($request->ajax()) {
            return view('customer-portal.partials.shipment-rows', compact('shipments'));
        }

        return view('customer-portal.shipments', compact('shipments'));
    }

    public function show($id)
    {
        $tpId = auth()->user()->trade_partner_id;
        $shipment = OceanImport::with(['portOfLoading', 'portOfDischarge', 'vessel', 'containers'])
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($q) use ($tpId) {
                $q->where('dm_shipper_id', $tpId)
                  ->orWhere('dm_consignee_id', $tpId)
                  ->orWhere('dm_customer_id', $tpId);
            })->findOrFail($id);

        $containerNo = $shipment->containers->first()->container_no ?? ($shipment->mbl_no ?? null);
        $tracking = null;

        if ($containerNo) {
            $tracking = ShipmentTracking::with(['events'])
                ->where('trackable_type', OceanImport::class)
                ->where('trackable_id', $shipment->id)
                ->latest('last_polled_at')
                ->first();

            if (!$tracking) {
                try {
                    $tracking = TrackingManager::syncTracking(
                        trackingNumber: $containerNo,
                        type: 'container',
                        options: [
                            'origin_name' => $shipment->portOfLoading->name ?? null,
                            'origin_locode' => $shipment->portOfLoading->code ?? null,
                            'destination_name' => $shipment->portOfDischarge->name ?? null,
                            'destination_locode' => $shipment->portOfDischarge->code ?? null,
                            'vessel_name' => $shipment->vessel->name ?? null,
                            'voyage_no' => $shipment->voyage ?? null,
                            'etd' => $shipment->etd ?? null,
                            'eta' => $shipment->eta ?? null,
                        ],
                        shipment: $shipment
                    );
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        }

        return view('customer-portal.shipment-detail', compact('shipment', 'tracking'));
    }
}
