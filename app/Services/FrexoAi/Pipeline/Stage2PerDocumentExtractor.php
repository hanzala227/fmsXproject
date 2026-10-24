<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\Client\GeminiClientInterface;
use App\Services\FrexoAi\DTO\DocumentAnalysisDTO;
use App\Services\FrexoAi\Schema\SchemaBuilder;
use Illuminate\Support\Facades\Log;
use Exception;

class Stage2PerDocumentExtractor
{
    protected GeminiClientInterface $client;

    public function __construct(GeminiClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Run extraction on each ingested document individually.
     *
     * @param string $module
     * @param array $ingestedDocs
     * @return array [ 'document_results' => [...], 'analyses' => [DocumentAnalysisDTO, ...] ]
     */
    public function extractAll(string $module, array $ingestedDocs): array
    {
        $promptA = file_get_contents(resource_path('prompts/extraction/v1/prompt_a_system.txt'));
        $promptB = file_get_contents(resource_path('prompts/extraction/v1/prompt_b_document.txt'));
        $moduleAddOn = str_starts_with($module, 'ocean_')
            ? file_get_contents(resource_path('prompts/extraction/v1/module_ocean.txt'))
            : file_get_contents(resource_path('prompts/extraction/v1/module_air.txt'));

        $fieldRules = SchemaBuilder::buildFieldRulesText($module);
        $manifest = json_encode(array_map(fn($d) => [
            'document_id' => $d['document_id'],
            'filename' => $d['filename'],
            'pages_total' => $d['pages_total'],
        ], $ingestedDocs));

        $schema = SchemaBuilder::buildGeminiResponseSchema($module);

        $results = [];
        $analyses = [];

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

            $system .= "\n\n" . $task . "\n\n" . $moduleAddOn;

            // Build user contents
            $userContents = [];
            $userContents[] = [
                'type' => 'text',
                'text' => "=== DOCUMENT TO EXTRACT (ID: {$docId}, Name: {$doc['filename']}) ===\n" . $doc['text_layer']
            ];

            if (!empty($doc['images'])) {
                foreach ($doc['images'] as $img) {
                    $userContents[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:' . ($img['mime'] ?: 'image/jpeg') . ';base64,' . $img['base64']
                        ]
                    ];
                }
            }

            try {
                $rawJson = $this->client->generateStructuredContent($system, $userContents, $schema);
                $decoded = json_decode($rawJson, true);

                if (!is_array($decoded)) {
                    throw new Exception("Malformed JSON output from model for document $docId");
                }

                if (isset($decoded['document_type'])) {
                    $analysis->documentType = (string)$decoded['document_type'];
                }

                $results[$docId] = $decoded;
            } catch (\Throwable $e) {
                Log::warning("Stage 2 extraction failed for {$docId}: " . $e->getMessage());
                $analysis->status = 'FAILED';
                $analysis->limitations[] = "Extraction error: " . $e->getMessage();
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
