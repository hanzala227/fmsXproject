<?php

namespace App\Services\Tracking\Contracts;

use App\Services\Tracking\DTO\TrackingResultDTO;

interface CarrierTrackingInterface
{
    /**
     * Get unique carrier code (e.g. hapag_lloyd, maersk, dcsa, mock)
     */
    public function getCarrierCode(): string;

    /**
     * Track cargo by Container, B/L, or Booking number.
     *
     * @param string $trackingNumber
     * @param string $type ('container', 'mbl', 'booking')
     * @param array $options
     * @return TrackingResultDTO
     */
    public function track(string $trackingNumber, string $type = 'container', array $options = []): TrackingResultDTO;

    /**
     * Test connection to carrier API endpoint.
     *
     * @return array ['success' => bool, 'message' => string, 'latency_ms' => int, 'details' => array]
     */
    public function testConnection(): array;
}
