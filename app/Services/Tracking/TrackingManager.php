<?php

namespace App\Services\Tracking;

use App\Models\CarrierApiConfig;
use App\Models\OceanImport;
use App\Models\OceanImportContainer;
use App\Models\OceanExport;
use App\Models\ShipmentTracking;
use App\Models\TrackingEvent;
use App\Services\Tracking\Contracts\CarrierTrackingInterface;
use App\Services\Tracking\Drivers\DcsaDriver;
use App\Services\Tracking\Drivers\HapagLloydDriver;
use App\Services\Tracking\Drivers\MaerskDriver;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TrackingManager
{
    /**
     * Resolve the appropriate carrier driver.
     */
    public static function driver(?string $carrierCode = null, ?string $trackingNumber = null): CarrierTrackingInterface
    {
        $code = strtolower((string) $carrierCode);

        // Auto-detect from container prefix if not explicitly specified
        if (empty($code) && !empty($trackingNumber)) {
            $code = static::detectCarrierFromPrefix($trackingNumber);
        }

        if (empty($code)) {
            $code = 'dcsa';
        }

        $config = CarrierApiConfig::getCarrier($code);

        return match ($code) {
            'hapag_lloyd', 'hlag', 'hapag' => new HapagLloydDriver($config),
            'maersk', 'maeu', 'msku' => new MaerskDriver($config),
            default => new DcsaDriver($config),
        };
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
            $matchedContainer = OceanImportContainer::with('oceanImport')->where('container_no', $cleanNumber)->first();
            if ($matchedContainer && $matchedContainer->oceanImport) {
                $shipment = $matchedContainer->oceanImport;
            } else {
                $matchedImport = OceanImport::where('mbl_no', $cleanNumber)->orWhere('file_no', $cleanNumber)->first();
                if ($matchedImport) {
                    $shipment = $matchedImport;
                }
            }
        }

        // If matched shipment exists, grab real shipment routing context
        if ($shipment && empty($options['origin_name'])) {
            $options['origin_name'] = $shipment->portOfLoading->name ?? ($shipment->pol_name ?? null);
            $options['origin_locode'] = $shipment->portOfLoading->code ?? null;
            $options['destination_name'] = $shipment->portOfDischarge->name ?? ($shipment->pod_name ?? null);
            $options['destination_locode'] = $shipment->portOfDischarge->code ?? null;
            $options['vessel_name'] = $shipment->vessel->name ?? ($shipment->vessel_name ?? null);
            $options['voyage_no'] = $shipment->voyage ?? null;
            $options['etd'] = $shipment->etd ?? null;
            $options['eta'] = $shipment->eta ?? null;
        }

        // Resolve carrier driver and execute API call
        $driver = static::driver($carrierCode, $cleanNumber);
        $result = $driver->track($cleanNumber, $type, $options);

        // If driver returned empty events or invalid status
        if (empty($result->events)) {
            throw new Exception("No live milestone events returned by carrier API for: {$cleanNumber}");
        }

        $companyId = auth()->check() ? auth()->user()->company_id : ($shipment->company_id ?? null);

        // Find or create ShipmentTracking record
        $query = ShipmentTracking::where('tracking_number', $cleanNumber);
        if ($shipment) {
            $query->where('trackable_type', get_class($shipment))
                  ->where('trackable_id', $shipment->getKey());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        $tracking = $query->first() ?: new ShipmentTracking();

        $tracking->fill([
            'company_id' => $companyId,
            'carrier_code' => $result->carrierCode,
            'tracking_type' => $result->trackingType,
            'tracking_number' => $cleanNumber,
            'current_status' => $result->currentStatus,
            'status_description' => $result->statusDescription,
            'progress_percentage' => $result->progressPercentage,
            'origin_locode' => $result->originLocode ?: ($tracking->origin_locode ?? $options['origin_locode'] ?? null),
            'origin_name' => $result->originName ?: ($tracking->origin_name ?? $options['origin_name'] ?? null),
            'destination_locode' => $result->destinationLocode ?: ($tracking->destination_locode ?? $options['destination_locode'] ?? null),
            'destination_name' => $result->destinationName ?: ($tracking->destination_name ?? $options['destination_name'] ?? null),
            'vessel_name' => $result->vesselName ?: ($tracking->vessel_name ?? $options['vessel_name'] ?? null),
            'voyage_no' => $result->voyageNo ?: ($tracking->voyage_no ?? $options['voyage_no'] ?? null),
            'etd' => $result->etd ?: ($tracking->etd ?? $options['etd'] ?? null),
            'atd' => $result->atd ?: ($tracking->atd ?? null),
            'eta' => $result->eta ?: ($tracking->eta ?? $options['eta'] ?? null),
            'ata' => $result->ata ?: ($tracking->ata ?? null),
            'last_polled_at' => now(),
            'raw_payload' => $result->rawPayload,
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
