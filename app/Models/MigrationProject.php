<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MigrationProject extends Model
{
    protected $fillable = [
        'company_id',
        'project_code',
        'source_system',
        'source_type',
        'status',
        'progress',
        'total_records',
        'processed_records',
        'error_count',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function connection(): HasOne
    {
        return $this->hasOne(MigrationConnection::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(MigrationMapping::class);
    }
}
