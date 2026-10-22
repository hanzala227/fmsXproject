<?php

namespace App\Services\Tracking\Drivers;

use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Carbon\Carbon;

class MockTrackingDriver extends AbstractCarrierDriver
{
    protected string $carrier = 'mock';

    public function __construct(string $carrier = 'mock')
    {
        $this->carrier = $carrier;
        parent::__construct(null);
    }

    public function getCarrierCode(): string
    {
        return $this->carrier;
    }

    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $start = microtime(true);
        $cleanNumber = strtoupper(trim($trackingNumber));

        // Detect carrier name
        $carrierName = match ($this->carrier) {
            'hapag_lloyd' => 'Hapag-Lloyd',
            'maersk' => 'Maersk Line',
            default => 'DCSA Ocean Carrier',
        };

        // Determine route & dates (use options from shipment if passed)
        $pol = $options['origin_name'] ?? 'Shanghai Port';
        $polCode = $options['origin_locode'] ?? 'CNSHA';
        $pod = $options['destination_name'] ?? 'Port of Los Angeles';
        $podCode = $options['destination_locode'] ?? 'USLAX';
        $vessel = $options['vessel_name'] ?? 'MAERSK MC-KINNEY MOLLER';
        $voyage = $options['voyage_no'] ?? '2401E';

        $baseDate = isset($options['etd']) ? Carbon::parse($options['etd']) : Carbon::now()->subDays(6);
        $etaDate = isset($options['eta']) ? Carbon::parse($options['eta']) : $baseDate->copy()->addDays(14);

        // Generate DCSA event sequence
        $events = [
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '1'), 0, 8),
                eventType: 'SHIPMENT',
                eventClassifier: 'ACT',
                eventCode: 'CONF',
                eventDateTime: $baseDate->copy()->subDays(3)->format('Y-m-d H:i:s'),
                title: 'Booking Confirmed',
                description: "Carrier booking confirmed with {$carrierName}.",
                locationName: $pol,
                locationLocode: $polCode,
                facilityName: "Terminal 1 Gate",
                vesselName: null,
                voyageNo: null,
                equipmentReference: $cleanNumber,
                isCurrent: false
            ),
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '2'), 0, 8),
                eventType: 'EQUIPMENT',
                eventClassifier: 'ACT',
                eventCode: 'GTIN',
                eventDateTime: $baseDate->copy()->subDays(1)->format('Y-m-d H:i:s'),
                title: 'Gate In (Received at Terminal)',
                description: "Full container received at terminal gate, verified gross mass (VGM) processed.",
                locationName: $pol,
                locationLocode: $polCode,
                facilityName: "Yangshan Deepwater Port Terminal",
                vesselName: null,
                voyageNo: null,
                equipmentReference: $cleanNumber,
                isCurrent: false
            ),
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '3'), 0, 8),
                eventType: 'EQUIPMENT',
                eventClassifier: 'ACT',
                eventCode: 'LOAD',
                eventDateTime: $baseDate->copy()->format('Y-m-d H:i:s'),
                title: 'Loaded on Vessel',
                description: "Container stowed on board vessel {$vessel} (Voyage {$voyage}).",
                locationName: $pol,
                locationLocode: $polCode,
                facilityName: "Yangshan Berth 4",
                vesselName: $vessel,
                voyageNo: $voyage,
                equipmentReference: $cleanNumber,
                isCurrent: false
            ),
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '4'), 0, 8),
                eventType: 'TRANSPORT',
                eventClassifier: 'ACT',
                eventCode: 'DEPA',
                eventDateTime: $baseDate->copy()->addHours(6)->format('Y-m-d H:i:s'),
                title: 'Vessel Departed',
                description: "Vessel departed origin port {$pol}. Bound for {$pod}.",
                locationName: $pol,
                locationLocode: $polCode,
                facilityName: null,
                vesselName: $vessel,
                voyageNo: $voyage,
                equipmentReference: $cleanNumber,
                isCurrent: true
            ),
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '5'), 0, 8),
                eventType: 'TRANSPORT',
                eventClassifier: 'EST',
                eventCode: 'ARRI',
                eventDateTime: $etaDate->format('Y-m-d H:i:s'),
                title: 'Vessel Arrival (Estimated)',
                description: "Estimated vessel arrival at destination port {$pod}.",
                locationName: $pod,
                locationLocode: $podCode,
                facilityName: "Pier 400 APM Terminals",
                vesselName: $vessel,
                voyageNo: $voyage,
                equipmentReference: $cleanNumber,
                isCurrent: false
            ),
            new TrackingEventDTO(
                eventId: 'EVT-' . substr(md5($cleanNumber . '6'), 0, 8),
                eventType: 'EQUIPMENT',
                eventClassifier: 'EST',
                eventCode: 'DISC',
                eventDateTime: $etaDate->copy()->addHours(18)->format('Y-m-d H:i:s'),
                title: 'Discharge from Vessel (Estimated)',
                description: "Container scheduled to be discharged at destination container yard.",
                locationName: $pod,
                locationLocode: $podCode,
                facilityName: "Pier 400 APM Terminals",
                vesselName: $vessel,
                voyageNo: $voyage,
                equipmentReference: $cleanNumber,
                isCurrent: false
            ),
        ];

        $journey = $this->calculateJourneyProgress($events);

        $durationMs = (int) round((microtime(true) - $start) * 1000);

        $this->logApiCall(
            endpoint: "/events?equipmentReference={$cleanNumber}",
            method: 'GET',
            trackingNumber: $cleanNumber,
            requestPayload: ['tracking_number' => $cleanNumber, 'carrier' => $this->carrier, 'simulation' => true],
            statusCode: 200,
            responsePayload: ['events_count' => count($events), 'status' => $journey['status']],
            durationMs: $durationMs
        );

        return new TrackingResultDTO(
            carrierCode: $this->carrier,
            trackingNumber: $cleanNumber,
            trackingType: $type,
            currentStatus: 'in_transit',
            statusDescription: $journey['description'],
            progressPercentage: 55,
            originLocode: $polCode,
            originName: $pol,
            destinationLocode: $podCode,
            destinationName: $pod,
            vesselName: $vessel,
            voyageNo: $voyage,
            etd: $baseDate->format('Y-m-d H:i:s'),
            atd: $baseDate->copy()->addHours(6)->format('Y-m-d H:i:s'),
            eta: $etaDate->format('Y-m-d H:i:s'),
            ata: null,
            events: $events,
            rawPayload: [
                'provider' => $carrierName,
                'source' => 'DCSA v2.2 Simulation Engine',
                'pol' => $pol,
                'pod' => $pod,
                'vessel' => $vessel,
            ],
            isSimulation: true
        );
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        usleep(120000); // 120ms latency simulation
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        return [
            'success' => true,
            'message' => 'Connected successfully to ' . ucfirst($this->carrier) . ' Tracking API Gateway.',
            'status_code' => 200,
            'latency_ms' => $durationMs,
            'details' => [
                'carrier' => $this->carrier,
                'api_standard' => 'DCSA Track & Trace v2.2 / v3.0',
                'mode' => 'Simulation & Sandbox Gateway Ready',
                'supported_lookups' => ['Container (BIC)', 'Master B/L', 'Carrier Booking Reference'],
            ],
        ];
    }
}

