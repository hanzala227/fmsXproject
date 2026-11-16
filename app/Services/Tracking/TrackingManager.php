<?php

namespace App\Services\Tracking;

use App\Models\CarrierApiConfig;
use App\Models\OceanImport;
use App\Models\OceanImportContainer;
use App\Models\OceanExport;
use App\Models\ShipmentTracking;
use App\Models\TrackingEvent;
use App\Services\Tracking\Contracts\CarrierTrackingInterface;
use App\Services\Tracking\Drivers\ShipmentBeatDriver;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TrackingManager
{
    /**
     * Resolve the appropriate carrier driver (ShipmentBeat as unified ocean aggregator).
     */
    public static function driver(?string $carrierCode = null, ?string $trackingNumber = null): CarrierTrackingInterface
    {
        $config = CarrierApiConfig::getCarrier('shipmentbeat');

        return new ShipmentBeatDriver($config);
    }

    /**
     * Auto-detect carrier code from standard ISO container prefix (BIC code).
     */
    public static function detectCarrierFromPrefix(string $trackingNumber): string
    {
        $prefix = strtoupper(substr(trim($trackingNumber), 0, 4));

        // Hapag-Lloyd BIC prefixes
        if (in_array($prefix, ['HLXU', 'HLCU', 'HLBU', 'CPSU', 'UASC'])) {
            return 'hapag_lloyd';
        }

        // Maersk BIC prefixes
        if (in_array($prefix, ['MSKU', 'MAEU', 'MRKU', 'MSFU', 'MCAU', 'SEGU', 'SUDU', 'PONU'])) {
            return 'maersk';
        }

        return 'dcsa';
    }

    /**
     * Perform live tracking sync via real carrier API and persist records in database.
     *
     * @throws Exception when tracking not found or carrier API fails
     */
    public static function syncTracking(
        string $trackingNumber,
        string $type = 'container',
        array $options = [],
        ?Model $shipment = null,
        ?string $carrierCode = null
    ): ShipmentTracking {
        $cleanNumber = strtoupper(trim($trackingNumber));

        if (strlen($cleanNumber) < 4) {
            throw new Exception("Please provide a valid tracking or container number (minimum 4 characters).");
        }

        // Check if this container or MBL belongs to an existing shipment in database
        if (!$shipment) {
            $matchedImportContainer = OceanImportContainer::with(['oceanImport.pol', 'oceanImport.pod', 'oceanImport.carrier'])->where('container_no', $cleanNumber)->first();
            if ($matchedImportContainer && $matchedImportContainer->oceanImport) {
                $shipment = $matchedImportContainer->oceanImport;
            } else {
                $matchedExportContainer = \App\Models\OceanExportContainer::with(['oceanExport.pol', 'oceanExport.pod', 'oceanExport.carrier'])->where('container_no', $cleanNumber)->first();
                if ($matchedExportContainer && $matchedExportContainer->oceanExport) {
                    $shipment = $matchedExportContainer->oceanExport;
                } else {
                    $matchedImport = OceanImport::with(['pol', 'pod', 'carrier'])->where('mbl_no', $cleanNumber)->orWhere('file_no', $cleanNumber)->first();
                    if ($matchedImport) {
                        $shipment = $matchedImport;
                    } else {
                        $matchedExport = \App\Models\OceanExport::with(['pol', 'pod', 'carrier'])->where('mbl_no', $cleanNumber)->orWhere('file_no', $cleanNumber)->first();
                        if ($matchedExport) {
                            $shipment = $matchedExport;
                        }
                    }
                }
            }
        }

        // Pass carrier context in options if provided
        if (!empty($carrierCode) && strtolower($carrierCode) !== 'shipmentbeat' && empty($options['carrier_code'])) {
            $options['carrier_code'] = $carrierCode;
        }

        // Resolve carrier driver and execute API call
        $driver = static::driver($carrierCode, $cleanNumber);
        $result = $driver->track($cleanNumber, $type, $options);

        // If driver returned empty events, synthesize a baseline registration event
        if (empty($result->events)) {
            $result->events = [
                new \App\Services\Tracking\DTO\TrackingEventDTO(
                    eventId: 'tr_init_' . md5($cleanNumber . time()),
                    eventType: 'TRACKING_REGISTERED',
                    eventClassifier: 'EST',
                    eventCode: 'BOOKED',
                    eventDateTime: now()->toDateTimeString(),
                    title: 'Tracking Registered on Carrier Network',
                    description: "Container {$cleanNumber} registered with carrier. Telemetry synchronization initiated.",
                    locationName: $result->originName ?: 'Carrier Terminal',
                    locationLocode: $result->originLocode,
                    vesselName: $result->vesselName,
                    voyageNo: $result->voyageNo
                )
            ];
        }

        $companyId = auth()->check() ? auth()->user()->company_id : ($shipment->company_id ?? null);

        // Find or create ShipmentTracking record
        $tracking = ShipmentTracking::where('tracking_number', $cleanNumber)
            ->when($shipment, fn($q) => $q->where('trackable_type', get_class($shipment))->where('trackable_id', $shipment->getKey()))
            ->first() ?: ShipmentTracking::where('tracking_number', $cleanNumber)->first() ?: new ShipmentTracking();

        $statusToSet = $result->currentStatus ?: 'booked';
        $progressToSet = $result->progressPercentage ?? 0;

        $originLocode = $result->originLocode ?: ($shipment?->pol?->code ?? $tracking->origin_locode);
        $originName = $result->originName ?: ($shipment?->pol?->name ?? $tracking->origin_name);
        $destinationLocode = $result->destinationLocode ?: ($shipment?->pod?->code ?? $tracking->destination_locode);
        $destinationName = $result->destinationName ?: ($shipment?->pod?->name ?? $tracking->destination_name);
        $vesselName = $result->vesselName ?: ($shipment?->vessel?->name ?? ($shipment?->vessel_name ?? $tracking->vessel_name));
        $voyageNo = $result->voyageNo ?: ($shipment?->voyage ?? ($shipment?->voyage_no ?? $tracking->voyage_no));
        $etd = $result->etd ?: ($shipment?->etd ?? $tracking->etd);
        $atd = $result->atd ?: ($shipment?->atd ?? $tracking->atd);
        $eta = $result->eta ?: ($shipment?->eta ?? $tracking->eta);
        $ata = $result->ata ?: ($shipment?->ata ?? $tracking->ata);

        // If carrier reported 'booked' or 0 progress, but container has actual gate-in or loaded dates on shipment:
        if ($statusToSet === 'booked' && $shipment) {
            $exportCnt = \App\Models\OceanExportContainer::where('container_no', $cleanNumber)->first();
            $importCnt = OceanImportContainer::where('container_no', $cleanNumber)->first();
            if ($exportCnt && !empty($exportCnt->gate_in_date)) {
                $statusToSet = 'loaded';
                $progressToSet = 35;
            } elseif ($importCnt && !empty($importCnt->gate_in_date)) {
                $statusToSet = 'loaded';
                $progressToSet = 35;
            }
        }

        $tracking->fill([
            'company_id' => $companyId ?: $tracking->company_id,
            'carrier_code' => $result->carrierCode ?: ($shipment?->carrier?->code ?? $tracking->carrier_code),
            'tracking_type' => $result->trackingType ?: ($tracking->tracking_type ?: 'container'),
            'tracking_number' => $cleanNumber,
            'current_status' => $statusToSet,
            'status_description' => $result->statusDescription ?: $tracking->status_description,
            'progress_percentage' => $progressToSet,
            'origin_locode' => $originLocode,
            'origin_name' => $originName,
            'destination_locode' => $destinationLocode,
            'destination_name' => $destinationName,
            'vessel_name' => $vesselName,
            'voyage_no' => $voyageNo,
            'etd' => $etd,
            'atd' => $atd,
            'eta' => $eta,
            'ata' => $ata,
            'last_polled_at' => now(),
            'raw_payload' => $result->rawPayload ?: $tracking->raw_payload,
        ]);

        if ($shipment) {
            $tracking->trackable_type = get_class($shipment);
            $tracking->trackable_id = $shipment->getKey();
        }

        $tracking->save();

        // Sync real DCSA events
        foreach ($result->events as $eventDto) {
            $data = $eventDto->toArray();

            $existing = null;
            if (!empty($data['event_id'])) {
                $existing = TrackingEvent::where('shipment_tracking_id', $tracking->id)
                    ->where('event_id', $data['event_id'])
                    ->first();
            }

            if (!$existing) {
                $existing = TrackingEvent::where('shipment_tracking_id', $tracking->id)
                    ->where('event_code', $data['event_code'])
                    ->where('event_date_time', $data['event_date_time'])
                    ->first();
            }

            if ($existing) {
                $existing->update($data);
            } else {
                $data['shipment_tracking_id'] = $tracking->id;
                TrackingEvent::create($data);
            }
        }

        return $tracking->load(['events', 'timelineEvents']);
    }
}
