<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiProvider implements AiProviderInterface
{
    protected $apiKey;
    protected $model;

    public function __construct(string $apiKey, string $model = 'gemini-1.5-pro')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateResponse(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is missing.");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            Log::error('Gemini Error: ' . $response->body());
            throw new Exception("Failed to get response from Gemini.");
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function analyzeJson(string $prompt, array $data): array
    {
        $fullPrompt = $prompt . "\n\nData: " . json_encode($data) . "\n\nPlease respond ONLY with valid JSON.";
        
        $response = $this->generateResponse($fullPrompt);
        
        // Try to parse JSON from the response
        $cleanResponse = str_replace(['```json', '```'], '', $response);
        $decoded = json_decode(trim($cleanResponse), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Gemini did not return valid JSON.");
        }
        
        return $decoded;
    }
}
