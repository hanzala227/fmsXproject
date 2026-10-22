<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirExportHbl extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'air_export_id', 'hawb_no', 'booking_no', 'booking_date', 'quotation_no',
        'customer_id', 'shipper_id', 'consignee_id',
        'notify_party_id', 'sales_person_id', 'oversea_agent_id',
        'issuing_carrier', 'bill_to', 'departure', 'destination',
        'cargo_pickup', 'delivery_to', 'cargo_type', 'ship_type',
        'feta', 'itn_no', 'display_unit', 'cargo_ready_date',
        'pkg_qty', 'pkg_unit_id',
        'gross_weight', 'chargeable_weight', 'volume',
        'buying_rate', 'selling_rate', 'commodity',
        'incoterms_id', 'freight_term', 'sales_type',
        'dv_carriage', 'dv_customs', 'insurance', 'other_charge_term',
        'hbl_remark', 'mark', 'description', 'remark',
        'op_id', 'color', 'is_blocked', 'commodities'
    ];

    protected $casts = [
        'commodities' => 'array',
        'booking_date' => 'date',
        'feta' => 'date',
        'cargo_ready_date' => 'date',
        'is_blocked' => 'boolean',
    ];

    protected $attributes = [
        'is_blocked' => false,
        'color' => null,
    ];

    public function airExport() { return $this->belongsTo(AirExport::class); }
    public function customer() { return $this->belongsTo(TradePartner::class, 'customer_id'); }
    public function shipper() { return $this->belongsTo(TradePartner::class, 'shipper_id'); }
    public function consignee() { return $this->belongsTo(TradePartner::class, 'consignee_id'); }
    public function notifyParty() { return $this->belongsTo(TradePartner::class, 'notify_party_id'); }
    public function salesPerson() { return $this->belongsTo(User::class, 'sales_person_id'); }
    public function packageUnit() { return $this->belongsTo(PackageUnit::class, 'pkg_unit_id'); }
    
    public function op() { return $this->belongsTo(User::class, 'op_id'); }
    public function charges() { return $this->morphMany(Charge::class, 'chargeable'); }
}
