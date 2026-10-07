<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class AirImport extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'file_no', 'mawb_no', 'post_date', 'office_id', 'op_id',
        'forwarding_agent_id', 'oversea_agent_id', 'carrier_id', 'acct_carrier_id',
        'flight_no', 'dep_port_id', 'dst_port_id', 'etd', 'eta', 'atd', 'ata',
        'pkg_qty', 'pkg_unit_id', 'gross_weight', 'weight_unit', 'chargeable_weight',
        'volume', 'freight_term', 'freight_location_id', 'is_ecommerce', 'internal_remark', 'color',
        // Filing fields
        'shipper_id', 'consignee_id', 'bill_to_id', 'notify_id', 'trucker_id',
        'pod_eta', 'ship_mode', 'go_date', 'sub_bl_no',
        'final_destination_id', 'delivery_location_id', 'final_eta',
        'last_free_day', 'storage_start_date',
        // New filing fields
        'cy_cfs_loc', 'expiry_date', 'ams_no', 'isf_no', 'isf_matched_date',
        'isf_3rd_party', 'sales_type', 'c_released_date', 'entry_no', 'ror',
        'released_by_id', 'do_sent', 'do_sent_date', 'entry_doc_sent_date',
        'hold', 'door_delivered_date',
        // HAWB section fields
        'class_of_entry', 'cargo_released_to', 'ship_type',
        'memos',
        // Direct Master fields
        'is_direct_master', 'dm_customer_id', 'dm_shipper_id', 'dm_consignee_id',
        'dm_notify_id', 'dm_bill_to_id', 'dm_sales_person_id', 'is_blocked',
        'transportation',
    ];

    protected $casts = [
        'post_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
        'atd' => 'date',
        'ata' => 'date',
        'is_ecommerce' => 'boolean',
        'final_eta' => 'date',
        'last_free_day' => 'date',
        'storage_start_date' => 'date',
        'pod_eta' => 'date',
        'go_date' => 'date',
        'expiry_date' => 'date',
        'isf_matched_date' => 'date',
        'isf_3rd_party' => 'boolean',
        'c_released_date' => 'date',
        'ror' => 'boolean',
        'do_sent' => 'boolean',
        'do_sent_date' => 'date',
        'entry_doc_sent_date' => 'date',
        'hold' => 'boolean',
        'door_delivered_date' => 'date',
        'is_direct_master' => 'boolean',
        'is_blocked' => 'boolean',
        'memos' => 'array',
    ];

    public function releasedBy() { return $this->belongsTo(User::class, 'released_by_id'); }

    public function office() { return $this->belongsTo(Office::class); }
    public function operator() { return $this->belongsTo(User::class, 'op_id'); }
    public function forwardingAgent() { return $this->belongsTo(TradePartner::class, 'forwarding_agent_id'); }
    public function overseaAgent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function carrier() { return $this->belongsTo(TradePartner::class, 'carrier_id'); }
    public function acctCarrier() { return $this->belongsTo(TradePartner::class, 'acct_carrier_id'); }
    public function depPort() { return $this->belongsTo(Port::class, 'dep_port_id'); }
    public function dstPort() { return $this->belongsTo(Port::class, 'dst_port_id'); }
    public function packageUnit() { return $this->belongsTo(PackageUnit::class, 'pkg_unit_id'); }
    public function shipper_rel() { return $this->belongsTo(TradePartner::class, 'shipper_id'); }
    public function consignee_rel() { return $this->belongsTo(TradePartner::class, 'consignee_id'); }
    public function billTo() { return $this->belongsTo(TradePartner::class, 'bill_to_id'); }
    public function trucker() { return $this->belongsTo(TradePartner::class, 'trucker_id'); }
    public function notifyParty() { return $this->belongsTo(TradePartner::class, 'notify_id'); }
    public function finalDestination() { return $this->belongsTo(Port::class, 'final_destination_id'); }

    public function hbls() { return $this->hasMany(AirImportHbl::class); }
    public function containers() { return $this->hasMany(AirImportContainer::class); }

    // Direct Master relationships
    public function dmCustomer() { return $this->belongsTo(TradePartner::class, 'dm_customer_id'); }
    public function dmShipper() { return $this->belongsTo(TradePartner::class, 'dm_shipper_id'); }
    public function dmConsignee() { return $this->belongsTo(TradePartner::class, 'dm_consignee_id'); }
    public function dmNotify() { return $this->belongsTo(TradePartner::class, 'dm_notify_id'); }
    public function dmBillTo() { return $this->belongsTo(TradePartner::class, 'dm_bill_to_id'); }
    public function dmSalesPerson() { return $this->belongsTo(User::class, 'dm_sales_person_id'); }
    
    // Polymorphic
    public function charges() { return $this->morphMany(Charge::class, 'chargeable'); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }
    public function statusLogs() { return $this->morphMany(ShipmentStatusLog::class, 'shipment'); }
}
