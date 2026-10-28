<?php

namespace App\Models\FrexoAi;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldEvidence extends Model
{
    use BelongsToTenant;

    protected $table = 'frexo_field_evidence';

    protected $fillable = [
        'run_id',
        'company_id',
        'field_path',
        'scope',
        'raw_value',
        'normalized_value',
        'unit',
        'status',
        'source_document_id',
        'source_page',
        'evidence_text',
        'derivation',
    ];

    protected $casts = [
        'derivation' => 'array',
        'source_page' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ExtractionRun::class, 'run_id');
    }
}

