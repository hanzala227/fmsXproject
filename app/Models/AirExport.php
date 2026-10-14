<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class AirExport extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'file_no', 'mawb_no', 'booking_no', 'post_date', 'office_id', 'op_id',
        'forwarding_agent_id', 'oversea_agent_id', 'carrier_id', 'acct_carrier_id',
        'flight_no', 'dep_port_id', 'dst_port_id', 'etd', 'eta', 'atd', 'ata',
        'pkg_qty', 'pkg_unit_id', 'gross_weight', 'chargeable_weight',
        'volume', 'buying_rate', 'selling_rate',
        'freight_term', 'is_ecommerce', 'sales_type', 'is_blocked', 'internal_remark', 'color', 'label_description',
        'is_direct_master', 'dm_customer_id', 'dm_shipper_id', 'dm_bill_to_id', 'dm_consignee_id', 'dm_notify_id', 'dm_sales_person_id', 'agent_ref_no',
        'shipper_id', 'consignee_id', 'notify_id', 'actual_shipper_id',
        // Additional fields from UpdateRequest validation (may need migration)
        'incoterm_id', 'mark_number', 'service_term_from', 'service_term_to',
        'agent_id', 'co_loader_id', 'trans_port_id', 'trans_port1_id', 'trans_port2_id', 'trans_port3_id', 'delivery_port_id',
        'route_data', 'itn_no', 'cers_no', 'reference_no', 'awb_date', 'cargo_ready_date',
        'issuing_carrier', 'awb_type', 'dv_carriage', 'dv_customs', 'insurance', 'wt_val', 'other_term', 'routes',
    ];

    protected $casts = [
        'routes' => 'array',
        'post_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
        'atd' => 'date',
        'ata' => 'date',
        'awb_date' => 'date',
        'cargo_ready_date' => 'date',
        'is_ecommerce' => 'boolean',
        'is_blocked' => 'boolean',
        'is_direct_master' => 'boolean',
        'buying_rate' => 'decimal:4',
        'selling_rate' => 'decimal:4',
        'route_data' => 'array', // JSON field
    ];

    public function office() { return $this->belongsTo(Office::class); }
    public function operator() { return $this->belongsTo(User::class, 'op_id'); }
    public function forwardingAgent() { return $this->belongsTo(TradePartner::class, 'forwarding_agent_id'); }
    public function overseaAgent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function deliveryAgent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function agent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function coLoader() { return $this->belongsTo(TradePartner::class, 'co_loader_id'); }
    public function carrier() { return $this->belongsTo(TradePartner::class, 'carrier_id'); }
    public function acctCarrier() { return $this->belongsTo(TradePartner::class, 'acct_carrier_id'); }
    public function depPort() { return $this->belongsTo(Port::class, 'dep_port_id'); }
    public function dstPort() { return $this->belongsTo(Port::class, 'dst_port_id'); }
    public function packageUnit() { return $this->belongsTo(PackageUnit::class, 'pkg_unit_id'); }

    public function dmCustomer() { return $this->belongsTo(TradePartner::class, 'dm_customer_id'); }
    public function dmShipper() { return $this->belongsTo(TradePartner::class, 'dm_shipper_id'); }
    public function dmNotify() { return $this->belongsTo(TradePartner::class, 'dm_notify_id'); }
    public function dmBillTo() { return $this->belongsTo(TradePartner::class, 'dm_bill_to_id'); }
    public function dmConsignee() { return $this->belongsTo(TradePartner::class, 'dm_consignee_id'); }
    public function salesPerson() { return $this->belongsTo(User::class, 'dm_sales_person_id'); }
    public function shipper() { return $this->belongsTo(TradePartner::class, 'shipper_id'); }
    public function consignee() { return $this->belongsTo(TradePartner::class, 'consignee_id'); }
    public function notifyParty() { return $this->belongsTo(TradePartner::class, 'notify_id'); }

    public function hbls() { return $this->hasMany(AirExportHbl::class); }
    public function memos() { return $this->hasMany(AirExportMemo::class); }
    
    // Polymorphic
    public function charges() { return $this->morphMany(Charge::class, 'chargeable'); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }
    public function statusLogs() { return $this->morphMany(ShipmentStatusLog::class, 'shipment'); }
}
