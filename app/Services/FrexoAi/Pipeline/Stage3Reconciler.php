<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\Client\GeminiClientInterface;
use App\Services\FrexoAi\DTO\ConflictCandidateDTO;
use App\Services\FrexoAi\DTO\ExtractionResultDTO;
use App\Services\FrexoAi\DTO\FieldEvidenceDTO;
use Illuminate\Support\Facades\File;

class Stage3Reconciler
{
    protected GeminiClientInterface $client;

    public function __construct(GeminiClientInterface $client)
    {
        $this->client = $client;
    }

    public function reconcile(
        string $module,
        array $documentResults,
        array $ingestedDocs,
        ExtractionResultDTO $dto
    ): ExtractionResultDTO {
        $isAir = str_starts_with($module, 'air_');

        // Shortcut for 0 or 1 document: no cross-document reconciliation needed
        if (count($documentResults) <= 1) {
            $docId = array_key_first($documentResults);
            $rawResult = $documentResults[$docId] ?? [];

            $dto->shipmentGroups[] = [
                'group_id' => 'group_1',
                'documents' => $docId ? [$docId] : [],
                'association_status' => 'CONFIRMED',
                'association_evidence' => ['single_document'],
            ];

            $fd = $rawResult['form_data'] ?? $rawResult;
            $master = ! empty($fd['master']) ? $fd['master'] : ($fd['mbl'] ?? []);
            $houses = ! empty($fd['hawbs']) ? $fd['hawbs'] : ($fd['hbls'] ?? []);
            if (empty($houses) && isset($fd['hawb'])) {
                $houses = [$fd['hawb']];
            }

            $dto->formData = [
                'mbl' => $master,
                'master' => $master,
                'hbls' => $houses,
                'hawbs' => $houses,
                'containers' => $fd['containers'] ?? [],
                'routes' => $fd['routes'] ?? ($fd['flights'] ?? []),
            ];

            if (! empty($rawResult['conflicts']) && is_array($rawResult['conflicts'])) {
                foreach ($rawResult['conflicts'] as $c) {
                    $dto->addConflict(new ConflictCandidateDTO(
                        $c['field_path'] ?? 'unknown',
                        $c['scope'] ?? ($isAir ? 'master' : 'mbl'),
                        $c['chosen_value'] ?? ($c['proposed_value'] ?? null),
                        $c['candidates'] ?? [],
                        $c['reason'] ?? 'Material difference between sources'
                    ));
                }
            }

            if (! empty($rawResult['warnings']) && is_array($rawResult['warnings'])) {
                foreach ($rawResult['warnings'] as $w) {
                    $dto->addWarning($w['code'] ?? 'RECONCILIATION_WARNING', $w['message'] ?? 'Warning detected', $w['field_path'] ?? null);
                }
            }

            if ($docId) {
                $this->populateEvidenceFromRaw($dto, $rawResult, $docId);
            }

            return $dto;
        }

        // Multiple documents: run Stage 3 Reconciler call
        $promptA = File::get(resource_path('prompts/extraction/v1/prompt_a_system.txt'));
        $promptC = File::get(resource_path('prompts/extraction/v1/prompt_c_reconcile.txt'));
        $moduleAddOn = File::get(resource_path('prompts/extraction/v1/module_'.($isAir ? 'air' : 'ocean').'.txt'));

        $system = str_replace(
            ['{{MODULE}}', '{{DOCUMENT_MANIFEST}}', '{{FIELD_RULES}}', '{{EXISTING_SHIPMENT_CONTEXT}}'],
            [$module, json_encode($ingestedDocs), '', 'Reconcile multi-document shipment data.'],
            $promptA
        )."\n\n".$promptC."\n\n".$moduleAddOn;

        $userContents = [
            [
                'type' => 'text',
                'text' => "PER-DOCUMENT EXTRACTION RESULTS TO RECONCILE:\n".json_encode($documentResults, JSON_PRETTY_PRINT),
            ],
        ];

        $reconcileSchema = [
            'type' => 'object',
            'properties' => [
                'shipment_groups' => ['type' => 'array'],
                'unmatched_documents' => ['type' => 'array'],
                'form_data' => ['type' => 'object'],
                'conflicts' => ['type' => 'array'],
                'calculations' => ['type' => 'array'],
                'warnings' => ['type' => 'array'],
            ],
            'required' => ['shipment_groups', 'form_data'],
        ];

        try {
            $rawJson = $this->client->generateStructuredContent($system, $userContents, $reconcileSchema);
            $decoded = json_decode($rawJson, true);

            if (! is_array($decoded)) {
                $cleaned = trim($rawJson);
                $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
                $cleaned = preg_replace('/\s*```$/i', '', $cleaned);
                if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
                    $cleaned = $matches[0];
                }
                $decoded = json_decode($cleaned, true);
            }

            if (is_array($decoded)) {
                $dto->shipmentGroups = $decoded['shipment_groups'] ?? [];
                $dto->unmatchedDocuments = $decoded['unmatched_documents'] ?? [];

                $fd = $decoded['form_data'] ?? [];
                $master = $fd['master'] ?? ($fd['mbl'] ?? []);
                $houses = $fd['hawbs'] ?? ($fd['hbls'] ?? []);

                $dto->formData = [
                    'mbl' => $master,
                    'master' => $master,
                    'hbls' => $houses,
                    'hawbs' => $houses,
                    'containers' => $fd['containers'] ?? [],
                    'routes' => $fd['routes'] ?? ($fd['flights'] ?? []),
                ];

                if (isset($decoded['conflicts']) && is_array($decoded['conflicts'])) {
                    foreach ($decoded['conflicts'] as $c) {
                        $dto->addConflict(new ConflictCandidateDTO(
                            $c['field_path'] ?? 'unknown',
                            $c['scope'] ?? ($isAir ? 'master' : 'mbl'),
                            $c['proposed_value'] ?? ($c['chosen_value'] ?? null),
                            $c['candidates'] ?? [],
                            $c['reason'] ?? 'Material difference between documents'
                        ));
                    }
                }

                if (isset($decoded['warnings']) && is_array($decoded['warnings'])) {
                    foreach ($decoded['warnings'] as $w) {
                        $dto->addWarning(
                            $w['code'] ?? 'RECONCILIATION_WARNING',
                            $w['message'] ?? 'Difference detected',
                            $w['field_path'] ?? null
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback: programmatic merge of per-document results
            $merged = [
                'mbl' => [],
                'master' => [],
                'hbls' => [],
                'hawbs' => [],
                'containers' => [],
                'routes' => [],
            ];
            foreach ($documentResults as $res) {
                $src = $res['form_data'] ?? $res;
                $m = $src['master'] ?? ($src['mbl'] ?? []);
                if (! empty($m)) {
                    $merged['mbl'] = array_merge($merged['mbl'], $m);
                    $merged['master'] = array_merge($merged['master'], $m);
                }
                $h = $src['hawbs'] ?? ($src['hbls'] ?? []);
                if (! empty($h)) {
                    $merged['hbls'] = array_merge($merged['hbls'], $h);
                    $merged['hawbs'] = array_merge($merged['hawbs'], $h);
                }
                if (! empty($src['containers'])) {
                    $merged['containers'] = array_merge($merged['containers'], $src['containers']);
                }
                if (! empty($src['routes'])) {
                    $merged['routes'] = array_merge($merged['routes'], $src['routes']);
                }
                if (! empty($src['flights'])) {
                    $merged['routes'] = array_merge($merged['routes'], $src['flights']);
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
        $evidenceMap = $raw['field_evidence'] ?? ($raw['form_data']['field_evidence'] ?? null);
        if (is_array($evidenceMap)) {
            foreach ($evidenceMap as $fKey => $ev) {
                if (! is_array($ev)) {
                    continue;
                }
                $fieldPath = $ev['field_path'] ?? (is_string($fKey) ? $fKey : 'unknown');
                $scope = $ev['scope'] ?? (str_starts_with($fieldPath, 'mbl.') || str_starts_with($fieldPath, 'master.') ? 'master' : 'house');
                $evidenceText = $ev['evidence_text'] ?? ($ev['source']['evidence_text'] ?? null);

                $source = array_merge(
                    is_array($ev['source'] ?? null) ? $ev['source'] : [],
                    [
                        'document_id' => $docId,
                        'page' => $ev['page_number'] ?? ($ev['page'] ?? ($ev['source']['page'] ?? 1)),
                        'evidence_text' => $evidenceText,
                    ]
                );

                $dto->addFieldEvidence(new FieldEvidenceDTO(
                    $fieldPath,
                    $scope,
                    $ev['raw_value'] ?? null,
                    $ev['normalized_value'] ?? ($ev['raw_value'] ?? null),
                    $ev['unit'] ?? null,
                    $ev['status'] ?? FieldEvidenceDTO::STATUS_EXPLICIT,
                    $source,
                    $ev['derivation'] ?? null
                ));
            }
        } else {
            // Auto-create explicit evidence for top-level master/mbl values
            $src = $raw['form_data'] ?? $raw;
            $master = $src['master'] ?? ($src['mbl'] ?? []);
            if (is_array($master)) {
                foreach ($master as $k => $v) {
                    if (is_null($v) || is_array($v)) {
                        continue;
                    }
                    $dto->addFieldEvidence(new FieldEvidenceDTO(
                        "mbl.{$k}",
                        'master',
                        $v,
                        $v,
                        null,
                        FieldEvidenceDTO::STATUS_EXPLICIT,
                        [
                            'document_id' => $docId,
                            'page' => 1,
                            'evidence_text' => (string) $v,
                        ]
                    ));
                }
            }
        }
    }
}
