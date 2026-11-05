<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'created_by_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'office_code',
        'office_name',
        'department_code',
        'department_name',
        'branch',
        'role',
        'accessible_modules',
        'status',
        'create_date',
        'last_login_at',
        'trade_partner_id',
        'department',
        'profile_photo_path',
        'mfa_enabled',
        'access_expiry',
        'last_login_ip',
    ];

    public function tradePartner()
    {
        return $this->belongsTo(TradePartner::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the office relation by office_code.
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_code', 'code');
    }

    /**
     * Accessor for assigned Office model.
     */
    public function getAssignedOfficeAttribute()
    {
        if (!empty($this->office_code)) {
            $office = Office::where('code', $this->office_code)
                ->orWhere('name', 'like', '%' . $this->office_code . '%')
                ->first();
            if ($office) {
                return $office;
            }
        }
        if (!empty($this->office_name)) {
            $office = Office::where('name', 'like', '%' . $this->office_name . '%')
                ->orWhere('code', $this->office_name)
                ->first();
            if ($office) {
                return $office;
            }
        }
        if (!empty($this->branch)) {
            $office = Office::where('name', 'like', '%' . $this->branch . '%')
                ->orWhere('code', $this->branch)
                ->first();
            if ($office) {
                return $office;
            }
        }
        return null;
    }

    /**
     * Accessor for assigned Office ID.
     */
    public function getAssignedOfficeIdAttribute()
    {
        return $this->assigned_office?->id;
    }

    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SuperAdmin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'Customer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isActive(): bool
    {
        return $this->status === 'Enable';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'created_by_id');
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        // SuperAdmin has access to everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Company Admins have access to all modules subscribed by their company
        if ($this->isAdmin()) {
            return $this->company ? $this->company->hasModule($moduleKey) : true;
        }

        // Check explicit accessible_modules configured by their Admin
        if (!empty($this->accessible_modules) && is_array($this->accessible_modules)) {
            $keyAlt = str_contains($moduleKey, '-') ? str_replace('-', '_', $moduleKey) : str_replace('_', '-', $moduleKey);
            return in_array($moduleKey, $this->accessible_modules) || in_array($keyAlt, $this->accessible_modules);
        }

        // Check Spatie permissions mapping
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

        $userPermModules = $this->permissions->pluck('module_name')
            ->map(fn($m) => array_map('trim', explode(',', $m)))
            ->flatten()
            ->unique()
            ->toArray();

        foreach ($userPermModules as $uMod) {
            if (($moduleMapping[$uMod] ?? null) === $moduleKey) {
                return true;
            }
        }

        // Fallback to company module subscription
        return $this->company ? $this->company->hasModule($moduleKey) : false;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'create_date' => 'datetime',
            'last_login_at' => 'datetime',
            'access_expiry' => 'date',
            'mfa_enabled' => 'boolean',
            'accessible_modules' => 'array',
        ];
    }
}
