<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_tracking_id',
        'event_id',
        'event_type',
        'event_classifier',
        'event_code',
        'event_date_time',
        'title',
        'description',
        'location_name',
        'location_locode',
        'facility_name',
        'vessel_name',
        'voyage_no',
        'equipment_reference',
        'is_current',
        'is_manual',
    ];

    protected $casts = [
        'event_date_time' => 'datetime',
        'is_current' => 'boolean',
        'is_manual' => 'boolean',
    ];

    public function shipmentTracking()
    {
        return $this->belongsTo(ShipmentTracking::class, 'shipment_tracking_id');
    }

    /**
     * DCSA Classifier Label (Actual vs Estimated vs Planned)
     */
    public function getClassifierBadgeAttribute(): array
    {
        return match (strtoupper((string) $this->event_classifier)) {
            'ACT' => ['bg' => '#dcfce7', 'color' => '#166534', 'text' => 'ACTUAL'],
            'EST' => ['bg' => '#fef9c3', 'color' => '#854d0e', 'text' => 'ESTIMATED'],
            'PLN' => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'text' => 'PLANNED'],
            'REQ' => ['bg' => '#f1f5f9', 'color' => '#475569', 'text' => 'REQUESTED'],
            default => ['bg' => '#f3f4f6', 'color' => '#374151', 'text' => $this->event_classifier ?: 'INFO'],
        };
    }
}
