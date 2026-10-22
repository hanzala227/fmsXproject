<?php

namespace App\Services\Tracking\DTO;

class TrackingEventDTO
{
    public function __construct(
        public ?string $eventId,
        public string $eventType, // SHIPMENT, TRANSPORT, EQUIPMENT
        public string $eventClassifier, // ACT, EST, PLN, REQ
        public ?string $eventCode, // LOAD, DISC, GTIN, GTOUT, DEPA, ARRI
        public ?string $eventDateTime,
        public string $title,
        public ?string $description = null,
        public ?string $locationName = null,
        public ?string $locationLocode = null,
        public ?string $facilityName = null,
        public ?string $vesselName = null,
        public ?string $voyageNo = null,
        public ?string $equipmentReference = null,
        public bool $isCurrent = false,
        public bool $isManual = false
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->eventType,
            'event_classifier' => $this->eventClassifier,
            'event_code' => $this->eventCode,
            'event_date_time' => $this->eventDateTime,
            'title' => $this->title,
            'description' => $this->description,
            'location_name' => $this->locationName,
            'location_locode' => $this->locationLocode,
            'facility_name' => $this->facilityName,
            'vessel_name' => $this->vesselName,
            'voyage_no' => $this->voyageNo,
            'equipment_reference' => $this->equipmentReference,
            'is_current' => $this->isCurrent,
            'is_manual' => $this->isManual,
        ];
    }
}

