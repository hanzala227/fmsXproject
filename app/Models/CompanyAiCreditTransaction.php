<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAiCreditTransaction extends Model
{
    use HasFactory;

    protected $table = 'company_ai_credit_transactions';

    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'credits_amount',
        'balance_before',
        'balance_after',
        'tokens_used',
        'prompt_tokens',
        'completion_tokens',
        'module',
        'reference_doc',
        'description',
        'metadata',
    ];

    protected $casts = [
        'credits_amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'tokens_used' => 'integer',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
