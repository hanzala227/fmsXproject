<?php

namespace App\Services\FrexoAi\Client;

use App\Models\AiSetting;
use App\Services\FrexoAi\Observability\FrexoAiLogger;
use Exception;
use Illuminate\Support\Facades\Http;

class GeminiClient implements GeminiClientInterface
{
    protected ?AiSetting $settings;

    public function __construct(?AiSetting $settings = null)
    {
        $this->settings = $settings ?? (class_exists(AiSetting::class) ? AiSetting::current() : null);
    }

    public function generateStructuredContent(
        string $systemInstruction,
        array $userContents,
        array $responseSchema,
        ?float $temperature = null
    ): string {
        if (! $this->settings || ! $this->settings->api_key) {
            throw new Exception('AI settings or API Key not configured.');
        }

        $baseUrl = rtrim($this->settings->base_url, '/');
        $apiType = strtolower($this->settings->api_type ?? 'gemini');

        if ($apiType === 'gemini' && str_contains($baseUrl, 'api.openai.com')) {
            $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
        }

        if (! str_ends_with($baseUrl, '/chat/completions')) {
            if (str_ends_with($baseUrl, '/v1') || str_ends_with($baseUrl, '/v1beta/openai')) {
                $baseUrl .= '/chat/completions';
            } else {
                $baseUrl .= '/v1/chat/completions';
            }
        }

        $model = config('frexo_ai.model', $this->settings->model_name ?? 'gemini-1.5-pro');
        $temp = $temperature ?? config('frexo_ai.temperature', 0.1);
        $timeout = config('frexo_ai.timeout', 60);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userContents],
            ],
            'temperature' => $temp,
        ];

        if ($apiType !== 'gemini') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $maxRetries = (int) config('frexo_ai.max_retries', 2);
        $baseDelayMs = (int) config('frexo_ai.retry_backoff_ms', 500);
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->settings->api_key,
                    'Content-Type' => 'application/json',
                ])->timeout($timeout)->post($baseUrl, $payload);

                $statusCode = $response->status();

                // Check for transient rate limit or server error: 429 or 5xx
                if ($statusCode === 429 || ($statusCode >= 500 && $statusCode <= 504)) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        $jitter = random_int(10, 100);
                        $delayMs = ($baseDelayMs * (2 ** ($attempt - 1))) + $jitter;
                        FrexoAiLogger::logRetry('gemini_call', $attempt, $statusCode, "HTTP {$statusCode} transient provider status");
                        usleep($delayMs * 1000);

                        continue;
                    }
                }

                // Permanent client errors (400, 401, 403, 404): DO NOT RETRY
                if ($response->failed()) {
                    throw new Exception("AI Gateway returned HTTP {$statusCode}");
                }

                $data = $response->json();

                // Check safety blocks
                if (isset($data['promptFeedback']['blockReason'])) {
                    throw new Exception('SAFETY_BLOCKED: Content was blocked by safety filter: '.$data['promptFeedback']['blockReason']);
                }

                $choice = $data['choices'][0] ?? null;
                if ($choice && isset($choice['finish_reason']) && $choice['finish_reason'] === 'safety') {
                    throw new Exception('SAFETY_BLOCKED: Content output was terminated by safety filter.');
                }

                $content = $choice['message']['content'] ?? '';
                if (empty(trim($content))) {
                    throw new Exception('EMPTY_RESPONSE: Empty completion returned from AI service.');
                }

                return $content;
            } catch (Exception $e) {
                $lastException = $e;
                $msg = $e->getMessage();

                // Only retry on network timeouts or transient connection issues
                if (str_contains($msg, 'timed out') || str_contains($msg, 'cURL error 28')) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        $delayMs = ($baseDelayMs * $attempt) + random_int(10, 50);
                        FrexoAiLogger::logRetry('gemini_call', $attempt, 408, "Network timeout: {$msg}");
                        usleep($delayMs * 1000);

                        continue;
                    }
                }

                // If non-transient, break retry loop immediately
                break;
            }
        }

        throw new Exception('AI call failed after retries: '.($lastException ? $lastException->getMessage() : 'Unknown error'));
    }
}
