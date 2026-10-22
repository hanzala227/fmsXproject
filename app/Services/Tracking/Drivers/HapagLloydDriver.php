<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class HapagLloydDriver extends AbstractCarrierDriver
{
    public function getCarrierCode(): string
    {
        return 'hapag_lloyd';
    }

    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO
    {
        $start = microtime(true);
        $cleanNumber = strtoupper(trim($trackingNumber));
        $config = $this->config ?: CarrierApiConfig::getCarrier('hapag_lloyd');

        if (!$config || !$config->is_active) {
            throw new Exception("Hapag-Lloyd tracking integration is disabled in Carrier Settings.");
        }

        if (!$config->hasCredentials()) {
            throw new Exception("Hapag-Lloyd API credentials (Client ID / Client Secret) are not configured yet. Please configure them in Super Admin > Carrier Tracking APIs to fetch live data.");
        }

        $baseUrl = rtrim($config->base_url ?: 'https://api.hlag.com', '/');
        $token = $this->getAccessToken($config);

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

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }
        if ($config->api_key) {
            $headers['X-IBM-Client-Id'] = $config->api_key;
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
                throw new Exception("No container or document events found on Hapag-Lloyd API for: {$cleanNumber}");
            }

            if (!$response->successful()) {
                $errorMsg = $response->json('message') ?? $response->json('error') ?? $response->body();
                throw new Exception("Hapag-Lloyd API returned HTTP {$response->status()}: " . substr((string)$errorMsg, 0, 150));
            }

            $json = $response->json();
            if (empty($json) || !is_array($json)) {
                throw new Exception("Hapag-Lloyd API returned no events for: {$cleanNumber}");
            }

            return $this->parseDcsaResponse($json, $cleanNumber, $type, false);
        } catch (Throwable $e) {
            if ($e instanceof Exception && str_starts_with($e->getMessage(), 'Hapag-Lloyd') || str_starts_with($e->getMessage(), 'No container')) {
                throw $e;
            }
            throw new Exception("Hapag-Lloyd API request failed: " . $e->getMessage());
        }
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        $config = $this->config ?: CarrierApiConfig::getCarrier('hapag_lloyd');

        if (!$config || !$config->hasCredentials()) {
            return [
                'success' => false,
                'message' => 'Hapag-Lloyd API credentials missing. Please enter Client ID and Client Secret from developer.hlag.com.',
                'status_code' => 400,
                'latency_ms' => 0,
                'details' => [
                    'portal_url' => 'https://www.hapag-lloyd.com/en/services-information/data-solutions/api-portal.html',
                    'status' => 'Credentials Required',
                    'environment' => $config->environment ?? 'sandbox',
                ],
            ];
        }

        try {
            $baseUrl = rtrim($config->base_url ?: 'https://api.hlag.com', '/');
            $response = Http::timeout(8)->get("{$baseUrl}/");
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->status() < 500,
                'message' => $response->successful() 
                    ? 'Connected successfully to Hapag-Lloyd API Portal.' 
                    : "Hapag-Lloyd responded with HTTP {$response->status()}",
                'status_code' => $response->status(),
                'latency_ms' => $durationMs,
                'details' => [
                    'environment' => $config->environment,
                    'base_url' => $baseUrl,
                    'auth_configured' => !empty($config->client_id),
                ],
            ];
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            return [
                'success' => false,
                'message' => 'Connection to Hapag-Lloyd failed: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => $durationMs,
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    protected function getAccessToken(CarrierApiConfig $config): ?string
    {
        if (empty($config->client_id) || empty($config->client_secret)) {
            return null;
        }

        $tokenUrl = $config->settings['token_url'] ?? 'https://api.hlag.com/oauth2/token';

        $response = Http::asForm()->timeout(8)->post($tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $config->client_id,
            'client_secret' => $config->client_secret,
            'scope' => $config->settings['scope'] ?? 'read:events',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }

    protected function parseDcsaResponse(array $payload, string $trackingNumber, string $type, bool $isSim): TrackingResultDTO
    {
        $events = [];

        foreach ($payload as $item) {
            $events[] = new TrackingEventDTO(
                eventId: $item['eventID'] ?? null,
                eventType: $item['eventType'] ?? 'EQUIPMENT',
                eventClassifier: $item['eventClassifierCode'] ?? 'ACT',
                eventCode: $item['equipmentEventTypeCode'] ?? ($item['transportEventTypeCode'] ?? ($item['shipmentEventTypeCode'] ?? null)),
                eventDateTime: $item['eventDateTime'] ?? ($item['eventCreatedDateTime'] ?? now()->toDateTimeString()),
                title: $item['equipmentEventTypeCode'] ?? ($item['eventType'] ?? 'Event Update'),
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
            carrierCode: 'hapag_lloyd',
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
