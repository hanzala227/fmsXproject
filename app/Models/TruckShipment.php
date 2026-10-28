<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class TruckShipment extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'file_no', 'mbl_no', 'hbl_no', 'vessel_flight_no', 'carrier_bkg_no',
        'post_date', 'office_id', 'op_id', 'sales_id',
        'customer_id', 'shipper_id', 'consignee_id', 'trucker_id', 'customer_ref_no', 'bill_to_id',
        'truck_no', 'driver_name', 'driver_phone',
        'ship_type', 'pol_id', 'pod_id', 'final_destination_id', 'etd', 'eta', 'feta',
        'empty_pickup_location_id', 'freight_pickup_location_id',
        'delivery_to_location_id', 'empty_return_location_id',
        'pkg_qty', 'pkg_unit_id', 'weight_kg', 'volume_cbm', 'measure_cft',
        'est_delivery_date', 'is_delivered', 'delivered_date', 'is_ecommerce',
        'internal_remark', 'instruction_text', 'quotation_id', 'color', 'description', 'is_blocked'
    ];

    protected $casts = [
        'post_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
        'feta' => 'date',
        'est_delivery_date' => 'date',
        'delivered_date' => 'date',
        'is_delivered' => 'boolean',
        'is_ecommerce' => 'boolean',
        'is_blocked' => 'boolean',
        'pkg_qty' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'volume_cbm' => 'decimal:3',
        'measure_cft' => 'decimal:3',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'op_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function dmCustomer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function dmShipper()
    {
        return $this->belongsTo(TradePartner::class, 'shipper_id');
    }

    public function dmConsignee()
    {
        return $this->belongsTo(TradePartner::class, 'consignee_id');
    }

    public function dmNotify()
    {
        return $this->belongsTo(TradePartner::class, 'consignee_id');
    }

    public function agent()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function forwardingAgent()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function overseaAgent()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function shipper()
    {
        return $this->belongsTo(TradePartner::class, 'shipper_id');
    }

    public function consignee()
    {
        return $this->belongsTo(TradePartner::class, 'consignee_id');
    }

    public function trucker()
    {
        return $this->belongsTo(TradePartner::class, 'trucker_id');
    }

    public function billTo()
    {
        return $this->belongsTo(TradePartner::class, 'bill_to_id');
    }

    public function pol()
    {
        return $this->belongsTo(Port::class, 'pol_id');
    }

    public function pod()
    {
        return $this->belongsTo(Port::class, 'pod_id');
    }

    public function finalDestination()
    {
        return $this->belongsTo(Port::class, 'final_destination_id');
    }

    public function emptyPickupLocation()
    {
        return $this->belongsTo(TradePartner::class, 'empty_pickup_location_id');
    }

    public function freightPickupLocation()
    {
        return $this->belongsTo(TradePartner::class, 'freight_pickup_location_id');
    }

    public function deliveryToLocation()
    {
        return $this->belongsTo(TradePartner::class, 'delivery_to_location_id');
    }

    public function emptyReturnLocation()
    {
        return $this->belongsTo(TradePartner::class, 'empty_return_location_id');
    }

    public function packageUnit()
    {
        return $this->belongsTo(PackageUnit::class, 'pkg_unit_id');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function charges()
    {
        return $this->morphMany(Charge::class, 'chargeable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function statusLogs()
    {
        return $this->morphMany(ShipmentStatusLog::class, 'shipment');
    }

    public function workOrders()
    {
        return $this->morphMany(WorkOrder::class, 'workable');
    }

    public function memos()
    {
        return $this->hasMany(TruckShipmentMemo::class);
    }

    public function containers()
    {
        return $this->hasMany(TruckShipmentContainer::class);
    }

    public function commodities()
    {
        return $this->hasMany(TruckShipmentCommodity::class);
    }

    public function warehouseReceipts()
    {
        return $this->hasMany(WarehouseReceipt::class, 'truck_shipment_id');
    }
}
