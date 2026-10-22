<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class MaerskDriver extends AbstractCarrierDriver
{
    public function getCarrierCode(): string
    {
        return 'maersk';
    }

    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $start = microtime(true);
        $cleanNumber = strtoupper(trim($trackingNumber));
        $config = $this->config ?: CarrierApiConfig::getCarrier('maersk');

        if (!$config || !$config->is_active) {
            throw new Exception("Maersk tracking integration is disabled in Carrier Settings.");
        }

        if (!$config->hasCredentials()) {
            throw new Exception("Maersk API credentials (Consumer Key) are not configured yet. Please configure your Consumer Key in Super Admin > Carrier Tracking APIs to fetch live data from Maersk.");
        }

        $baseUrl = rtrim($config->base_url ?: 'https://api.maersk.com', '/');
        $consumerKey = $config->api_key ?: $config->client_id;

        $param = match ($type) {
            'booking' => 'carrierBookingReference',
            'mbl', 'bol' => 'transportDocumentReference',
            default => 'equipmentReference',
        };

        $url = "{$baseUrl}/track-and-trace/v2/events?{$param}={$cleanNumber}";

        $headers = [
            'Accept' => 'application/json',
            'Consumer-Key' => $consumerKey,
            'API-Version' => '2',
        ];

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
                throw new Exception("No container or document events found on Maersk API for: {$cleanNumber}");
            }

            if (!$response->successful()) {
                $errorMsg = $response->json('message') ?? $response->json('error') ?? $response->body();
                throw new Exception("Maersk API returned HTTP {$response->status()}: " . substr((string)$errorMsg, 0, 150));
            }

            $json = $response->json();
            if (empty($json) || !is_array($json)) {
                throw new Exception("Maersk API returned no events for: {$cleanNumber}");
            }

            return $this->parseMaerskResponse($json, $cleanNumber, $type, false);
        } catch (Throwable $e) {
            if ($e instanceof Exception && str_starts_with($e->getMessage(), 'Maersk') || str_starts_with($e->getMessage(), 'No container')) {
                throw $e;
            }
            throw new Exception("Maersk API request failed: " . $e->getMessage());
        }
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        $config = $this->config ?: CarrierApiConfig::getCarrier('maersk');

        if (!$config || !$config->hasCredentials()) {
            return [
                'success' => false,
                'message' => 'Maersk Consumer Key missing. Please enter Consumer Key from developer.maersk.com.',
                'status_code' => 400,
                'latency_ms' => 0,
                'details' => [
                    'portal_url' => 'https://developer.maersk.com',
                    'status' => 'Consumer Key Required',
                    'environment' => $config->environment ?? 'sandbox',
                ],
            ];
        }

        try {
            $baseUrl = rtrim($config->base_url ?: 'https://api.maersk.com', '/');
            $consumerKey = $config->api_key ?: $config->client_id;

            $response = Http::withHeaders([
                'Consumer-Key' => $consumerKey,
            ])->timeout(8)->get("{$baseUrl}/");

            $durationMs = (int) round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->status() < 500,
                'message' => $response->successful() 
                    ? 'Connected successfully to Maersk Developer Gateway.' 
                    : "Maersk Gateway responded with HTTP {$response->status()}",
                'status_code' => $response->status(),
                'latency_ms' => $durationMs,
                'details' => [
                    'environment' => $config->environment,
                    'base_url' => $baseUrl,
                    'consumer_key_configured' => !empty($consumerKey),
                ],
            ];
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            return [
                'success' => false,
                'message' => 'Connection to Maersk failed: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => $durationMs,
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    protected function parseMaerskResponse(array $payload, string $trackingNumber, string $type, bool $isSim): TrackingResultDTO
    {
        $events = [];

        foreach ($payload as $item) {
            $events[] = new TrackingEventDTO(
                eventId: $item['eventID'] ?? null,
                eventType: $item['eventType'] ?? 'EQUIPMENT',
                eventClassifier: $item['eventClassifierCode'] ?? 'ACT',
                eventCode: $item['equipmentEventTypeCode'] ?? ($item['transportEventTypeCode'] ?? null),
                eventDateTime: $item['eventDateTime'] ?? now()->toDateTimeString(),
                title: $item['equipmentEventTypeCode'] ?? ($item['eventType'] ?? 'Maersk Milestone'),
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
            carrierCode: 'maersk',
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
