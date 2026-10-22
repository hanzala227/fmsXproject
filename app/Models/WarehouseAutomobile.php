<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseAutomobile extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'warehouse_automobiles';

    protected $fillable = [
        'company_id',
        'vin_no',
        'wh_receipt_no',
        'received_by',
        'received_date',
        'customer_id',
        'maker',
        'year',
        'model',
        'engine_no',
        'manufacture_date',
        'title_received',
        'office_id',
        'color',
        'is_blocked',
        'created_by',
        'internal_remark',
        'tag_no',
        'vehicle_state',
        'condition',
        'key_number',
        'fuel',
        'tire_size_front',
        'tire_size_rear',
        'mileage',
        'w_sticker',
        'remote_control',
        'headphone',
        'owners_manual',
        'cd_player',
        'cd_changer',
        'first_aid_kit',
        'floor_mat',
        'cigarette_lighter',
        'cargo_net',
        'ashtray',
        'Tools',
        'spare_tire',
        'sun_roof',
    ];

    protected $casts = [
        'received_date' => 'date',
        'manufacture_date' => 'date',
        'title_received' => 'boolean',
        'is_blocked' => 'boolean',
        'w_sticker' => 'boolean',
        'remote_control' => 'boolean',
        'headphone' => 'boolean',
        'owners_manual' => 'boolean',
        'cd_player' => 'boolean',
        'cd_changer' => 'boolean',
        'first_aid_kit' => 'boolean',
        'floor_mat' => 'boolean',
        'cigarette_lighter' => 'boolean',
        'cargo_net' => 'boolean',
        'ashtray' => 'boolean',
        'Tools' => 'boolean',
        'spare_tire' => 'boolean',
        'sun_roof' => 'boolean',
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function customer()
    {
        return $this->belongsTo(TradePartner::class, 'customer_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
