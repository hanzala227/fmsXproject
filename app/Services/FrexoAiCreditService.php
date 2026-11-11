<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAiCreditTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FrexoAiCreditService
{
    /**
     * Check if company has sufficient credits before sending request to AI.
     */
    public function checkPreflight($companyOrId, ?float $requiredCredits = null): array
    {
        $enabled = config('frexo_ai.billing.enabled', true);
        if (!$enabled) {
            return [
                'allowed' => true,
                'balance' => 999999.0,
                'unlimited' => true,
                'message' => null,
            ];
        }

        $company = $this->resolveCompany($companyOrId);
        if (!$company) {
            return [
                'allowed' => false,
                'balance' => 0.0,
                'unlimited' => false,
                'message' => 'Company not found or invalid tenant context.',
            ];
        }

        if ($company->frexo_ai_unlimited) {
            return [
                'allowed' => true,
                'balance' => (float) $company->frexo_ai_credits,
                'unlimited' => true,
                'message' => null,
            ];
        }

        $minRequired = $requiredCredits ?? (float) config('frexo_ai.billing.min_required_credits', 1.0);
        $balance = (float) ($company->frexo_ai_credits ?? 0.0);

        if ($balance < $minRequired) {
            return [
                'allowed' => false,
                'balance' => $balance,
                'unlimited' => false,
                'message' => sprintf(
                    'Your Frexo AI credit balance is insufficient (%.2f credits remaining). Please recharge your credits to continue processing documents.',
                    $balance
                ),
            ];
        }

        return [
            'allowed' => true,
            'balance' => $balance,
            'unlimited' => false,
            'message' => null,
        ];
    }

    /**
     * Calculate token count from texts, images, and schema/prompts.
     */
    public function estimateTokens(array $documentTexts = [], array $documentImages = [], string $extraPrompt = ''): int
    {
        $totalChars = mb_strlen($extraPrompt);
        foreach ($documentTexts as $text) {
            $totalChars += mb_strlen($text);
        }

        // ~4 characters per token for English & standard freight documents
        $textTokens = (int) ceil($totalChars / 4);

        // Vision model image weight per image / page
        $imageWeight = (int) config('frexo_ai.billing.image_tokens_weight', 258);
        $imageTokens = count($documentImages) * $imageWeight;

        return max(50, $textTokens + $imageTokens);
    }

    /**
     * Convert token count into credit cost.
     */
    public function calculateCreditsFromTokens(int $tokens): float
    {
        $tokensPerCredit = (int) config('frexo_ai.billing.tokens_per_credit', 100);
        if ($tokensPerCredit <= 0) {
            $tokensPerCredit = 100;
        }

        $credits = round($tokens / $tokensPerCredit, 2);
        return max(0.1, $credits);
    }

    /**
     * Grant initial complimentary credits to a newly created company.
     */
    public function grantInitialCredits(Company $company, ?float $amount = null, ?string $note = null): ?CompanyAiCreditTransaction
    {
        $initial = $amount ?? (float) config('frexo_ai.billing.initial_company_credits', 1000.0);
        if ($initial <= 0) {
            return null;
        }

        return DB::transaction(function () use ($company, $initial, $note) {
            // Check if already granted
            $existing = CompanyAiCreditTransaction::where('company_id', $company->id)
                ->where('type', 'INITIAL_BONUS')
                ->first();

            if ($existing) {
                return $existing;
            }

            $balanceBefore = (float) ($company->frexo_ai_credits ?? 0.0);
            $balanceAfter = $balanceBefore + $initial;

            $company->update(['frexo_ai_credits' => $balanceAfter]);

            return CompanyAiCreditTransaction::create([
                'company_id' => $company->id,
                'user_id' => auth()->id(),
                'type' => 'INITIAL_BONUS',
                'credits_amount' => $initial,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tokens_used' => 0,
                'module' => 'system',
                'description' => $note ?: 'Initial complimentary Frexo AI credits for new tenant registration.',
                'metadata' => [
                    'source' => 'company_onboarding',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        });
    }

    /**
     * Concurrency-safe atomic deduction of AI credits from company balance.
     */
    public function deductCredits(
        $companyOrId,
        int $tokensUsed,
        string $module = 'extraction',
        ?string $referenceDoc = null,
        ?int $userId = null,
        array $metadata = []
    ): array {
        $enabled = config('frexo_ai.billing.enabled', true);
        $company = $this->resolveCompany($companyOrId);

        if (!$company) {
            throw new Exception("Unable to deduct AI credits: Company not found.");
        }

        $creditsToDeduct = $this->calculateCreditsFromTokens($tokensUsed);
        $userId = $userId ?? auth()->id();

        return DB::transaction(function () use ($company, $creditsToDeduct, $tokensUsed, $module, $referenceDoc, $userId, $metadata, $enabled) {
            /** @var Company $lockedCompany */
            $lockedCompany = Company::where('id', $company->id)->lockForUpdate()->first();
            $balanceBefore = (float) ($lockedCompany->frexo_ai_credits ?? 0.0);

            if ($lockedCompany->frexo_ai_unlimited || !$enabled) {
                $creditsDeducted = 0.0;
                $balanceAfter = $balanceBefore;
            } else {
                $creditsDeducted = min($balanceBefore, $creditsToDeduct);
                $balanceAfter = max(0.0, round($balanceBefore - $creditsToDeduct, 4));
                $lockedCompany->update(['frexo_ai_credits' => $balanceAfter]);
            }

            $transaction = CompanyAiCreditTransaction::create([
                'company_id' => $lockedCompany->id,
                'user_id' => $userId,
                'type' => 'USAGE_DEDUCTION',
                'credits_amount' => -1 * $creditsDeducted,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tokens_used' => $tokensUsed,
                'prompt_tokens' => $metadata['prompt_tokens'] ?? 0,
                'completion_tokens' => $metadata['completion_tokens'] ?? 0,
                'module' => $module,
                'reference_doc' => $referenceDoc,
                'description' => sprintf(
                    'Frexo AI %s extraction: %d tokens consumed (%.2f credits).',
                    str_replace('_', ' ', strtoupper($module)),
                    $tokensUsed,
                    $creditsDeducted
                ),
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'tokens_used' => $tokensUsed,
                'credits_deducted' => $creditsDeducted,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    /**
     * Top-up / Recharge company credit balance.
     */
    public function rechargeCredits(
        $companyOrId,
        float $amount,
        ?int $userId = null,
        ?string $note = null,
        string $type = 'RECHARGE',
        array $metadata = []
    ): CompanyAiCreditTransaction {
        if ($amount <= 0) {
            throw new Exception("Recharge amount must be greater than zero.");
        }

        $company = $this->resolveCompany($companyOrId);
        if (!$company) {
            throw new Exception("Company not found for credit recharge.");
        }

        $userId = $userId ?? auth()->id();

        return DB::transaction(function () use ($company, $amount, $userId, $note, $type, $metadata) {
            /** @var Company $lockedCompany */
            $lockedCompany = Company::where('id', $company->id)->lockForUpdate()->first();
            $balanceBefore = (float) ($lockedCompany->frexo_ai_credits ?? 0.0);
            $balanceAfter = round($balanceBefore + $amount, 4);

            $lockedCompany->update(['frexo_ai_credits' => $balanceAfter]);

            return CompanyAiCreditTransaction::create([
                'company_id' => $lockedCompany->id,
                'user_id' => $userId,
                'type' => in_array($type, ['RECHARGE', 'ADMIN_ADJUSTMENT', 'REFUND']) ? $type : 'RECHARGE',
                'credits_amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tokens_used' => 0,
                'module' => 'billing',
                'description' => $note ?: sprintf('Account recharge of +%.2f Frexo AI credits.', $amount),
                'metadata' => array_merge([
                    'recharged_by_user_id' => $userId,
                    'timestamp' => now()->toIso8601String(),
                ], $metadata),
            ]);
        });
    }

    /**
     * Helper to resolve Company model instance from ID or object.
     */
    protected function resolveCompany($companyOrId): ?Company
    {
        if ($companyOrId instanceof Company) {
            return $companyOrId;
        }

        if (is_numeric($companyOrId)) {
            return Company::find($companyOrId);
        }

        if (auth()->check() && auth()->user()->company_id) {
            return Company::find(auth()->user()->company_id);
        }

        return null;
    }
}
