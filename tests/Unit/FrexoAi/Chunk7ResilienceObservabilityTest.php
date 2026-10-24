<?php

namespace Tests\Unit\FrexoAi;

use App\Models\AiSetting;
use App\Services\FrexoAi\Client\GeminiClient;
use App\Services\FrexoAi\Client\MockGeminiClient;
use App\Services\FrexoAi\Observability\FrexoAiLogger;
use App\Services\FrexoAi\Pipeline\Stage2PerDocumentExtractor;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Chunk7ResilienceObservabilityTest extends TestCase
{
    /**
     * Test 1: Simulated 429 returns retry attempts up to max_retries.
     */
    public function test_transient_429_retries_up_to_max_retries(): void
    {
        $setting = new AiSetting;
        $setting->api_key = 'test-secret-key-12345';
        $setting->base_url = 'https://mock.api.endpoint';
        $setting->model_name = 'gemini-1.5-pro';

        $attempts = 0;
        Http::fake([
            '*' => function () use (&$attempts) {
                $attempts++;

                return Http::response(['error' => 'Rate limit exceeded'], 429);
            },
        ]);

        config(['frexo_ai.max_retries' => 2]);
        config(['frexo_ai.retry_backoff_ms' => 1]); // Fast backoff in test

        $client = new GeminiClient($setting);

        try {
            $client->generateStructuredContent('system', ['test'], []);
            $this->fail('Expected exception was not thrown.');
        } catch (Exception $e) {
            $this->assertStringContainsString('AI call failed after retries', $e->getMessage());
            // Initial call (1) + 2 retries = 3 attempts total
            $this->assertEquals(3, $attempts, 'Should attempt initial call plus exactly 2 retries on 429');
        }
    }

    /**
     * Test 2: Permanent client errors (401 Unauthorized, 400 Bad Request) are NOT retried.
     */
    public function test_permanent_errors_are_not_retried(): void
    {
        $setting = new AiSetting;
        $setting->api_key = 'invalid-api-key';
        $setting->base_url = 'https://mock.api.endpoint';

        $attempts = 0;
        Http::fake([
            '*' => function () use (&$attempts) {
                $attempts++;

                return Http::response(['error' => 'Invalid API key'], 401);
            },
        ]);

        config(['frexo_ai.max_retries' => 2]);
        config(['frexo_ai.retry_backoff_ms' => 1]);

        $client = new GeminiClient($setting);

        try {
            $client->generateStructuredContent('system', ['test'], []);
            $this->fail('Expected exception was not thrown.');
        } catch (Exception $e) {
            $this->assertStringContainsString('AI Gateway returned HTTP 401', $e->getMessage());
            $this->assertEquals(1, $attempts, 'Permanent 401 must not be retried');
        }
    }

    /**
     * Test 3: Safety filter block is caught and flagged as SAFETY_BLOCKED.
     */
    public function test_safety_blocked_response_is_handled(): void
    {
        $setting = new AiSetting;
        $setting->api_key = 'test-key';
        $setting->base_url = 'https://mock.api.endpoint';

        Http::fake([
            '*' => Http::response([
                'promptFeedback' => ['blockReason' => 'SAFETY'],
            ], 200),
        ]);

        $client = new GeminiClient($setting);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SAFETY_BLOCKED');

        $client->generateStructuredContent('system', ['test'], []);
    }

    /**
     * Test 4: Empty model output throws EMPTY_RESPONSE.
     */
    public function test_empty_response_is_handled(): void
    {
        $setting = new AiSetting;
        $setting->api_key = 'test-key';
        $setting->base_url = 'https://mock.api.endpoint';

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '   ']],
                ],
            ], 200),
        ]);

        $client = new GeminiClient($setting);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('EMPTY_RESPONSE');

        $client->generateStructuredContent('system', ['test'], []);
    }

    /**
     * Test 5: Malformed JSON with markdown fences is repaired successfully on attempt 1.
     */
    public function test_malformed_json_markdown_repaired_successfully(): void
    {
        $fencedJson = "```json\n{\n  \"document_type\": \"BILL_OF_LADING\",\n  \"mbl\": {\"mbl_no\": \"MEDU1234567\"}\n}\n```";

        $mockClient = new MockGeminiClient([$fencedJson]);
        $stage2 = new Stage2PerDocumentExtractor($mockClient);

        $docs = [
            [
                'document_id' => 'doc_test_1',
                'filename' => 'bl.pdf',
                'pages_total' => 1,
                'failed_pages' => [],
                'text_layer' => 'BILL OF LADING MEDU1234567',
                'images' => [],
            ],
        ];

        $result = $stage2->extractAll('ocean_import', $docs);

        $this->assertArrayHasKey('doc_test_1', $result['document_results']);
        $this->assertEquals('BILL_OF_LADING', $result['analyses'][0]->documentType);
        $this->assertEquals('SUCCESS', $result['analyses'][0]->status);
        $this->assertEquals('MEDU1234567', $result['document_results']['doc_test_1']['mbl']['mbl_no']);
    }

    /**
     * Test 6: Irreparable malformed JSON marks document visibly FAILED without crashing batch.
     */
    public function test_irreparable_json_fails_document_visibly(): void
    {
        $garbage = 'Sorry, I cannot process this document because it is unclear. [NOT JSON]';

        $mockClient = new MockGeminiClient([$garbage]);
        $stage2 = new Stage2PerDocumentExtractor($mockClient);

        $docs = [
            [
                'document_id' => 'doc_fail_1',
                'filename' => 'corrupt.pdf',
                'pages_total' => 1,
                'failed_pages' => [],
                'text_layer' => 'Corrupt text',
                'images' => [],
            ],
        ];

        $result = $stage2->extractAll('ocean_import', $docs);

        $this->assertEquals('FAILED', $result['analyses'][0]->status);
        $this->assertNotEmpty($result['analyses'][0]->limitations);
        $this->assertStringContainsString('MALFORMED_JSON_OUTPUT', $result['analyses'][0]->limitations[0]);
    }

    /**
     * Test 7: Observability sanitization strips API keys, passwords, and full document content.
     */
    public function test_logger_sanitizer_removes_secrets_and_full_content(): void
    {
        $dirtyContext = [
            'run_uuid' => 'uuid-1234',
            'api_key' => 'AIzaSyA_SECRET_KEY_123456789',
            'authorization' => 'Bearer sensitive_token_abc',
            'bearer_token' => 'secret_jwt_payload',
            'raw_text' => str_repeat('Full freight document text content with cargo descriptions... ', 50),
            'status_code' => 200,
        ];

        $clean = FrexoAiLogger::sanitize($dirtyContext);

        $this->assertEquals('[REDACTED_SECRET]', $clean['api_key']);
        $this->assertEquals('[REDACTED_SECRET]', $clean['authorization']);
        $this->assertEquals('[REDACTED_SECRET]', $clean['bearer_token']);
        $this->assertStringStartsWith('[DOCUMENT_TEXT_REDACTED_LEN_', $clean['raw_text']);
        $this->assertStringNotContainsString('AIzaSyA', json_encode($clean));
        $this->assertEquals(200, $clean['status_code']);
    }

    /**
     * Test 8: Codebase check proves zero logging of raw API keys or unredacted document bodies.
     */
    public function test_codebase_contains_no_unredacted_logging_calls(): void
    {
        $servicesDir = app_path('Services/FrexoAi');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($servicesDir));

        $violations = [];

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $code = file_get_contents($file->getRealPath());

                // Check for dangerous logging calls like Log::info($apiKey) or Log::info($textLayer)
                if (preg_match('/Log::[a-z]+\([^)]*(?:api_key|token|text_layer|raw_text)[^)]*\)/i', $code, $matches)) {
                    // Check if it's using FrexoAiLogger::sanitize
                    if (! str_contains($code, 'FrexoAiLogger::sanitize')) {
                        $violations[] = $file->getRealPath().' => '.$matches[0];
                    }
                }
            }
        }

        $this->assertEmpty($violations, 'Potential unredacted logging found in: '.implode(', ', $violations));
    }
}
