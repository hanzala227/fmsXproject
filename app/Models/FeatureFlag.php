<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'is_active',
        'company_overrides'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'company_overrides' => 'array'
    ];
}
