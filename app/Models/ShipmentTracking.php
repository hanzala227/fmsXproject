<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShipmentTracking extends Model
{
    use HasFactory;

    protected $table = 'shipment_trackings';

    protected $fillable = [
        'company_id',
        'trackable_type',
        'trackable_id',
        'carrier_code',
        'tracking_type',
        'tracking_number',
        'current_status',
        'status_description',
        'progress_percentage',
        'origin_locode',
        'origin_name',
        'destination_locode',
        'destination_name',
        'vessel_name',
        'voyage_no',
        'etd',
        'atd',
        'eta',
        'ata',
        'last_polled_at',
        'raw_payload',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'etd' => 'datetime',
        'atd' => 'datetime',
        'eta' => 'datetime',
        'ata' => 'datetime',
        'last_polled_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /**
     * Polymorphic relation to shipment (OceanImport, OceanExport, etc.)
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Chronological tracking events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_tracking_id')
            ->orderBy('event_date_time', 'desc');
    }

    /**
     * Chronological tracking events ascending (for journey playback).
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_tracking_id')
            ->orderBy('event_date_time', 'asc');
    }

    /**
     * Badge CSS class helper based on status.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match (strtolower((string) $this->current_status)) {
            'delivered', 'completed' => ['bg' => '#dcfce7', 'color' => '#15803d', 'label' => 'DELIVERED', 'step' => 5],
            'gate_out' => ['bg' => '#fef3c7', 'color' => '#b45309', 'label' => 'GATE OUT', 'step' => 4],
            'discharged', 'arrived' => ['bg' => '#e0e7ff', 'color' => '#4338ca', 'label' => 'DISCHARGED', 'step' => 4],
            'in_transit', 'loaded' => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'label' => 'IN TRANSIT', 'step' => 3],
            'gate_in' => ['bg' => '#f3e8ff', 'color' => '#7e22ce', 'label' => 'GATE IN', 'step' => 2],
            default => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'BOOKED', 'step' => 1],
        };
    }
}

