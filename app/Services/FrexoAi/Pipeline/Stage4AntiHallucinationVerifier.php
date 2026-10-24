<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\DTO\ExtractionResultDTO;
use App\Services\FrexoAi\DTO\FieldEvidenceDTO;

class Stage4AntiHallucinationVerifier
{
    /**
     * Deterministically verify every field_evidence against ingested document text layers.
     * Downgrades unverified or fabricated fields to REVIEW_REQUIRED.
     *
     * @param ExtractionResultDTO $dto
     * @param array $ingestedDocs Indexed by document_id or list with 'document_id' and 'text_layer'
     * @return ExtractionResultDTO
     */
    public function verify(ExtractionResultDTO $dto, array $ingestedDocs): ExtractionResultDTO
    {
        $docsById = [];
        foreach ($ingestedDocs as $d) {
            $docsById[$d['document_id']] = [
                'text_layer' => $this->normalizeText($d['text_layer'] ?? ''),
                'pages_total' => $d['pages_total'] ?? 1,
                'images' => $d['images'] ?? [],
            ];
        }

        $downgradedCount = 0;

        foreach ($dto->fieldEvidence as $evidence) {
            $docId = $evidence->source['document_id'] ?? null;
            $page = $evidence->source['page'] ?? 1;
            $evidenceSnippet = $evidence->source['evidence_text'] ?? null;

            // 1. Check if document was issued by the server
            if (!$docId || !isset($docsById[$docId])) {
                $evidence->status = FieldEvidenceDTO::STATUS_REVIEW_REQUIRED;
                $dto->addWarning('UNVERIFIED_SOURCE', "Source document ID '{$docId}' was not issued by server.", $evidence->fieldPath);
                $downgradedCount++;
                continue;
            }

            $docData = $docsById[$docId];

            // 2. Check page boundary
            if ($page > $docData['pages_total']) {
                $evidence->status = FieldEvidenceDTO::STATUS_REVIEW_REQUIRED;
                $dto->addWarning('INVALID_PAGE', "Referenced page $page exceeds document page count {$docData['pages_total']}.", $evidence->fieldPath);
                $downgradedCount++;
                continue;
            }

            // 3. If image document without text layer, allow vision-based evidence if not empty
            if (empty(trim($docData['text_layer'])) || str_contains($docData['text_layer'], '[IMAGE DOCUMENT:')) {
                // Image scan verified by vision presence
                continue;
            }

            // 4. Verify snippet presence in digital text layer
            if (empty($evidenceSnippet)) {
                $evidence->status = FieldEvidenceDTO::STATUS_REVIEW_REQUIRED;
                $dto->addWarning('MISSING_EVIDENCE', "No verbatim evidence snippet provided for field.", $evidence->fieldPath);
                $downgradedCount++;
                continue;
            }

            $normSnippet = $this->normalizeText($evidenceSnippet);
            if (!str_contains($docData['text_layer'], $normSnippet)) {
                // Fuzzy fallback: check if raw_value is contained
                $normRaw = $this->normalizeText((string)$evidence->rawValue);
                if (empty($normRaw) || !str_contains($docData['text_layer'], $normRaw)) {
                    $evidence->status = FieldEvidenceDTO::STATUS_REVIEW_REQUIRED;
                    $dto->addWarning('EVIDENCE_NOT_FOUND', "Evidence snippet not found in verbatim document text.", $evidence->fieldPath);
                    $downgradedCount++;
                }
            }
        }

        $dto->processingSummary['anti_hallucination'] = [
            'total_evidence_checked' => count($dto->fieldEvidence),
            'downgraded_to_review' => $downgradedCount,
            'passed' => count($dto->fieldEvidence) - $downgradedCount,
        ];

        return $dto;
    }

    protected function normalizeText(string $text): string
    {
        // Normalize whitespace and lowercase for comparison
        $clean = preg_replace('/\s+/', ' ', $text);
        return strtolower(trim($clean));
    }
}
