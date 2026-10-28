<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class WarehouseReceipt extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'warehouse_receipts';

    protected $fillable = [
        'company_id',
        'truck_shipment_id',
        'receipt_no',
        'receipt_date',
        'warehouse_id',
        'customer_id',
        'shipper_id',
        'consignee_id',
        'office_id',
        'operator_id',
        'tracking_no',
        'carrier_name',
        'cargo_type',
        'is_hazardous',
        'is_heat_treated',
        'commodity',
        'po_no',
        'location_code',
        'delivered_by',
        'freight_charge_type',
        'freight_amount',
        'check_no',
        'color',
        'internal_remark',
        'charges_data',
        'memos_data',
    ];

    protected $casts = [
        'receipt_date' => 'datetime',
        'is_hazardous' => 'boolean',
        'is_heat_treated' => 'boolean',
        'freight_amount' => 'decimal:2',
        'charges_data' => 'array',
        'memos_data' => 'array',
    ];

    public function truckShipment()
    {
        return $this->belongsTo(TruckShipment::class, 'truck_shipment_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(TradePartner::class, 'warehouse_id');
    }

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function shipper()
    {
        return $this->belongsTo(TradePartner::class, 'shipper_id');
    }

    public function consignee()
    {
        return $this->belongsTo(TradePartner::class, 'consignee_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function receivings()
    {
        return $this->hasMany(WarehouseReceiving::class, 'warehouse_receipt_id');
    }

    public function items()
    {
        return $this->hasMany(WarehouseReceiptItem::class, 'warehouse_receipt_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
