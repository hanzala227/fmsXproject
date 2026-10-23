<?php

namespace App\Services\Tracking\Drivers;

use App\Models\CarrierApiConfig;
use App\Models\CarrierApiLog;
use App\Services\Tracking\Contracts\CarrierTrackingInterface;
use App\Services\Tracking\DTO\TrackingEventDTO;
use App\Services\Tracking\DTO\TrackingResultDTO;
use Illuminate\Support\Facades\Http;
use Throwable;

abstract class AbstractCarrierDriver implements CarrierTrackingInterface
{
    protected ?CarrierApiConfig $config = null;

    public function __construct(?CarrierApiConfig $config = null)
    {
        $this->config = $config ?: CarrierApiConfig::getCarrier($this->getCarrierCode());
    }

    /**
     * Log carrier API requests and responses.
     */
    protected function logApiCall(
        string $endpoint,
        string $method,
        ?string $trackingNumber,
        ?array $requestPayload,
        int $statusCode,
        ?array $responsePayload,
        int $durationMs,
        ?string $errorMessage = null
    ): void {
        try {
            CarrierApiLog::create([
                'carrier_code' => $this->getCarrierCode(),
                'endpoint' => $endpoint,
                'method' => strtoupper($method),
                'tracking_number' => $trackingNumber,
                'request_payload' => $requestPayload,
                'response_status' => $statusCode,
                'response_payload' => $responsePayload,
                'duration_ms' => $durationMs,
                'error_message' => $errorMessage,
                'company_id' => auth()->check() ? auth()->user()->company_id : null,
                'user_id' => auth()->id(),
            ]);
        } catch (Throwable $e) {
            // Silently fail logging to avoid interrupting main tracking flow
        }
    }

    /**
     * Map DCSA events to an overall status code and progress percentage.
     */
    protected function calculateJourneyProgress(array $events): array
    {
        $status = 'booked';
        $progress = 10;
        $description = 'Shipment booking confirmed with carrier.';

        // Look for milestones
        $hasDelivered = false;
        $hasGateOut = false;
        $hasDischarged = false;
        $hasLoaded = false;
        $hasGateIn = false;

        foreach ($events as $event) {
            $code = strtoupper((string) ($event->eventCode ?? ''));
            $title = strtolower((string) ($event->title ?? ''));

            if ($code === 'DDLV' || str_contains($title, 'deliver') || str_contains($title, 'empty container returned')) {
                $hasDelivered = true;
            } elseif ($code === 'GTOUT' || str_contains($title, 'gate out')) {
                $hasGateOut = true;
            } elseif ($code === 'DISC' || str_contains($title, 'discharge') || str_contains($title, 'vessel arrival')) {
                $hasDischarged = true;
            } elseif ($code === 'LOAD' || $code === 'DEPA' || str_contains($title, 'loaded') || str_contains($title, 'departure')) {
                $hasLoaded = true;
            } elseif ($code === 'GTIN' || str_contains($title, 'gate in') || str_contains($title, 'received')) {
                $hasGateIn = true;
            }
        }

        if ($hasDelivered) {
            $status = 'delivered';
            $progress = 100;
            $description = 'Cargo successfully delivered to consignee/destination.';
        } elseif ($hasGateOut) {
            $status = 'gate_out';
            $progress = 85;
            $description = 'Container gated out from destination terminal.';
        } elseif ($hasDischarged) {
            $status = 'discharged';
            $progress = 75;
            $description = 'Container discharged at destination port of discharge.';
        } elseif ($hasLoaded) {
            $status = 'in_transit';
            $progress = 50;
            $description = 'Vessel en route / container loaded onboard vessel.';
        } elseif ($hasGateIn) {
            $status = 'gate_in';
            $progress = 25;
            $description = 'Container received at origin terminal gate.';
        }

        return [
            'status' => $status,
            'progress' => $progress,
            'description' => $description,
        ];
    }
}

