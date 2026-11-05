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
        'exchange_rate',
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
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'main_sequence_no',
        'group_letter',
        'document_type',
        'idempotency_key',
        'mbl_no',
        'remark',
    ];

    protected $appends = [
        'master_bl_no',
        'house_bl_no',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'discount_pct' => 'decimal:2',
        'tax_pct' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'color' => 'string',
        'cancelled_at' => 'datetime',
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

    public function getInvoiceableTypeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        // If backslashes were stripped: e.g. AppModelsOceanImport -> App\Models\OceanImport
        if (str_starts_with($value, 'AppModels')) {
            $class = 'App\\Models\\' . substr($value, 9);
            if (class_exists($class)) {
                return $class;
            }
        }

        // If simple class name without namespace: e.g. OceanImport -> App\Models\OceanImport
        if (!str_contains($value, '\\')) {
            $class = 'App\\Models\\' . $value;
            if (class_exists($class)) {
                return $class;
            }
        }

        // If class does not exist, return null to avoid fatal error during morph resolution
        if (!class_exists($value)) {
            return null;
        }

        return $value;
    }

    public function setInvoiceableTypeAttribute($value)
    {
        if (!empty($value) && str_starts_with($value, 'AppModels')) {
            $class = 'App\\Models\\' . substr($value, 9);
            if (class_exists($class)) {
                $value = $class;
            }
        }
        $this->attributes['invoiceable_type'] = $value;
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

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
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

    public function getMasterBlNoAttribute()
    {
        if (!empty($this->mbl_no)) {
            return $this->mbl_no;
        }

        try {
            $shipment = $this->invoiceable;
            if ($shipment) {
                return $shipment->mbl_no ?? $shipment->mawb_no ?? null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getHouseBlNoAttribute()
    {
        try {
            $shipment = $this->invoiceable;
            if (!$shipment) return null;

            if (isset($shipment->hbl_no)) return $shipment->hbl_no;
            if (isset($shipment->hawb_no)) return $shipment->hawb_no;

            if (method_exists($shipment, 'hbls') && $shipment->relationLoaded('hbls')) {
                $hblNos = $shipment->hbls->map(fn($h) => $h->hbl_no ?? $h->hawb_no ?? null)->filter()->unique()->values();
                if ($hblNos->isNotEmpty()) {
                    return $hblNos->join(', ');
                }
            } elseif (method_exists($shipment, 'hbls')) {
                $hblNos = $shipment->hbls()->get()->map(fn($h) => $h->hbl_no ?? $h->hawb_no ?? null)->filter()->unique()->values();
                if ($hblNos->isNotEmpty()) {
                    return $hblNos->join(', ');
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getShipmentUrlAttribute(): ?string
    {
        try {
            $type = $this->invoiceable_type;
            $id = $this->invoiceable_id;

            if (!$id) {
                $shipment = $this->invoiceable;
                if ($shipment) {
                    $id = $shipment->id;
                    $type = get_class($shipment);
                }
            }

            if (!$id || !$type) {
                return null;
            }

            if (str_contains($type, 'OceanImport')) {
                return route('ocean-import.edit', $id);
            }
            if (str_contains($type, 'OceanExport')) {
                return route('ocean-export.edit', $id);
            }
            if (str_contains($type, 'AirImport')) {
                return route('air-import.edit', $id);
            }
            if (str_contains($type, 'AirExport')) {
                return route('air-export.edit', $id);
            }
            if (str_contains($type, 'TruckShipment') || str_contains($type, 'Truck')) {
                return route('truck.edit', $id);
            }
            if (str_contains($type, 'OceanBooking')) {
                return route('ocean-bookings.edit', $id);
            }
            if (str_contains($type, 'AirBooking')) {
                return route('air-bookings.edit', $id);
            }
            if (str_contains($type, 'Quotation')) {
                return route('sales.quotations.edit', $id);
            }
            if (str_contains($type, 'WarehouseReceipt')) {
                return route('warehouse.receipts.edit', $id);
            }
            if (str_contains($type, 'WarehouseReceiving')) {
                return route('receiving.edit', $id);
            }
            if (str_contains($type, 'WarehouseShipping')) {
                return route('shipping.edit', $id);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Determine if the document has received final approval or is posted/settled.
     */
    public function isApproved(): bool
    {
        return in_array(strtoupper($this->status ?? ''), ['POSTED', 'PARTIAL', 'PAID', 'VOID'])
            || in_array($this->verification_status, ['Verified', 'Cancelled']);
    }

    /**
     * Determine if the document is cancelled or voided.
     */
    public function isCancelled(): bool
    {
        return strtoupper($this->status ?? '') === 'VOID'
            || $this->verification_status === 'Cancelled'
            || !empty($this->cancelled_at);
    }
}
