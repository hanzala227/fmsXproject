<?php

namespace App\Services\FrexoAi\Client;

interface GeminiClientInterface
{
    /**
     * Send structured prompt and schema to Gemini multimodal endpoint.
     *
     * @param string $systemInstruction
     * @param array $userContents Array of text and/or image payload blocks
     * @param array $responseSchema Expected JSON schema
     * @param float|null $temperature
     * @return string Raw JSON response string
     * @throws \Exception
     */
    public function generateStructuredContent(
        string $systemInstruction,
        array $userContents,
        array $responseSchema,
        ?float $temperature = null
    ): string;
}
