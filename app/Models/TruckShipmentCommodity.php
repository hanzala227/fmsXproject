<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckShipmentCommodity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'truck_shipment_id',
        'container_id',
        'description',
        'hts_code',
        'po_no',
        'pkg_qty',
        'weight',
        'cbm',
    ];

    public function truckShipment()
    {
        return $this->belongsTo(TruckShipment::class);
    }

    public function container()
    {
        return $this->belongsTo(TruckShipmentContainer::class, 'container_id');
    }
}

