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

            $master = ! empty($rawResult['master']) ? $rawResult['master'] : ($rawResult['mbl'] ?? []);
            $houses = ! empty($rawResult['hawbs']) ? $rawResult['hawbs'] : ($rawResult['hbls'] ?? []);
            if (empty($houses) && isset($rawResult['hawb'])) {
                $houses = [$rawResult['hawb']];
            }

            $dto->formData = [
                'mbl' => $master,
                'master' => $master,
                'hbls' => $houses,
                'hawbs' => $houses,
                'containers' => $rawResult['containers'] ?? [],
                'routes' => $rawResult['routes'] ?? ($rawResult['flights'] ?? []),
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
                $m = $res['master'] ?? ($res['mbl'] ?? []);
                if (! empty($m)) {
                    $merged['mbl'] = array_merge($merged['mbl'], $m);
                    $merged['master'] = array_merge($merged['master'], $m);
                }
                $h = $res['hawbs'] ?? ($res['hbls'] ?? []);
                if (! empty($h)) {
                    $merged['hbls'] = array_merge($merged['hbls'], $h);
                    $merged['hawbs'] = array_merge($merged['hawbs'], $h);
                }
                if (! empty($res['containers'])) {
                    $merged['containers'] = array_merge($merged['containers'], $res['containers']);
                }
                if (! empty($res['routes'])) {
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
            // Auto-create explicit evidence for top-level master/mbl values
            $master = $raw['master'] ?? ($raw['mbl'] ?? []);
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
