<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class OceanImportCharge extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id', 'ocean_import_id', 'ocean_import_hbl_id', 'type', 'party', 'party_name', 'sal', 'charge_code', 
        'charge_name', 'bill_to_id', 'vendor_id', 'pc', 'qty', 'unit', 
        'currency_id', 'rate', 'amount', 'tax_percent', 'tax_amount', 
        'total_amount', 'roe', 'vat', 'is_invoiced', 'invoice_no', 'invoice_date', 'remark'
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'roe' => 'decimal:4',
        'vat' => 'decimal:2',
        'is_invoiced' => 'boolean',
        'invoice_date' => 'date',
    ];

    public function oceanImport() { return $this->belongsTo(OceanImport::class); }
    public function oceanImportHbl() { return $this->belongsTo(OceanImportHbl::class); }
    public function billTo() { return $this->belongsTo(TradePartner::class, 'bill_to_id'); }
    public function vendor() { return $this->belongsTo(TradePartner::class, 'vendor_id'); }
    public function currency() { return $this->belongsTo(Currency::class); }
}
