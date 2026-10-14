<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OceanExportHblCommodity extends Model
{
    use HasFactory;

    protected $table = 'ocean_export_hbl_commodities';

    protected $fillable = [
        'hbl_id',
        'hbl_no',
        'commodity_desc',
        'hts_code',
        'container_no',
        'po_no'
    ];

    public function hbl()
    {
        return $this->belongsTo(OceanExportHbl::class, 'hbl_id');
    }
}
