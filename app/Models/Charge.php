<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Charge extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'chargeable_type', 'chargeable_id', 'type', 'charge_code', 
        'charge_name', 'party', 'party_name', 'sal', 'bill_to_id', 'vendor_id', 'pc', 'qty', 'unit', 
        'currency_id', 'rate', 'amount', 'tax_percent', 'tax_amount', 
        'total_amount', 'is_invoiced', 'invoice_no', 'invoice_date', 'remark',
        'seal_no2', 'pickup_no', 'cprs_no', 'cnru_no', 'it_no', 'dg', 
        'temp', 'vent', 'storage_start_date', 'storage_end_date', 
        'carrier_release', 'yard_location', 'unload_vessel_date', 
        'gate_in_date', 'rail_start_date', 'pod_eta_date', 'available_pickup', 
        'weight_lb', 'appt_date', 'trucker_id', 'pickup_date', 'gate_out_date', 
        'fdest_eta_date', 'eta_door_date', 'ata_door_date', 'measurement_cft', 
        'container_remarks', 'internal_remarks', 'empty_confirmed_date', 
        'empty_return_date', 'complete'
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_invoiced' => 'boolean',
        'invoice_date' => 'date',
    ];

    public function chargeable()
    {
        return $this->morphTo();
    }

    public function billTo() { return $this->belongsTo(TradePartner::class, 'bill_to_id'); }
    public function vendor() { return $this->belongsTo(TradePartner::class, 'vendor_id'); }
    public function currency() { return $this->belongsTo(Currency::class); }
}
