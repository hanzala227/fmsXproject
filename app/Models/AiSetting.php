<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'api_type',
        'base_url',
        'api_key',
        'model_name'
    ];

    /**
     * Helper to get the single settings instance
     */
    public static function current()
    {
        return self::first();
    }
}
