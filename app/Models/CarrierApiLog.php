<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierApiLog extends Model
{
    use HasFactory;

    protected $table = 'carrier_api_logs';

    protected $fillable = [
        'carrier_code',
        'endpoint',
        'method',
        'tracking_number',
        'request_payload',
        'response_status',
        'response_payload',
        'duration_ms',
        'error_message',
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'response_status' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

