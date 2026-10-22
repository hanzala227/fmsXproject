<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierApiConfig extends Model
{
    use HasFactory;

    protected $table = 'carrier_api_configs';

    protected $fillable = [
        'carrier_code',
        'name',
        'base_url',
        'client_id',
        'client_secret',
        'api_key',
        'environment',
        'is_active',
        'enable_simulation',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enable_simulation' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get carrier configuration by code.
     */
    public static function getCarrier(string $code): ?self
    {
        return static::where('carrier_code', $code)->first();
    }

    /**
     * Determine if carrier has active API credentials.
     */
    public function hasCredentials(): bool
    {
        if ($this->carrier_code === 'maersk') {
            return !empty($this->api_key) || !empty($this->client_id);
        }

        if ($this->carrier_code === 'hapag_lloyd') {
            return !empty($this->client_id) || !empty($this->api_key);
        }

        return !empty($this->base_url);
    }
}

