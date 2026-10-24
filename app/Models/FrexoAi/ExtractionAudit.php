<?php

namespace App\Models\FrexoAi;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractionAudit extends Model
{
    use BelongsToTenant;

    protected $table = 'frexo_extraction_audits';

    protected $fillable = [
        'run_id',
        'company_id',
        'user_id',
        'field_path',
        'old_value',
        'new_value',
        'source_document_id',
        'source_page',
        'status',
        'model_version',
        'prompt_version',
    ];

    protected $casts = [
        'source_page' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ExtractionRun::class, 'run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
