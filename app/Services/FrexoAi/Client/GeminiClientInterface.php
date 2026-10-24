<?php

namespace App\Services\FrexoAi\Client;

interface GeminiClientInterface
{
    /**
     * Send structured prompt and schema to Gemini multimodal endpoint.
     *
     * @param  array  $userContents  Array of text and/or image payload blocks
     * @param  array  $responseSchema  Expected JSON schema
     * @return string Raw JSON response string
     *
     * @throws \Exception
     */
    public function generateStructuredContent(
        string $systemInstruction,
        array $userContents,
        array $responseSchema,
        ?float $temperature = null
    ): string;
}
