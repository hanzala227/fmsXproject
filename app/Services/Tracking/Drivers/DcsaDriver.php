<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class DcsaDriver extends AbstractCarrierDriver
{
    public function getCarrierCode(): string
    {
        return 'dcsa';
    }

    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $start = microtime(true);
        $cleanNumber = strtoupper(trim($trackingNumber));
        $config = $this->config ?: CarrierApiConfig::getCarrier('dcsa');

        if (!$config || !$config->is_active) {
            throw new Exception("DCSA Gateway tracking is disabled in Carrier Settings.");
        }

        if (!$config->hasCredentials()) {
            throw new Exception("DCSA Gateway API credentials (Token / Key) are not configured. Please enter your credentials in Super Admin > Carrier Tracking APIs to fetch live data.");
        }

        $baseUrl = rtrim($config->base_url ?: 'https://api.dcsa.org', '/');
        $param = match ($type) {
            'booking' => 'carrierBookingReference',
            'mbl', 'bol' => 'transportDocumentReference',
            default => 'equipmentReference',
        };

        $url = "{$baseUrl}/v2/events?{$param}={$cleanNumber}";

        $headers = [
            'Accept' => 'application/json',
            'API-Version' => '2',
        ];

        if (!empty($config->api_key)) {
            $headers['Authorization'] = 'Bearer ' . $config->api_key;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            $this->logApiCall(
                endpoint: $url,
                method: 'GET',
                trackingNumber: $cleanNumber,
                requestPayload: ['param' => $param, 'value' => $cleanNumber],
                statusCode: $response->status(),
                responsePayload: $response->json(),
                durationMs: $durationMs,
                errorMessage: $response->successful() ? null : $response->body()
            );

            if ($response->status() === 404) {
                throw new Exception("No container or document events found on DCSA Gateway for: {$cleanNumber}");
            }

            if (!$response->successful()) {
                throw new Exception("DCSA Gateway returned HTTP {$response->status()}: " . substr((string)$response->body(), 0, 150));
            }

            $json = $response->json();
            if (empty($json) || !is_array($json)) {
                throw new Exception("DCSA Gateway returned no events for: {$cleanNumber}");
            }

            return $this->parseDcsaResponse($json, $cleanNumber, $type, false);
        } catch (Throwable $e) {
            if ($e instanceof Exception && (str_starts_with($e->getMessage(), 'DCSA') || str_starts_with($e->getMessage(), 'No container'))) {
                throw $e;
            }
            throw new Exception("DCSA Gateway request failed: " . $e->getMessage());
        }
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        $config = $this->config ?: CarrierApiConfig::getCarrier('dcsa');

        if (!$config || !$config->hasCredentials()) {
            return [
                'success' => false,
                'message' => 'DCSA Gateway credentials missing. Please enter Bearer Token / API Key in Super Admin.',
                'status_code' => 400,
                'latency_ms' => 0,
                'details' => [
                    'standard' => 'DCSA Interface Standards v2.2 / v3.0',
                    'docs' => 'https://reference.dcsa.org',
                    'status' => 'Token Required',
                ],
            ];
        }

        try {
            $baseUrl = rtrim($config->base_url ?: 'https://api.dcsa.org', '/');
            $response = Http::timeout(8)->get($baseUrl);
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->status() < 500,
                'message' => 'Connected successfully to DCSA Standard Gateway.',
                'status_code' => $response->status(),
                'latency_ms' => $durationMs,
                'details' => ['base_url' => $baseUrl],
            ];
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            return [
                'success' => false,
                'message' => 'Connection to DCSA Gateway failed: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => $durationMs,
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    protected function parseDcsaResponse(array $payload, string $trackingNumber, string $type, bool $isSim): TrackingResultDTO
    {
        $events = [];

        foreach ($payload as $item) {
            $events[] = new TrackingEventDTO(
                eventId: $item['eventID'] ?? null,
                eventType: $item['eventType'] ?? 'EQUIPMENT',
                eventClassifier: $item['eventClassifierCode'] ?? 'ACT',
                eventCode: $item['equipmentEventTypeCode'] ?? ($item['transportEventTypeCode'] ?? null),
                eventDateTime: $item['eventDateTime'] ?? now()->toDateTimeString(),
                title: $item['equipmentEventTypeCode'] ?? ($item['eventType'] ?? 'DCSA Event'),
                description: $item['eventDescription'] ?? null,
                locationName: $item['eventLocation']['locationName'] ?? null,
                locationLocode: $item['eventLocation']['UNLocationCode'] ?? null,
                facilityName: $item['eventLocation']['facilityName'] ?? null,
                vesselName: $item['transportCall']['vessel']['vesselName'] ?? null,
                voyageNo: $item['transportCall']['carrierVoyageNumber'] ?? null,
                equipmentReference: $item['equipmentReference'] ?? $trackingNumber,
                isCurrent: false
            );
        }

        $journey = $this->calculateJourneyProgress($events);

        return new TrackingResultDTO(
            carrierCode: 'dcsa',
            trackingNumber: $trackingNumber,
            trackingType: $type,
            currentStatus: $journey['status'],
            statusDescription: $journey['description'],
            progressPercentage: $journey['progress'],
            events: $events,
            rawPayload: $payload,
            isSimulation: $isSim
        );
    }
}
