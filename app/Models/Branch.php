<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'branch_type',
        'is_head_office',
        'iata_code',
        'unlocode',
        'country',
        'city',
        'state_province',
        'postal_code',
        'address',
        'manager_name',
        'email',
        'phone',
        'fax',
        'timezone',
        'currency',
        'tax_number',
        'customs_code',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_head_office' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Parent freight forwarder company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope for active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filtered by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
