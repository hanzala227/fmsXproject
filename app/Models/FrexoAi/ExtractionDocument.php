<?php

namespace App\Models\FrexoAi;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractionDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'frexo_extraction_documents';

    protected $fillable = [
        'run_id',
        'company_id',
        'document_id',
        'filename',
        'document_type',
        'pages_total',
        'pages_processed',
        'status',
        'limitations',
    ];

    protected $casts = [
        'limitations' => 'array',
        'pages_total' => 'integer',
        'pages_processed' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ExtractionRun::class, 'run_id');
    }
}

