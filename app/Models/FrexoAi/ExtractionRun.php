<?php

namespace App\Models\FrexoAi;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtractionRun extends Model
{
    use BelongsToTenant;

    protected $table = 'frexo_extraction_runs';

    protected $fillable = [
        'uuid',
        'company_id',
        'user_id',
        'module',
        'schema_version',
        'status',
        'idempotency_token',
        'processing_summary',
    ];

    protected $casts = [
        'processing_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ExtractionDocument::class, 'run_id');
    }

    public function fieldEvidence(): HasMany
    {
        return $this->hasMany(FieldEvidence::class, 'run_id');
    }

    public function reviewDecisions(): HasMany
    {
        return $this->hasMany(ReviewDecision::class, 'run_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ExtractionAudit::class, 'run_id');
    }
}
