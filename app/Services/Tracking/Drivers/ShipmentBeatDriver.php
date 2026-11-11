<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Services\Tracking\Contracts\CarrierTrackingInterface;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class ShipmentBeatDriver extends AbstractCarrierDriver implements CarrierTrackingInterface
{
    protected string $carrierCode = 'shipmentbeat';

    public function __construct(?CarrierApiConfig $config = null)
    {
        $this->config = $config ?: CarrierApiConfig::getCarrier('shipmentbeat');
    }

    public function getCarrierCode(): string
    {
        return 'shipmentbeat';
    }

    /**
     * Map container prefix or carrier name to ShipmentBeat standard carrier code.
     */
    public static function resolveCarrierCode(string $trackingNumber, ?string $providedCarrier = null): string
    {
        $prefix = strtoupper(substr(trim($trackingNumber), 0, 4));

        // Direct BIC prefix mapping to ShipmentBeat codes
        $bicMap = [
            'MSKU' => 'MAEU',
            'MAEU' => 'MAEU',
            'MRKU' => 'MAEU',
            'MSFU' => 'MAEU',
            'MCAU' => 'MAEU',
            'SEGU' => 'MAEU',
            'SUDU' => 'MAEU',
            'PONU' => 'MAEU',
            'MEDU' => 'MSCU',
            'MSCU' => 'MSCU',
            'HLXU' => 'HLCU',
            'HLCU' => 'HLCU',
            'HLBU' => 'HLCU',
            'CPSU' => 'HLCU',
            'UASC' => 'HLCU',
            'CMAU' => 'CMDU',
            'CMDU' => 'CMDU',
            'CGMU' => 'CMDU',
            'APZU' => 'CMDU',
            'COSU' => 'COSU',
            'CHGO' => 'COSU',
            'CBHU' => 'COSU',
            'ONEY' => 'ONEY',
            'KKFU' => 'ONEY',
            'MOLU' => 'ONEY',
            'NYKU' => 'ONEY',
            'EGLV' => 'EGLV',
            'EGHU' => 'EGLV',
            'EISU' => 'EGLV',
            'HDMU' => 'HDMU',
            'ZIMU' => 'ZIMU',
            'ZCSU' => 'ZIMU',
            'YMLU' => 'YMLU',
            'WHLC' => 'WHLC',
            'WHLU' => 'WHLC',
            'PCIU' => 'PCIU',
            'OOLU' => 'OOLU',
            'MATS' => 'MATS',
        ];

        if (isset($bicMap[$prefix])) {
            return $bicMap[$prefix];
        }

        if (!empty($providedCarrier)) {
            $c = strtolower(trim($providedCarrier));
            if (str_contains($c, 'maersk')) return 'MAEU';
            if (str_contains($c, 'msc')) return 'MSCU';
            if (str_contains($c, 'hapag')) return 'HLCU';
            if (str_contains($c, 'cma')) return 'CMDU';
            if (str_contains($c, 'cosco')) return 'COSU';
            if (str_contains($c, 'one') || str_contains($c, 'ocean network')) return 'ONEY';
            if (str_contains($c, 'evergreen')) return 'EGLV';
            if (str_contains($c, 'hmm')) return 'HDMU';
            if (str_contains($c, 'zim')) return 'ZIMU';
            if (str_contains($c, 'yang ming') || str_contains($c, 'yml')) return 'YMLU';
            if (str_contains($c, 'wan hai')) return 'WHLC';
            if (str_contains($c, 'pil')) return 'PCIU';
            if (str_contains($c, 'oocl')) return 'OOLU';
        }

        return 'MAEU'; // Default fallback carrier
    }

    /**
     * Test connection to ShipmentBeat API endpoint.
     */
    public function testConnection(): array
    {
        $config = $this->config ?: CarrierApiConfig::getCarrier('shipmentbeat');
        $apiKey = $config?->api_key ?? env('SHIPMENTBEAT_API_KEY');
        $baseUrl = rtrim($config?->base_url ?? env('SHIPMENTBEAT_BASE_URL', 'https://api.shipmentbeat.com'), '/');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'ShipmentBeat API Key is not configured.',
                'latency_ms' => 0,
                'details' => [],
            ];
        }

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->timeout(10)->get("{$baseUrl}/api/v1/carriers");

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();
            $data = $response->json();

            $this->logApiCall(
                endpoint: "{$baseUrl}/api/v1/carriers",
                method: 'GET',
                trackingNumber: null,
                requestPayload: null,
                statusCode: $statusCode,
                responsePayload: $data,
                durationMs: $latencyMs
            );

            if ($response->successful() && ($data['success'] ?? false)) {
                $carriersCount = count($data['data'] ?? []);
                return [
                    'success' => true,
                    'message' => "Successfully connected to ShipmentBeat ({$carriersCount} ocean carriers active).",
                    'latency_ms' => $latencyMs,
                    'details' => [
                        'status' => 'connected',
                        'supported_carriers_count' => $carriersCount,
                        'endpoint' => "{$baseUrl}/api/v1/carriers",
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => $data['msg'] ?? "ShipmentBeat API returned status HTTP {$statusCode}.",
                'latency_ms' => $latencyMs,
                'details' => $data,
            ];
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'success' => false,
                'message' => 'ShipmentBeat connection failed: ' . $e->getMessage(),
                'latency_ms' => $latencyMs,
                'details' => ['exception' => get_class($e)],
            ];
        }
    }

    /**
     * Track cargo by Container, B/L, or Booking number via ShipmentBeat.
     */
    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $cleanNumber = strtoupper(trim($trackingNumber));
        $config = $this->config ?: CarrierApiConfig::getCarrier('shipmentbeat');
        $apiKey = $config?->api_key ?? env('SHIPMENTBEAT_API_KEY');
        $baseUrl = rtrim($config?->base_url ?? env('SHIPMENTBEAT_BASE_URL', 'https://api.shipmentbeat.com'), '/');

        if (empty($apiKey)) {
            throw new Exception("ShipmentBeat API key is not configured in settings or environment.");
        }

        $carrierCode = self::resolveCarrierCode($cleanNumber, $options['carrier_code'] ?? ($options['carrier_name'] ?? null));
        $startTime = microtime(true);

        $trackingNumberType = match (strtolower($type)) {
            'mbl', 'bl', 'bill_of_lading' => 'BillOfLading',
            'booking', 'booking_number' => 'BookingNumber',
            default => 'ContainerNumber',
        };

        try {
            // First check if shipment is already registered in ShipmentBeat
            $searchResponse = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$baseUrl}/api/v1/shipments", [
                'page' => 1,
                'pageSize' => 10,
            ]);

            $existingShipment = null;
            if ($searchResponse->successful()) {
                $list = $searchResponse->json('data.items') ?? ($searchResponse->json('data') ?? []);
                if (is_array($list)) {
                    foreach ($list as $item) {
                        if (strtoupper($item['trackingNumber'] ?? '') === $cleanNumber) {
                            $existingShipment = $item;
                            break;
                        }
                    }
                }
            }

            // If not found in active list, register/create tracking on ShipmentBeat
            if (!$existingShipment) {
                $createPayload = [
                    'carrierCode' => $carrierCode,
                    'trackingNumber' => $cleanNumber,
                    'trackingNumberType' => $trackingNumberType,
                ];

                $createResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(15)->post("{$baseUrl}/api/v1/shipments", $createPayload);

                $createData = $createResponse->json();
                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                $this->logApiCall(
                    endpoint: "{$baseUrl}/api/v1/shipments",
                    method: 'POST',
                    trackingNumber: $cleanNumber,
                    requestPayload: $createPayload,
                    statusCode: $createResponse->status(),
                    responsePayload: $createData,
                    durationMs: $latencyMs
                );

                if ($createResponse->successful() && ($createData['success'] ?? false)) {
                    $existingShipment = $createData['data'] ?? $createData;
                } else {
                    // If error message indicates already exists, parse message
                    $errMsg = $createData['msg'] ?? 'Unable to create tracking on ShipmentBeat';
                    if (!str_contains(strtolower($errMsg), 'already')) {
                        throw new Exception("ShipmentBeat tracking error: {$errMsg}");
                    }
                }
            }

            // Map ShipmentBeat status to FreightX status
            $rawStatus = strtolower($existingShipment['trackingStatus'] ?? ($existingShipment['status'] ?? 'in_transit'));
            $status = match ($rawStatus) {
                'delivered', 'completed' => 'delivered',
                'arrived' => 'arrived',
                'loaded', 'departed', 'in_transit' => 'in_transit',
                'delayed', 'exception' => 'delayed',
                default => 'booked',
            };

            $progress = match ($status) {
                'delivered' => 100,
                'arrived' => 90,
                'in_transit' => 50,
                'delayed' => 45,
                default => 20,
            };

            $events = [];
            if (!empty($existingShipment['events']) && is_array($existingShipment['events'])) {
                foreach ($existingShipment['events'] as $evt) {
                    $events[] = new TrackingEventDTO(
                        eventId: $evt['id'] ?? ('sb_' . uniqid()),
                        eventType: $evt['type'] ?? 'MILESTONE',
                        eventClassifier: $evt['classifier'] ?? 'ACT',
                        eventDateTime: $evt['date'] ?? now()->toIso8601String(),
                        title: $evt['title'] ?? ($evt['description'] ?? 'Carrier Milestone'),
                        description: $evt['description'] ?? null,
                        locationName: $evt['location'] ?? null,
                        locationLocode: $evt['locode'] ?? null,
                        vesselName: $evt['vessel'] ?? null,
                        voyageNo: $evt['voyage'] ?? null
                    );
                }
            } else {
                $events[] = new TrackingEventDTO(
                    eventId: 'sb_evt_init_' . uniqid(),
                    eventType: 'TRACKING_ACTIVE',
                    eventClassifier: 'ACT',
                    eventDateTime: now()->toIso8601String(),
                    title: 'ShipmentBeat Monitoring Active',
                    description: "Container {$cleanNumber} linked to {$carrierCode} via ShipmentBeat",
                    locationName: $options['origin_name'] ?? null
                );
            }

            return new TrackingResultDTO(
                carrierCode: 'shipmentbeat',
                trackingNumber: $cleanNumber,
                trackingType: $type,
                currentStatus: $status,
                statusDescription: "Carrier: {$carrierCode} | Status: " . ucfirst(str_replace('_', ' ', $rawStatus)),
                progressPercentage: $progress,
                originLocode: $options['origin_locode'] ?? null,
                originName: $options['origin_name'] ?? null,
                destinationLocode: $options['destination_locode'] ?? null,
                destinationName: $options['destination_name'] ?? null,
                vesselName: $existingShipment['vessel_name'] ?? ($options['vessel_name'] ?? null),
                voyageNo: $existingShipment['voyage_no'] ?? ($options['voyage_no'] ?? null),
                etd: isset($existingShipment['etd']) ? date('Y-m-d H:i:s', strtotime($existingShipment['etd'])) : null,
                eta: isset($existingShipment['eta']) ? date('Y-m-d H:i:s', strtotime($existingShipment['eta'])) : null,
                events: $events,
                rawPayload: $existingShipment ?: ['tracking_number' => $cleanNumber, 'carrier' => $carrierCode],
                isSimulation: false
            );
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logApiCall(
                endpoint: "{$baseUrl}/api/v1/shipments",
                method: 'POST',
                trackingNumber: $cleanNumber,
                requestPayload: ['trackingNumber' => $cleanNumber],
                statusCode: 500,
                responsePayload: ['error' => $e->getMessage()],
                durationMs: $latencyMs,
                errorMessage: $e->getMessage()
            );

            throw new Exception("ShipmentBeat: " . $e->getMessage(), 0, $e);
        }
    }
}
