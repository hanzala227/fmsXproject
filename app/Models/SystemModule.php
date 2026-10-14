<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'category',
        'version',
        'status',
        'release_date',
        'description',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'release_date' => 'date'
    ];

    public function companies()
    {
        // Using the existing company_modules table to map active modules for companies
        return $this->hasMany(CompanyModule::class, 'module_key', 'key');
    }
}
