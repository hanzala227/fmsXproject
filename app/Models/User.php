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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SuperAdmin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'Customer';
    }

    public function isActive(): bool
    {
        return $this->status === 'Enable';
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
        ];
    }
}
