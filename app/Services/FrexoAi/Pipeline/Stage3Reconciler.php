<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\Client\GeminiClientInterface;
use App\Services\FrexoAi\DTO\ConflictCandidateDTO;
use App\Services\FrexoAi\DTO\ExtractionResultDTO;
use App\Services\FrexoAi\DTO\FieldEvidenceDTO;
use App\Services\FrexoAi\Schema\SchemaBuilder;
use Exception;

class Stage3Reconciler
{
    protected GeminiClientInterface $client;

    public function __construct(GeminiClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Reconcile multiple document extractions into a unified shipment result.
     *
     * @param string $module
     * @param array $documentResults [doc_id => [...]]
     * @param array $ingestedDocs
     * @param ExtractionResultDTO $dto
     * @return ExtractionResultDTO
     */
    public function reconcile(string $module, array $documentResults, array $ingestedDocs, ExtractionResultDTO $dto): ExtractionResultDTO
    {
        // 1. If only 1 document, deterministic pass-through
        if (count($documentResults) === 1) {
            $docId = array_key_first($documentResults);
            $rawResult = $documentResults[$docId] ?? [];

            $dto->shipmentGroups[] = [
                'group_id' => 'group_1',
                'documents' => [$docId],
                'association_status' => 'CONFIRMED',
                'association_evidence' => ['single_document'],
            ];

            $dto->formData = [
                'mbl' => $rawResult['mbl'] ?? [],
                'hbls' => $rawResult['hbls'] ?? [],
                'containers' => $rawResult['containers'] ?? [],
                'routes' => $rawResult['routes'] ?? ($rawResult['flights'] ?? []),
            ];

            $this->populateEvidenceFromRaw($dto, $rawResult, $docId);
            return $dto;
        }

        // 2. Multi-document reconciliation call
        $promptA = file_get_contents(resource_path('prompts/extraction/v1/prompt_a_system.txt'));
        $promptC = file_get_contents(resource_path('prompts/extraction/v1/prompt_c_reconcile.txt'));
        $moduleAddOn = str_starts_with($module, 'ocean_')
            ? file_get_contents(resource_path('prompts/extraction/v1/module_ocean.txt'))
            : file_get_contents(resource_path('prompts/extraction/v1/module_air.txt'));

        $fieldRules = SchemaBuilder::buildFieldRulesText($module);
        $manifest = json_encode(array_map(fn($d) => [
            'document_id' => $d['document_id'],
            'filename' => $d['filename'],
        ], $ingestedDocs));

        $system = str_replace(
            ['{{MODULE}}', '{{DOCUMENT_MANIFEST}}', '{{FIELD_RULES}}', '{{EXISTING_SHIPMENT_CONTEXT}}'],
            [$module, $manifest, $fieldRules, 'Synthesize multiple extracted shipment documents.'],
            $promptA
        );

        $system .= "\n\n" . $promptC . "\n\n" . $moduleAddOn;

        $userContents = [
            [
                'type' => 'text',
                'text' => "PER-DOCUMENT EXTRACTION RESULTS TO RECONCILE:\n" . json_encode($documentResults, JSON_PRETTY_PRINT)
            ]
        ];

        $reconcileSchema = [
            'type' => 'object',
            'properties' => [
                'shipment_groups' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'group_id' => ['type' => 'string'],
                            'documents' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'association_status' => ['type' => 'string', 'enum' => ['CONFIRMED', 'PROBABLE', 'UNCERTAIN']],
                            'association_evidence' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ]
                    ]
                ],
                'unmatched_documents' => [
                    'type' => 'array',
                    'items' => ['type' => 'string']
                ],
                'form_data' => SchemaBuilder::buildGeminiResponseSchema($module),
                'conflicts' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'field_path' => ['type' => 'string'],
                            'scope' => ['type' => 'string'],
                            'proposed_value' => ['type' => 'string'],
                            'reason' => ['type' => 'string'],
                            'candidates' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ]
                    ]
                ]
            ],
            'required' => ['shipment_groups', 'form_data']
        ];

        try {
            $rawJson = $this->client->generateStructuredContent($system, $userContents, $reconcileSchema);
            $decoded = json_decode($rawJson, true);

            if (is_array($decoded)) {
                $dto->shipmentGroups = $decoded['shipment_groups'] ?? [];
                $dto->unmatchedDocuments = $decoded['unmatched_documents'] ?? [];
                $dto->formData = $decoded['form_data'] ?? [];

                if (isset($decoded['conflicts']) && is_array($decoded['conflicts'])) {
                    foreach ($decoded['conflicts'] as $c) {
                        $dto->addConflict(new ConflictCandidateDTO(
                            $c['field_path'] ?? 'unknown',
                            $c['scope'] ?? 'master',
                            $c['proposed_value'] ?? null,
                            $c['candidates'] ?? [],
                            $c['reason'] ?? 'Material difference between documents'
                        ));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback: merge non-empty fields deterministically from first successful doc to last
            $merged = ['mbl' => [], 'hbls' => [], 'containers' => [], 'routes' => []];
            foreach ($documentResults as $dId => $res) {
                if (isset($res['mbl']) && is_array($res['mbl'])) {
                    $merged['mbl'] = array_merge($merged['mbl'], array_filter($res['mbl'], fn($v) => !is_null($v)));
                }
                if (!empty($res['hbls'])) {
                    $merged['hbls'] = array_merge($merged['hbls'], $res['hbls']);
                }
                if (!empty($res['containers'])) {
                    $merged['containers'] = array_merge($merged['containers'], $res['containers']);
                }
                if (!empty($res['routes'])) {
                    $merged['routes'] = array_merge($merged['routes'], $res['routes']);
                }
            }
            $dto->formData = $merged;
        }

        // Populate field evidences
        foreach ($documentResults as $dId => $res) {
            $this->populateEvidenceFromRaw($dto, $res, $dId);
        }

        return $dto;
    }

    protected function populateEvidenceFromRaw(ExtractionResultDTO $dto, array $raw, string $docId): void
    {
        if (isset($raw['field_evidence']) && is_array($raw['field_evidence'])) {
            foreach ($raw['field_evidence'] as $ev) {
                $dto->addFieldEvidence(new FieldEvidenceDTO(
                    $ev['field_path'] ?? 'unknown',
                    $ev['scope'] ?? 'master',
                    $ev['raw_value'] ?? null,
                    $ev['normalized_value'] ?? null,
                    $ev['unit'] ?? null,
                    $ev['status'] ?? FieldEvidenceDTO::STATUS_EXPLICIT,
                    array_merge($ev['source'] ?? [], ['document_id' => $docId]),
                    $ev['derivation'] ?? null
                ));
            }
        } else {
            // Auto-create explicit evidence for top-level mbl values
            if (isset($raw['mbl']) && is_array($raw['mbl'])) {
                foreach ($raw['mbl'] as $k => $v) {
                    if (is_null($v) || is_array($v)) continue;
                    $dto->addFieldEvidence(new FieldEvidenceDTO(
                        "mbl.$k",
                        'master',
                        $v,
                        $v,
                        null,
                        FieldEvidenceDTO::STATUS_EXPLICIT,
                        [
                            'document_id' => $docId,
                            'page' => 1,
                            'evidence_text' => (string)$v,
                        ]
                    ));
                }
            }
        }
    }
}
