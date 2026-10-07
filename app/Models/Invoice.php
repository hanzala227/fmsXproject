<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'invoice_no',
        'invoice_date',
        'due_date',
        'bill_to_id',
        'billing_address',
        'invoiceable_type',
        'invoiceable_id',
        'currency_id',
        'subtotal',
        'tax_total',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'type',
        'office_id',
        'issued_by',
        'discount_pct',
        'tax_pct',
        'shipping_amount',
        'color',
        'internal_remark',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'main_sequence_no',
        'group_letter',
        'document_type',
        'idempotency_key',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'discount_pct' => 'decimal:2',
        'tax_pct' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'color' => 'string',
    ];

    protected function casts(): array
    {
        return array_merge($this->casts, []);
    }

    public function billTo()
    {
        return $this->belongsTo(TradePartner::class, 'bill_to_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(AccountingPayment::class, 'invoice_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

}
