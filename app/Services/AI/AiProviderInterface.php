<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Generate a simple text response based on a prompt.
     */
    public function generateResponse(string $prompt): string;

    /**
     * Generate structured JSON based on prompt and data (Useful for mappings).
     */
    public function analyzeJson(string $prompt, array $data): array;
}
