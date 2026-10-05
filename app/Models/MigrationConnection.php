<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationConnection extends Model
{
    protected $fillable = [
        'migration_project_id',
        'db_host',
        'db_port',
        'db_database',
        'db_username',
        'db_password',
        'use_ssl',
        'file_path',
        'api_url',
        'api_key',
    ];

    protected $hidden = [
        'db_password',
        'api_key',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MigrationProject::class, 'migration_project_id');
    }
}
