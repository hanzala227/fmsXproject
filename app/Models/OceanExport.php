<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class OceanExport extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'file_no', 'mbl_no', 'booking_no', 'post_date', 'office_id', 'op_id', 
        'forwarding_agent_id', 'oversea_agent_id', 'co_loader_id', 
        'carrier_id', 'acct_carrier_id', 'business_referred_by_id',
        'is_direct_master', 'dm_customer_id', 'dm_shipper_id', 
        'dm_consignee_id', 'dm_notify_id', 'dm_bill_to_id', 
        'dm_sales_person_id', 'agent_ref_no', 'contract_no', 
        'sub_bl_no', 'bl_type', 'cargo_type', 'ship_mode', 
        'vessel_id', 'voyage', 'pol_id', 'pod_id', 'del_id', 
        'fdest_id', 'receipt_id', 'etd', 'eta', 'atd', 'ata', 
        'etb', 'final_eta', 'receipt_etd', 'cy_location_id', 
        'cfs_location_id', 'return_location_id', 'service_term_from_id', 
        'service_term_to_id', 'freight_term', 'obl_type', 
        'obl_received_date', 'released_date', 'latest_gate_in', 
        'is_ecommerce', 'is_released', 'internal_remark', 'is_blocked',
        'ams_no', 'isf_no', 'isf_matched_date', 'is_isf_3rd_party',
        'entry_no', 'entry_doc_sent_date', 'go_date', 'available_date',
        'c_released_date', 'released_by_id', 'is_ror', 'is_hold',
        'door_delivery_date', 'trucker_id', 'expiry_date', 'sales_type', 'incoterm_id',
        'color', 'routes',
    ];

    protected $casts = [
        'routes' => 'array',
        'post_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
        'atd' => 'date',
        'ata' => 'date',
        'etb' => 'date',
        'final_eta' => 'date',
        'receipt_etd' => 'date',
        'obl_received_date' => 'date',
        'released_date' => 'date',
        'latest_gate_in' => 'date',
        'is_direct_master' => 'boolean',
        'is_ecommerce' => 'boolean',
        'is_blocked' => 'boolean',
        'is_released' => 'boolean',
        'isf_matched_date' => 'date',
        'is_isf_3rd_party' => 'boolean',
        'entry_doc_sent_date' => 'date',
        'go_date' => 'date',
        'available_date' => 'date',
        'c_released_date' => 'date',
        'is_ror' => 'boolean',
        'is_hold' => 'boolean',
        'door_delivery_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Core Relations
    public function office() { return $this->belongsTo(Office::class); }
    public function operator() { return $this->belongsTo(User::class, 'op_id'); }
    public function salesPerson() { return $this->belongsTo(User::class, 'dm_sales_person_id'); }
    public function vessel() { return $this->belongsTo(Vessel::class); }
    public function portOfLoading() { return $this->belongsTo(Port::class, 'pol_id'); }
    public function portOfDischarge() { return $this->belongsTo(Port::class, 'pod_id'); }
    public function placeOfDelivery() { return $this->belongsTo(Port::class, 'del_id'); }
    public function finalDestination() { return $this->belongsTo(Port::class, 'fdest_id'); }
    public function placeOfReceipt() { return $this->belongsTo(Port::class, 'receipt_id'); }
    
    // Trade Partner Relations
    public function forwardingAgent() { return $this->belongsTo(TradePartner::class, 'forwarding_agent_id'); }
    public function overseaAgent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function deliveryAgent() { return $this->belongsTo(TradePartner::class, 'oversea_agent_id'); }
    public function coLoader() { return $this->belongsTo(TradePartner::class, 'co_loader_id'); }
    public function carrier() { return $this->belongsTo(TradePartner::class, 'carrier_id'); }
    public function acctCarrier() { return $this->belongsTo(TradePartner::class, 'acct_carrier_id'); }
    public function businessReferredBy() { return $this->belongsTo(TradePartner::class, 'business_referred_by_id'); }
    public function dmCustomer() { return $this->belongsTo(TradePartner::class, 'dm_customer_id'); }
    public function dmShipper() { return $this->belongsTo(TradePartner::class, 'dm_shipper_id'); }
    public function dmConsignee() { return $this->belongsTo(TradePartner::class, 'dm_consignee_id'); }
    public function dmNotify() { return $this->belongsTo(TradePartner::class, 'dm_notify_id'); }
    public function dmBillTo() { return $this->belongsTo(TradePartner::class, 'dm_bill_to_id'); }
    public function cyLocation() { return $this->belongsTo(TradePartner::class, 'cy_location_id'); }
    public function cfsLocation() { return $this->belongsTo(TradePartner::class, 'cfs_location_id'); }
    public function returnLocation() { return $this->belongsTo(TradePartner::class, 'return_location_id'); }
    public function releasedBy() { return $this->belongsTo(User::class, 'released_by_id'); }
    public function trucker() { return $this->belongsTo(TradePartner::class, 'trucker_id'); }
    
    // Terms
    public function serviceTermFrom() { return $this->belongsTo(ServiceTerm::class, 'service_term_from_id'); }
    public function serviceTermTo() { return $this->belongsTo(ServiceTerm::class, 'service_term_to_id'); }
    public function incoterm() { return $this->belongsTo(Incoterm::class, 'incoterm_id'); }
    
    // Items
    public function hbls() { return $this->hasMany(OceanExportHbl::class); }
    public function containers() { return $this->hasMany(OceanExportContainer::class); }
    
    // Polymorphic
    public function charges() { return $this->morphMany(Charge::class, 'chargeable'); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }
    public function statusLogs() { return $this->morphMany(ShipmentStatusLog::class, 'shipment'); }
}
