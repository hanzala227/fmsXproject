<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'code',
        'country',
        'email',
        'phone',
        'address',
        'subdomain',
        'status',
        'plan_id',
        'subscription_status',
        'storage_limit',
        'user_limit',
        'default_language',
        'timezone',
        'logo',
        'subscription_ends_at',
    ];

    protected $casts = [
        'subscription_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function modules()
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function hasModule(string $key): bool
    {
        // 1. Direct company_modules check (e.g. 'trade-partners', 'ocean-import', 'truck')
        if ($this->modules()->where('module_key', $key)->exists()) {
            return true;
        }

        // 2. Underscore / Dash variant check (e.g. 'ocean_import' vs 'ocean-import', 'master_data' vs 'master-data')
        $altKey = str_contains($key, '-') ? str_replace('-', '_', $key) : str_replace('_', '-', $key);
        if ($this->modules()->where('module_key', $altKey)->exists()) {
            return true;
        }

        // 3. Aliases mapping (e.g. 'trade-partners' <-> 'master_data' / 'trade_partners', 'truck' <-> 'trucking')
        $aliases = [
            'trade-partners' => ['master_data', 'master-data', 'trade_partners', 'trade_partner', 'customers'],
            'trade_partners' => ['master_data', 'master-data', 'trade-partners', 'trade_partner', 'customers'],
            'truck' => ['trucking'],
            'trucking' => ['truck'],
            'sales' => ['crm'],
            'crm' => ['sales'],
            'reports' => ['report'],
            'report' => ['reports'],
        ];

        if (isset($aliases[$key])) {
            if ($this->modules()->whereIn('module_key', $aliases[$key])->exists()) {
                return true;
            }
        }

        // 4. Fallback: If company has user permissions that grant this module, allow access
        $moduleMapping = [
            'Ocean Import' => 'ocean-import',
            'Ocean Export' => 'ocean-export',
            'Air Import' => 'air-import',
            'Air Export' => 'air-export',
            'Truck Shipment' => 'truck',
            'Custom Brokerage' => 'ocean-import',
            'Warehouse & Distribution' => 'warehouse',
            'Accounting' => 'accounting',
            'CRM' => 'sales',
            'Customers' => 'trade-partners',
            'Master Data' => 'trade-partners',
            'Reports' => 'reports',
            'Report' => 'reports',
            'General' => 'reports',
            'Settings' => 'settings'
        ];

        // Check if any permission assigned to users in this company maps to $key
        $userModuleNames = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->join('users', 'model_has_permissions.model_id', '=', 'users.id')
            ->where('users.company_id', $this->id)
            ->pluck('permissions.module_name')
            ->toArray();

        foreach ($userModuleNames as $modStr) {
            foreach (array_map('trim', explode(',', $modStr ?? '')) as $mName) {
                if (($moduleMapping[$mName] ?? null) === $key) {
                    return true;
                }
            }
        }

        return false;
    }

    public function moduleKeys(): array
    {
        return $this->modules()->pluck('module_key')->toArray();
    }
}
