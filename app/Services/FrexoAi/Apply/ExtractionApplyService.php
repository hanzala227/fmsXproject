<?php

namespace App\Services\FrexoAi\Apply;

use App\Models\FrexoAi\ExtractionAudit;
use App\Models\FrexoAi\ExtractionRun;
use App\Models\FrexoAi\ReviewDecision;
use App\Services\FrexoAi\Registry\FieldRegistry;
use App\Services\FrexoAi\Validation\ContainerTypeNormalizer;
use App\Services\FrexoAi\Validation\DateLogicValidator;
use App\Services\FrexoAi\Validation\IataMawbValidator;
use App\Services\FrexoAi\Validation\Iso6346ContainerValidator;
use App\Services\FrexoAi\Validation\PortCodeValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class ExtractionApplyService
{
    public Iso6346ContainerValidator $containerValidator;

    public ContainerTypeNormalizer $containerTypeNormalizer;

    public IataMawbValidator $mawbValidator;

    public PortCodeValidator $portCodeValidator;

    public DateLogicValidator $dateLogicValidator;

    public function __construct(
        ?Iso6346ContainerValidator $containerValidator = null,
        ?ContainerTypeNormalizer $containerTypeNormalizer = null,
        ?IataMawbValidator $mawbValidator = null,
        ?PortCodeValidator $portCodeValidator = null,
        ?DateLogicValidator $dateLogicValidator = null
    ) {
        $this->containerValidator = $containerValidator ?? new Iso6346ContainerValidator;
        $this->containerTypeNormalizer = $containerTypeNormalizer ?? new ContainerTypeNormalizer;
        $this->mawbValidator = $mawbValidator ?? new IataMawbValidator;
        $this->portCodeValidator = $portCodeValidator ?? new PortCodeValidator;
        $this->dateLogicValidator = $dateLogicValidator ?? new DateLogicValidator;
    }

    /**
     * Revalidates accepted fields, enforces overwrite policies and immutability.
     * Pure validation logic with no DB side-effects.
     *
     * @throws InvalidArgumentException
     */
    public function validateAndFilterFields(
        string $module,
        array $acceptedFields,
        array $existingValues = [],
        ?int $runId = null,
        ?int $companyId = null,
        ?int $userId = null
    ): array {
        $appliedData = [];
        $auditsToCreate = [];
        $decisionsToCreate = [];

        foreach ($acceptedFields as $item) {
            $fieldPath = $item['field_path'] ?? '';
            $value = $item['value'] ?? null;
            $rawValue = $item['raw_value'] ?? $value;
            $sourceDocId = $item['source_document_id'] ?? null;
            $sourcePage = (int) ($item['source_page'] ?? 1);
            $userConfirmed = (bool) ($item['user_confirmed'] ?? false);

            if (empty($fieldPath)) {
                continue;
            }

            // 1. Guard against immutable fields (file_no, id, company_id)
            $normalizedPath = strtolower($fieldPath);
            if (
                str_ends_with($normalizedPath, 'file_no') ||
                str_ends_with($normalizedPath, '.id') ||
                $normalizedPath === 'id' ||
                $normalizedPath === 'company_id'
            ) {
                throw new InvalidArgumentException("OVERWRITE_POLICY_VIOLATION: Forbidden attempt to overwrite immutable field: {$fieldPath}");
            }

            // 2. Guard against INTERNAL_WORKFLOW fields
            $fieldDef = FieldRegistry::getField($module, $fieldPath);
            if ($fieldDef && $fieldDef->sourcePolicy === 'INTERNAL_WORKFLOW') {
                throw new InvalidArgumentException("OVERWRITE_POLICY_VIOLATION: Cannot apply or overwrite internal workflow field: {$fieldPath}");
            }

            // 3. Overwrite policy checks ('never', 'only_if_empty')
            $oldValue = $existingValues[$fieldPath] ?? null;

            if ($fieldDef) {
                if ($fieldDef->overwritePolicy === 'never' && ! empty($oldValue)) {
                    throw new InvalidArgumentException("OVERWRITE_POLICY_VIOLATION: Overwrite policy for {$fieldPath} is 'never'.");
                }
                if ($fieldDef->overwritePolicy === 'only_if_empty' && ! empty($oldValue) && ! $userConfirmed) {
                    throw new InvalidArgumentException("OVERWRITE_POLICY_VIOLATION: Field {$fieldPath} already has a value and user_confirmed is false.");
                }
            }

            // 4. Deterministic Revalidation with Chunk 4 Validators
            $normalizedValue = $value;

            if (str_contains($normalizedPath, 'container_no')) {
                $valResult = $this->containerValidator->validate((string) $value);
                if ($valResult->isFail()) {
                    throw new InvalidArgumentException("VALIDATION_FAILURE [{$valResult->code}]: {$valResult->message}");
                }
                $normalizedValue = $valResult->normalizedValue ?? $value;
            } elseif (str_contains($normalizedPath, 'container_type')) {
                $valResult = $this->containerTypeNormalizer->normalize((string) $value);
                $normalizedValue = $valResult->normalizedValue ?? $value;
            } elseif (str_contains($normalizedPath, 'mawb_no') || str_contains($normalizedPath, 'mawb')) {
                $valResult = $this->mawbValidator->validate((string) $value);
                if ($valResult->isFail()) {
                    throw new InvalidArgumentException("VALIDATION_FAILURE [{$valResult->code}]: {$valResult->message}");
                }
                $normalizedValue = $valResult->normalizedValue ?? $value;
            } elseif (str_contains($normalizedPath, 'pol_id') || str_contains($normalizedPath, 'pod_id') || str_ends_with($normalizedPath, '_code')) {
                if (is_string($value) && strlen($value) === 5) {
                    $valResult = $this->portCodeValidator->validateUnLocode($value);
                    if ($valResult->normalizedValue) {
                        $normalizedValue = $valResult->normalizedValue;
                    }
                }
            } elseif (str_contains($normalizedPath, 'eta') || str_contains($normalizedPath, 'etd') || str_contains($normalizedPath, 'date')) {
                $valResult = $this->dateLogicValidator->validateDateFormat((string) $value);
                if ($valResult->isFail()) {
                    throw new InvalidArgumentException("VALIDATION_FAILURE [{$valResult->code}]: {$valResult->message}");
                }
                $normalizedValue = $valResult->normalizedValue ?? $value;
            }

            $appliedData[$fieldPath] = $normalizedValue;

            $auditsToCreate[] = [
                'run_id' => $runId,
                'company_id' => $companyId,
                'user_id' => $userId,
                'field_path' => $fieldPath,
                'old_value' => is_scalar($oldValue) ? (string) $oldValue : json_encode($oldValue),
                'new_value' => is_scalar($normalizedValue) ? (string) $normalizedValue : json_encode($normalizedValue),
                'source_document_id' => $sourceDocId,
                'source_page' => $sourcePage,
                'status' => 'APPLIED',
                'model_version' => config('frexo_ai.model', 'gemini-1.5-pro'),
                'prompt_version' => 'v1.0',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $decisionsToCreate[] = [
                'run_id' => $runId,
                'company_id' => $companyId,
                'user_id' => $userId,
                'field_path' => $fieldPath,
                'decision' => 'ACCEPTED',
                'original_value' => is_scalar($rawValue) ? (string) $rawValue : json_encode($rawValue),
                'final_value' => is_scalar($normalizedValue) ? (string) $normalizedValue : json_encode($normalizedValue),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return [
            'applied_data' => $appliedData,
            'audits' => $auditsToCreate,
            'decisions' => $decisionsToCreate,
        ];
    }

    /**
     * Apply validated extraction fields within a DB transaction.
     * Enforces tenant isolation, idempotency, optimistic locking, overwrite policies, and audit logging.
     *
     * @throws InvalidArgumentException|ValidationException|RuntimeException
     */
    public function apply(array $payload): array
    {
        // 1. Tenant Safety: Resolve authenticated company_id strictly from server session
        $user = Auth::user();
        if (! $user || empty($user->company_id)) {
            throw new InvalidArgumentException('Unauthorized: Valid authenticated tenant session required.');
        }
        $companyId = (int) $user->company_id;
        $userId = (int) $user->id;

        $runUuid = $payload['run_uuid'] ?? null;
        if (! $runUuid) {
            throw new InvalidArgumentException('Missing required parameter: run_uuid');
        }

        $idempotencyToken = $payload['idempotency_token'] ?? null;
        if (! $idempotencyToken) {
            throw new InvalidArgumentException('Missing required parameter: idempotency_token');
        }

        // 2. Idempotency Check: prevent duplicate execution
        $existingRun = ExtractionRun::where('company_id', $companyId)
            ->where('idempotency_token', $idempotencyToken)
            ->where('status', 'APPLIED')
            ->first();

        if ($existingRun) {
            return [
                'success' => true,
                'status' => 'ALREADY_APPLIED',
                'run_uuid' => $existingRun->uuid,
                'message' => 'This extraction was already applied (idempotent replay).',
                'applied_fields_count' => $existingRun->reviewDecisions()->where('decision', 'ACCEPTED')->count(),
                'applied_data' => $existingRun->processing_summary['applied_data'] ?? [],
            ];
        }

        // 3. Find target extraction run scoped strictly to company_id (Prevents cross-tenant ID guessing)
        $run = ExtractionRun::where('uuid', $runUuid)
            ->where('company_id', $companyId)
            ->first();

        if (! $run) {
            throw new InvalidArgumentException('Extraction run not found or forbidden for current tenant.');
        }

        // 4. Optimistic Version Check
        $expectedFormVersion = $payload['expected_form_version'] ?? null;
        $currentFormVersion = $payload['current_form_version'] ?? null;
        if ($expectedFormVersion !== null && $currentFormVersion !== null) {
            if ($expectedFormVersion !== $currentFormVersion) {
                throw new RuntimeException('STALE_FORM_STATE: Form was modified since extraction started.');
            }
        }

        $module = $payload['module'] ?? $run->module;
        $existingValues = $payload['existing_values'] ?? [];
        $acceptedFields = $payload['accepted_fields'] ?? [];
        $rejectedFields = $payload['rejected_fields'] ?? [];

        // 5. Overwrite Policy & Deterministic Revalidation
        $filtered = $this->validateAndFilterFields(
            $module,
            $acceptedFields,
            $existingValues,
            $run->id,
            $companyId,
            $userId
        );

        $appliedData = $filtered['applied_data'];
        $auditsToCreate = $filtered['audits'];
        $decisionsToCreate = $filtered['decisions'];

        foreach ($rejectedFields as $item) {
            $fieldPath = $item['field_path'] ?? '';
            if (empty($fieldPath)) {
                continue;
            }
            $decisionsToCreate[] = [
                'run_id' => $run->id,
                'company_id' => $companyId,
                'user_id' => $userId,
                'field_path' => $fieldPath,
                'decision' => 'REJECTED',
                'original_value' => isset($item['raw_value']) ? (string) $item['raw_value'] : null,
                'final_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 6. DB Transaction Execution
        DB::transaction(function () use ($run, $idempotencyToken, $appliedData, $auditsToCreate, $decisionsToCreate) {
            if (! empty($auditsToCreate)) {
                ExtractionAudit::insert($auditsToCreate);
            }

            if (! empty($decisionsToCreate)) {
                ReviewDecision::insert($decisionsToCreate);
            }

            $summary = $run->processing_summary ?? [];
            $summary['applied_data'] = $appliedData;
            $summary['applied_at'] = now()->toIso8601String();

            $run->status = 'APPLIED';
            $run->idempotency_token = $idempotencyToken;
            $run->processing_summary = $summary;
            $run->save();
        });

        // 7. Retention: Safely remove temporary files associated with this run (never log full text)
        $tempDir = storage_path("app/frexo_temp/{$run->uuid}");
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }

        return [
            'success' => true,
            'status' => 'APPLIED',
            'run_uuid' => $run->uuid,
            'applied_fields_count' => count($auditsToCreate),
            'rejected_fields_count' => count($rejectedFields),
            'applied_data' => $appliedData,
        ];
    }
}
