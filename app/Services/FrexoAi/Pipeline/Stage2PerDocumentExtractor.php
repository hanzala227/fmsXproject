<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\Client\GeminiClientInterface;
use App\Services\FrexoAi\DTO\DocumentAnalysisDTO;
use App\Services\FrexoAi\Observability\FrexoAiLogger;
use App\Services\FrexoAi\Schema\SchemaBuilder;
use Exception;
use Illuminate\Support\Facades\File;

class Stage2PerDocumentExtractor
{
    protected GeminiClientInterface $client;

    public function __construct(GeminiClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Run extraction per document with partial failure tolerance and JSON repair.
     *
     * @return array ['document_results' => array, 'analyses' => DocumentAnalysisDTO[]]
     */
    public function extractAll(string $module, array $ingestedDocs): array
    {
        $results = [];
        $analyses = [];

        $schema = SchemaBuilder::buildGeminiResponseSchema($module);
        $manifest = json_encode(array_map(fn ($d) => [
            'document_id' => $d['document_id'],
            'filename' => $d['filename'],
            'pages_total' => $d['pages_total'],
        ], $ingestedDocs));
        $fieldRules = SchemaBuilder::buildFieldRulesText($module);

        $promptA = File::get(resource_path('prompts/extraction/v1/prompt_a_system.txt'));
        $promptB = File::get(resource_path('prompts/extraction/v1/prompt_b_document.txt'));
        $moduleAddOn = File::get(resource_path('prompts/extraction/v1/module_'.(str_starts_with($module, 'ocean') ? 'ocean' : 'air').'.txt'));

        foreach ($ingestedDocs as $doc) {
            $docId = $doc['document_id'];
            $analysis = new DocumentAnalysisDTO(
                $docId,
                $doc['filename'],
                'UNKNOWN',
                $doc['pages_total'],
                $doc['pages_total'] - count($doc['failed_pages']),
                $doc['failed_pages'],
                'SUCCESS'
            );

            // Build system instruction
            $system = str_replace(
                ['{{MODULE}}', '{{DOCUMENT_MANIFEST}}', '{{FIELD_RULES}}', '{{EXISTING_SHIPMENT_CONTEXT}}'],
                [$module, $manifest, $fieldRules, 'New shipment booking creation.'],
                $promptA
            );

            // Build per-doc task
            $task = str_replace(
                ['{{MODULE}}', '{{DOCUMENT_ID}}'],
                [$module, $docId],
                $promptB
            );

            $system .= "\n\n".$task."\n\n".$moduleAddOn;

            // Build user contents
            $userContents = [];
            $userContents[] = [
                'type' => 'text',
                'text' => "=== DOCUMENT TO EXTRACT (ID: {$docId}, Name: {$doc['filename']}) ===\n".$doc['text_layer'],
            ];

            if (! empty($doc['images'])) {
                foreach ($doc['images'] as $img) {
                    $userContents[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:'.($img['mime'] ?: 'image/jpeg').';base64,'.$img['base64'],
                        ],
                    ];
                }
            }

            try {
                $rawJson = $this->client->generateStructuredContent($system, $userContents, $schema);
                $decoded = json_decode($rawJson, true);

                // Maximum one repair attempt for malformed JSON
                if (! is_array($decoded)) {
                    FrexoAiLogger::logJsonRepairAttempt('batch_run', $docId, 1);
                    $cleaned = trim($rawJson);
                    // Strip markdown fences
                    $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
                    $cleaned = preg_replace('/\s*```$/i', '', $cleaned);
                    // Extract substring between first { and last }
                    if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
                        $cleaned = $matches[0];
                    }
                    $decoded = json_decode($cleaned, true);
                    if (! is_array($decoded)) {
                        throw new Exception('MALFORMED_JSON_OUTPUT: Document output could not be parsed as valid JSON after repair attempt.');
                    }
                }

                if (isset($decoded['document_type'])) {
                    $analysis->documentType = (string) $decoded['document_type'];
                }

                $results[$docId] = $decoded;
            } catch (\Throwable $e) {
                FrexoAiLogger::logExtractionFailed('batch_run', 'STAGE2_DOC_ERROR', $e->getMessage(), ['doc_id' => $docId]);
                $analysis->status = 'FAILED';
                $analysis->limitations[] = 'Extraction failure: '.$e->getMessage();
                $results[$docId] = [
                    'mbl' => [],
                    'hbls' => [],
                    'containers' => [],
                    'routes' => [],
                    'field_evidence' => [],
                ];
            }

            $analyses[] = $analysis;
        }

        return [
            'document_results' => $results,
            'analyses' => $analyses,
        ];
    }
}
