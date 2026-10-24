<?php

namespace App\Services\FrexoAi\Client;

use Exception;

class MockGeminiClient implements GeminiClientInterface
{
    /** @var array<string, string|callable> */
    protected array $cannedResponses = [];
    protected ?string $defaultResponse = null;
    public array $callHistory = [];
    protected int $callCount = 0;

    public function queueResponse(string $jsonOrError): self
    {
        $this->cannedResponses[] = $jsonOrError;
        return $this;
    }

    public function setDefaultResponse(string $json): self
    {
        $this->defaultResponse = $json;
        return $this;
    }

    public function generateStructuredContent(
        string $systemInstruction,
        array $userContents,
        array $responseSchema,
        ?float $temperature = null
    ): string {
        $this->callCount++;
        $this->callHistory[] = [
            'system' => $systemInstruction,
            'user' => $userContents,
            'schema' => $responseSchema,
            'temperature' => $temperature,
        ];

        if (!empty($this->cannedResponses)) {
            $next = array_shift($this->cannedResponses);
            if ($next instanceof Exception) {
                throw $next;
            }
            if (is_callable($next)) {
                return $next($systemInstruction, $userContents);
            }
            return (string)$next;
        }

        if ($this->defaultResponse !== null) {
            return $this->defaultResponse;
        }

        // Default empty valid response
        return json_encode([
            'mbl' => [],
            'hbls' => [],
            'containers' => [],
            'routes' => [],
            'field_evidence' => [],
        ]);
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }
}
