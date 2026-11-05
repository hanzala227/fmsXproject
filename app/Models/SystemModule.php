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

    /**
     * Get real active companies count using freight SaaS tenancy rules.
     */
    public function getActiveCompaniesCountAttribute(): int
    {
        if (in_array($this->category, ['Core', 'Platform', 'Operations', 'Configuration'])) {
            return Company::count();
        }

        $directCount = $this->companies()->distinct('company_id')->count('company_id');
        if ($directCount > 0) {
            return $directCount;
        }

        // Fallback check for hyphen/underscore variant
        $altKey = str_contains($this->key, '_') ? str_replace('_', '-', $this->key) : str_replace('-', '_', $this->key);
        return CompanyModule::where('module_key', $altKey)->distinct('company_id')->count('company_id');
    }

    /**
     * Get collection of companies actively utilizing this module.
     */
    public function getActiveCompaniesListAttribute()
    {
        if (in_array($this->category, ['Core', 'Platform', 'Operations', 'Configuration'])) {
            return Company::select('id', 'name', 'code')->get();
        }

        $altKey = str_contains($this->key, '_') ? str_replace('_', '-', $this->key) : str_replace('-', '_', $this->key);
        $companyIds = CompanyModule::whereIn('module_key', [$this->key, $altKey])->pluck('company_id');

        return Company::whereIn('id', $companyIds)->select('id', 'name', 'code')->get();
    }
}
