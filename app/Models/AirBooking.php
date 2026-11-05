<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirBooking extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'air_bookings';

    protected $fillable = [
        'company_id',
        'booking_no',
        'booking_date',
        'customer_id',
        'carrier_id',
        'flight_no',
        'dep_port_id',
        'dst_port_id',
        'etd',
        'eta',
        'status',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'office_id',
        'sales_person_id',
        'oversea_agent_id',
        'incoterms_id',
        'cargo_type',
        'ship_type',
        'pkg_qty',
        'pkg_unit_id',
        'gross_weight',
        'volume',
        'chargeable_weight',
        'wt_val_payment',
        'other_charges_payment',
        'stackable',
        'handling_info',
        'pickup_delivery_instructions',
        'mawb_reference',
        'shipper_id',
        'op_id',
        'color',
        'is_blocked',
        'is_locked',
    ];

    protected $attributes = [
        'is_blocked' => false,
        'is_locked' => false,
    ];

    protected $casts = [
        'booking_date' => 'date',
        'etd'          => 'date',
        'eta'          => 'date',
        'stackable'    => 'boolean',
        'is_blocked'   => 'boolean',
        'is_locked'    => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function overseaAgent()
    {
        return $this->belongsTo(TradePartner::class, 'oversea_agent_id');
    }

    public function incoterm()
    {
        return $this->belongsTo(Incoterm::class, 'incoterms_id');
    }

    public function packageUnit()
    {
        return $this->belongsTo(PackageUnit::class, 'pkg_unit_id');
    }

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(TradePartner::class, 'carrier_id');
    }

    public function shipper()
    {
        return $this->belongsTo(TradePartner::class, 'shipper_id');
    }

    public function depPort()
    {
        return $this->belongsTo(Port::class, 'dep_port_id');
    }

    public function dstPort()
    {
        return $this->belongsTo(Port::class, 'dst_port_id');
    }

    public function op()
    {
        return $this->belongsTo(User::class, 'op_id');
    }

    public function charges()
    {
        return $this->morphMany(\App\Models\Charge::class, 'chargeable');
    }

    public function statusLogs()
    {
        return $this->morphMany(\App\Models\ShipmentStatusLog::class, 'shipment');
    }
}
