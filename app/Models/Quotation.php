<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'quote_date' => 'date',
        'expiry_date' => 'date',
        'valid_date' => 'date',
        'create_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id')->withTrashed();
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function op()
    {
        return $this->belongsTo(User::class, 'op_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function pol()
    {
        return $this->belongsTo(Port::class, 'pol_id');
    }

    public function pod()
    {
        return $this->belongsTo(Port::class, 'pod_id');
    }

    public function items()
    {
        return $this->morphMany(Charge::class, 'chargeable');
    }

    public function agent()
    {
        return $this->belongsTo(TradePartner::class, 'agent_id');
    }

    public function carrier()
    {
        return $this->belongsTo(TradePartner::class, 'carrier_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeForModule($query, string $module)
    {
        $module = trim($module);

        return $query->where(function ($q) use ($module) {
            $q->where('shipping_type', $module)
              ->orWhere('transport_mode', $module)
              ->orWhere('shipping_type', 'LIKE', '%' . $module . '%')
              ->orWhere('transport_mode', 'LIKE', '%' . $module . '%');
        });
    }
}
