<?php

namespace App\Models\FrexoAi;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewDecision extends Model
{
    use BelongsToTenant;

    protected $table = 'frexo_review_decisions';

    protected $fillable = [
        'run_id',
        'company_id',
        'user_id',
        'field_path',
        'decision',
        'original_value',
        'final_value',
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
