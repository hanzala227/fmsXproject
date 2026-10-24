<?php

namespace App\Services\FrexoAi\Client;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

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
        if (!$this->settings || !$this->settings->api_key) {
            throw new Exception("AI settings or API Key not configured.");
        }

        $baseUrl = rtrim($this->settings->base_url, '/');
        $apiType = strtolower($this->settings->api_type ?? 'gemini');

        if ($apiType === 'gemini' && str_contains($baseUrl, 'api.openai.com')) {
            $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
        }

        if (!str_ends_with($baseUrl, '/chat/completions')) {
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

        // Add response_format for OpenAI or schema if supported
        if ($apiType !== 'gemini') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $maxRetries = 2;
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->settings->api_key,
                    'Content-Type' => 'application/json',
                ])->timeout($timeout)->post($baseUrl, $payload);

                if ($response->status() === 429 || $response->status() >= 500) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        usleep(500000 * $attempt); // backoff 500ms, 1000ms
                        continue;
                    }
                }

                if ($response->failed()) {
                    throw new Exception("AI Gateway returned HTTP " . $response->status());
                }

                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                if (empty(trim($content))) {
                    throw new Exception("Empty completion returned from AI service.");
                }

                return $content;
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                if ($attempt <= $maxRetries) {
                    usleep(500000 * $attempt);
                    continue;
                }
                break;
            }
        }

        throw new Exception("AI call failed after retries: " . ($lastException ? $lastException->getMessage() : "Unknown error"));
    }
}
