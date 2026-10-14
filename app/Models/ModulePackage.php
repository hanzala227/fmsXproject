<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'price',
        'module_keys',
        'is_active'
    ];

    protected $casts = [
        'module_keys' => 'array',
        'is_active' => 'boolean'
    ];
}
