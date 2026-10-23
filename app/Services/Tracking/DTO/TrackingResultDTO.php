<?php

namespace App\Services\Tracking\DTO;

class TrackingResultDTO
{
    /**
     * @param string $carrierCode
     * @param string $trackingNumber
     * @param string $trackingType
     * @param string $currentStatus
     * @param string|null $statusDescription
     * @param int $progressPercentage
     * @param string|null $originLocode
     * @param string|null $originName
     * @param string|null $destinationLocode
     * @param string|null $destinationName
     * @param string|null $vesselName
     * @param string|null $voyageNo
     * @param string|null $etd
     * @param string|null $atd
     * @param string|null $eta
     * @param string|null $ata
     * @param TrackingEventDTO[] $events
     * @param array $rawPayload
     * @param bool $isSimulation
     */
    public function __construct(
        public string $carrierCode,
        public string $trackingNumber,
        public string $trackingType = 'container',
        public string $currentStatus = 'booked',
        public ?string $statusDescription = null,
        public int $progressPercentage = 10,
        public ?string $originLocode = null,
        public ?string $originName = null,
        public ?string $destinationLocode = null,
        public ?string $destinationName = null,
        public ?string $vesselName = null,
        public ?string $voyageNo = null,
        public ?string $etd = null,
        public ?string $atd = null,
        public ?string $eta = null,
        public ?string $ata = null,
        public array $events = [],
        public array $rawPayload = [],
        public bool $isSimulation = false
    ) {}

    public function toArray(): array
    {
        return [
            'carrier_code' => $this->carrierCode,
            'tracking_number' => $this->trackingNumber,
            'tracking_type' => $this->trackingType,
            'current_status' => $this->currentStatus,
            'status_description' => $this->statusDescription,
            'progress_percentage' => $this->progressPercentage,
            'origin_locode' => $this->originLocode,
            'origin_name' => $this->originName,
            'destination_locode' => $this->destinationLocode,
            'destination_name' => $this->destinationName,
            'vessel_name' => $this->vesselName,
            'voyage_no' => $this->voyageNo,
            'etd' => $this->etd,
            'atd' => $this->atd,
            'eta' => $this->eta,
            'ata' => $this->ata,
            'events' => array_map(fn($e) => $e instanceof TrackingEventDTO ? $e->toArray() : $e, $this->events),
            'raw_payload' => $this->rawPayload,
            'is_simulation' => $this->isSimulation,
        ];
    }
}

