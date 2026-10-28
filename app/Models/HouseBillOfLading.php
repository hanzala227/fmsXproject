<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OceanImport;

class HouseBillOfLading extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'ocean_import_hbls';

    protected $fillable = [
        'company_id',
        'ocean_import_id',
        'hbl_number',
        'customer_id',
        'shipper_id',
        'consignee_id',
        'notify_party_id',
        'bill_to_party_id',
        'sales_person_id',
        'customs_broker_id',
        'trucker_id',
        'incoterms',
        'quotation_no',
        'ams_no',
        'isf_no',
        'is_express',
        'is_door_move',
        'is_customs_clearance',
        'customs_clearance_date',
        'is_freight_released',
        'freight_released_date',
        'entry_no',
        'entry_date',
        'status',
    ];

    protected $casts = [
        'is_express' => 'boolean',
        'is_door_move' => 'boolean',
        'is_customs_clearance' => 'boolean',
        'is_freight_released' => 'boolean',
        'customs_clearance_date' => 'date',
        'freight_released_date' => 'date',
        'entry_date' => 'date',
    ];

    public function oceanImport()
    {
        return $this->belongsTo(OceanImport::class);
    }
}
