<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class TradePartner extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (is_null($model->credit_term_days)) {
                $model->credit_term_days = 0;
            }
            if (is_null($model->credit_limit)) {
                $model->credit_limit = 0.00;
            }
            if (is_null($model->profit_share_percent)) {
                $model->profit_share_percent = 0.00;
            }
            if (is_null($model->track_1099)) {
                $model->track_1099 = false;
            }
            if (is_null($model->bill_to_agent)) {
                $model->bill_to_agent = false;
            }
        });
    }

    protected $fillable = [
        'company_id', 'type', 'code', 'alias', 'name', 'print_name', 'local_name', 'local_address',
        'city', 'state', 'zip_code', 'country_id', 'iata_code', 'corporation_no',
        'sita_profile', 'account_no', 'scac_code', 'firms_code', 'cbsa_carrier_code',
        'phone', 'fax', 'url', 'email', 'status', 'sales_office_id', 'sales_person_id',
        'cs_person_id', 'billing_address', 'tax_id', 'payment_type', 'track_1099',
        'bill_to_agent', 'clm_id', 'credit_term_days', 'credit_limit', 'accountant_name',
        'bank_account_name_1', 'bank_account_no_1', 'bank_currency_1_id',
        'bank_account_name_2', 'bank_account_no_2', 'bank_currency_2_id',
        'profit_share_percent', 'popup_tips', 'remark',
        'account_group_id', 'credit_limit_group_id', 'aeo', 'credit_term_unit',
        'print_address_use_default', 'print_address_show_name',
        'print_address_show_address', 'print_address_show_contact',
        'additional_addresses',
    ];

    protected $casts = [
        'track_1099' => 'boolean',
        'bill_to_agent' => 'boolean',
        'popup_tips' => 'json',
        'additional_addresses' => 'array',
        'credit_limit' => 'decimal:2',
        'profit_share_percent' => 'decimal:2',
        'print_address_use_default' => 'boolean',
        'print_address_show_name' => 'boolean',
        'print_address_show_address' => 'boolean',
        'print_address_show_contact' => 'boolean',
    ];

    public function getTypeAttribute($value)
    {
        $map = [
            'CLIENT' => 'CS',
            'CONSIGNEE' => 'CN',
            'SHIPPER_KNOWN' => 'KS',
            'SHIPPER_UNKNOWN' => 'SH',
            'CARRIER' => 'CR',
            'AIR_CARRIER' => 'AC',
            'AGENT' => 'PR',
            'FORWARDER' => 'FR',
            'CUSTOMS_BROKER' => 'CB',
            'TRUCKER' => 'TK',
            'WAREHOUSE' => 'WH',
            'VENDOR' => 'VR',
            'BANK' => 'BK',
            'BOOKING_WINDOW' => 'BW',
            'CFS' => 'CF',
            'CY' => 'CY',
            'EMPLOYEE' => 'EM',
            'FBA_WAREHOUSE' => 'FB',
            'GOVERNMENT' => 'GV',
            'MANUFACTURER' => 'MF',
            'OFFICE_EXPENSE' => 'OE',
            'OTHER' => 'OT',
            'RAIL_COMPANY' => 'RL',
            'RAMP_LOCATION' => 'RC',
            'TERMINAL' => 'TM',
        ];
        return ($value !== null && isset($map[$value])) ? $map[$value] : $value;
    }

    public function setTypeAttribute($value)
    {
        $map = [
            'CS' => 'CLIENT',
            'CN' => 'CONSIGNEE',
            'KS' => 'SHIPPER_KNOWN',
            'SH' => 'SHIPPER_UNKNOWN',
            'AC' => 'AIR_CARRIER',
            'CR' => 'CARRIER',
            'PR' => 'AGENT',
            'FR' => 'FORWARDER',
            'CB' => 'CUSTOMS_BROKER',
            'TK' => 'TRUCKER',
            'WH' => 'WAREHOUSE',
            'VR' => 'VENDOR',
            'BK' => 'BANK',
            'BW' => 'BOOKING_WINDOW',
            'CF' => 'CFS',
            'CY' => 'CY',
            'EM' => 'EMPLOYEE',
            'FB' => 'FBA_WAREHOUSE',
            'GV' => 'GOVERNMENT',
            'MF' => 'MANUFACTURER',
            'OE' => 'OFFICE_EXPENSE',
            'OT' => 'OTHER',
            'RL' => 'RAIL_COMPANY',
            'RC' => 'RAMP_LOCATION',
            'TM' => 'TERMINAL',
            'CLIENT' => 'CLIENT',
            'CONSIGNEE' => 'CONSIGNEE',
            'SHIPPER_KNOWN' => 'SHIPPER_KNOWN',
            'SHIPPER_UNKNOWN' => 'SHIPPER_UNKNOWN',
            'CARRIER' => 'CARRIER',
            'AIR_CARRIER' => 'AIR_CARRIER',
            'AGENT' => 'AGENT',
            'FORWARDER' => 'FORWARDER',
            'CUSTOMS_BROKER' => 'CUSTOMS_BROKER',
            'TRUCKER' => 'TRUCKER',
            'WAREHOUSE' => 'WAREHOUSE',
            'VENDOR' => 'VENDOR',
            'BANK' => 'BANK',
            'BOOKING_WINDOW' => 'BOOKING_WINDOW',
            'CFS' => 'CFS',
            'CY' => 'CY',
            'EMPLOYEE' => 'EMPLOYEE',
            'FBA_WAREHOUSE' => 'FBA_WAREHOUSE',
            'GOVERNMENT' => 'GOVERNMENT',
            'MANUFACTURER' => 'MANUFACTURER',
            'OFFICE_EXPENSE' => 'OFFICE_EXPENSE',
            'OTHER' => 'OTHER',
            'RAIL_COMPANY' => 'RAIL_COMPANY',
            'RAMP_LOCATION' => 'RAMP_LOCATION',
            'TERMINAL' => 'TERMINAL',
        ];
        $this->attributes['type'] = $map[$value] ?? $value;
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function creditLimitGroup()
    {
        return $this->belongsTo(CreditLimitGroup::class, 'credit_limit_group_id');
    }

    public function salesOffice()
    {
        return $this->belongsTo(Office::class, 'sales_office_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function csPerson()
    {
        return $this->belongsTo(User::class, 'cs_person_id');
    }

    public function contacts()
    {
        return $this->hasMany(TradePartnerContact::class);
    }

    public function memos()
    {
        return $this->hasMany(TradePartnerMemo::class);
    }

    public function defaultFreights()
    {
        return $this->hasMany(TradePartnerDefaultFreight::class);
    }

    public function commodities()
    {
        return $this->hasMany(TradePartnerCommodity::class);
    }

    public function filingSettings()
    {
        return $this->hasOne(TradePartnerFilingSetting::class);
    }

    public function relatedParties()
    {
        return $this->hasMany(TradePartnerParty::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getActivityLogs()
    {
        $logs = [];
        
        // Creation log
        if ($this->created_at) {
            $logs[] = [
                'date' => $this->created_at->format('m-d-Y'),
                'time' => $this->created_at->format('H:i'),
                'icon' => 'C',
                'action' => 'Customer Created',
                'user' => $this->salesPerson?->name ?? 'System',
                'info' => null
            ];
        }
        
        // Update log
        if ($this->updated_at && $this->updated_at->ne($this->created_at)) {
            $logs[] = [
                'date' => $this->updated_at->format('m-d-Y'),
                'time' => $this->updated_at->format('H:i'),
                'icon' => 'U',
                'action' => 'Customer Updated',
                'user' => auth()->user()?->name ?? 'System',
                'info' => 'Customer profile was updated.'
            ];
        }
        
        // Document logs
        foreach ($this->documents()->latest()->get() as $doc) {
            $logs[] = [
                'date' => $doc->created_at->format('m-d-Y'),
                'time' => $doc->created_at->format('H:i'),
                'icon' => 'D',
                'action' => 'Document Uploaded',
                'user' => $doc->uploader?->name ?? 'System',
                'info' => 'File name: ' . $doc->file_name
            ];
        }
        
        return collect($logs)->sortByDesc(function ($log) {
            return $log['date'] . ' ' . $log['time'];
        })->values()->toArray();
    }

    /**
     * Get the formatted address block for printing.
     */
    public function getAddressAttribute(): string
    {
        $addressParts = [];
        
        if (!empty($this->billing_address)) {
            $addressParts[] = trim($this->billing_address);
        } elseif (!empty($this->local_address)) {
            $addressParts[] = trim($this->local_address);
        }
        
        $csz = [];
        if (!empty($this->city)) {
            $csz[] = trim($this->city);
        }
        if (!empty($this->state)) {
            $csz[] = trim($this->state);
        }
        if (!empty($this->zip_code)) {
            $csz[] = trim($this->zip_code);
        }
        
        if (!empty($csz)) {
            $cszString = implode(', ', $csz);
            $mainAddress = count($addressParts) > 0 ? $addressParts[0] : '';
            $mainAddressLower = strtolower($mainAddress);
            $cityLower = !empty($this->city) ? strtolower(trim($this->city)) : null;
            
            if (empty($mainAddress) || ($cityLower && strpos($mainAddressLower, $cityLower) === false)) {
                $addressParts[] = $cszString;
            }
        }
        
        if ($this->country) {
            $addressParts[] = trim($this->country->name);
        }
        
        return implode("\n", $addressParts);
    }
}

