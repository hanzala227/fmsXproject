<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationMapping extends Model
{
    protected $fillable = [
        'migration_project_id',
        'source_table',
        'target_table',
        'status',
        'ai_confidence',
        'field_mappings',
    ];

    protected $casts = [
        'field_mappings' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MigrationProject::class, 'migration_project_id');
    }
}
