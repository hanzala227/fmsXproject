<?php

namespace App\Services\FrexoAi\Observability;

use Illuminate\Support\Facades\Log;

class FrexoAiLogger
{
    /**
     * Sanitize context data before logging to ensure zero API keys or full document text are persisted.
     */
    public static function sanitize(array $context): array
    {
        $sanitized = [];
        $forbiddenKeys = ['api_key', 'authorization', 'bearer', 'password', 'secret', 'token'];

        foreach ($context as $k => $v) {
            $lowerKey = strtolower((string) $k);

            // Strip secret keys
            foreach ($forbiddenKeys as $forbidden) {
                if (str_contains($lowerKey, $forbidden)) {
                    $sanitized[$k] = '[REDACTED_SECRET]';
                    continue 2;
                }
            }

            // Never log full raw document text or large base64 payloads
            if ($lowerKey === 'content' || $lowerKey === 'raw_text' || $lowerKey === 'document_text' || $lowerKey === 'file_content') {
                $sanitized[$k] = is_string($v) ? '[DOCUMENT_TEXT_REDACTED_LEN_' . strlen($v) . ']' : '[REDACTED]';
                continue;
            }

            if (is_array($v)) {
                $sanitized[$k] = self::sanitize($v);
            } elseif (is_string($v) && strlen($v) > 500) {
                // Truncate overly long strings
                $sanitized[$k] = substr($v, 0, 500) . '...[TRUNCATED_LEN_' . strlen($v) . ']';
            } else {
                $sanitized[$k] = $v;
            }
        }

        return $sanitized;
    }

    public static function logExtractionStarted(string $runUuid, string $module, int $docCount, array $extra = []): void
    {
        Log::info('[FrexoAI] Extraction Started', self::sanitize(array_merge([
            'event' => 'extraction_started',
            'run_uuid' => $runUuid,
            'module' => $module,
            'document_count' => $docCount,
            'model' => config('frexo_ai.model', 'gemini-1.5-pro'),
            'prompt_version' => config('frexo_ai.prompt_version', 'v1'),
            'timestamp' => now()->toIso8601String(),
        ], $extra)));
    }

    public static function logStageLatency(string $runUuid, string $stage, float $durationMs, array $meta = []): void
    {
        Log::info("[FrexoAI] Stage {$stage} Completed", self::sanitize(array_merge([
            'event' => 'stage_latency',
            'run_uuid' => $runUuid,
            'stage' => $stage,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => now()->toIso8601String(),
        ], $meta)));
    }

    public static function logRetry(string $runUuid, int $attempt, int $statusCode, string $reason): void
    {
        Log::warning('[FrexoAI] Transient Retry Scheduled', self::sanitize([
            'event' => 'transient_retry',
            'run_uuid' => $runUuid,
            'attempt' => $attempt,
            'max_retries' => config('frexo_ai.max_retries', 2),
            'status_code' => $statusCode,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    public static function logValidationFailure(string $runUuid, string $code, string $message, ?string $fieldPath = null): void
    {
        Log::warning('[FrexoAI] Validation Failure', self::sanitize([
            'event' => 'validation_failure',
            'run_uuid' => $runUuid,
            'code' => $code,
            'message' => $message,
            'field_path' => $fieldPath,
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    public static function logJsonRepairAttempt(string $runUuid, string $documentId, int $attempt): void
    {
        Log::info('[FrexoAI] Attempting JSON Repair', self::sanitize([
            'event' => 'json_repair_attempt',
            'run_uuid' => $runUuid,
            'document_id' => $documentId,
            'attempt' => $attempt,
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    public static function logExtractionCompleted(string $runUuid, array $summary): void
    {
        Log::info('[FrexoAI] Extraction Completed', self::sanitize(array_merge([
            'event' => 'extraction_completed',
            'run_uuid' => $runUuid,
            'timestamp' => now()->toIso8601String(),
        ], $summary)));
    }

    public static function logExtractionFailed(string $runUuid, string $code, string $message, array $context = []): void
    {
        Log::error('[FrexoAI] Extraction Failed', self::sanitize(array_merge([
            'event' => 'extraction_failed',
            'run_uuid' => $runUuid,
            'code' => $code,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ], $context)));
    }
}
