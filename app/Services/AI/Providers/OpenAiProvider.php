<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAiProvider implements AiProviderInterface
{
    protected $apiKey;
    protected $model;

    public function __construct(string $apiKey, string $model = 'gpt-4o')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateResponse(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("OpenAI API key is missing.");
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI Error: ' . $response->body());
            throw new Exception("Failed to get response from OpenAI.");
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function analyzeJson(string $prompt, array $data): array
    {
        $fullPrompt = $prompt . "\n\nData: " . json_encode($data) . "\n\nPlease respond ONLY with valid JSON.";
        
        $response = $this->generateResponse($fullPrompt);
        
        // Try to parse JSON from the response (removing markdown if present)
        $cleanResponse = str_replace(['```json', '```'], '', $response);
        $decoded = json_decode(trim($cleanResponse), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("OpenAI did not return valid JSON.");
        }
        
        return $decoded;
    }
}
