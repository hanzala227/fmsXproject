<?php

namespace App\Services\FrexoAi\Validation;

use App\Services\FrexoAi\DTO\ConflictCandidateDTO;

class DocumentAuthorityResolver
{
    /**
     * Resolve conflicting values between documents using document authority priorities.
     * Still returns a CONFLICT candidate when documents disagree materially.
     *
     * @param string $fieldPath
     * @param string $scope
     * @param array<array{value: mixed, doc_type: string, doc_id: string, priority: int}> $candidates
     * @return array{proposed: mixed, conflict: ?ConflictCandidateDTO}
     */
    public function resolve(string $fieldPath, string $scope, array $candidates): array
    {
        if (empty($candidates)) {
            return ['proposed' => null, 'conflict' => null];
        }

        if (count($candidates) === 1) {
            return ['proposed' => $candidates[0]['value'], 'conflict' => null];
        }

        // Sort candidates by authority priority descending
        usort($candidates, fn($a, $b) => ($b['priority'] ?? 50) <=> ($a['priority'] ?? 50));

        $top = $candidates[0];
        $distinctValues = [];
        foreach ($candidates as $c) {
            $norm = is_string($c['value']) ? strtolower(trim($c['value'])) : (string)$c['value'];
            $distinctValues[$norm] = true;
        }

        // If all candidate values agree after normalization, no conflict
        if (count($distinctValues) <= 1) {
            return ['proposed' => $top['value'], 'conflict' => null];
        }

        // Material disagreement: Emit conflict candidate while proposing highest authority
        $conflict = new ConflictCandidateDTO(
            $fieldPath,
            $scope,
            $top['value'],
            array_map(fn($c) => [
                'value' => $c['value'],
                'document_type' => $c['doc_type'] ?? 'UNKNOWN',
                'document_id' => $c['doc_id'] ?? 'unknown',
                'priority' => $c['priority'] ?? 50,
            ], $candidates),
            "Multiple documents disagree on '$fieldPath'. Proposing {$top['doc_type']} value ({$top['value']}) based on authority priority {$top['priority']}."
        );

        return [
            'proposed' => $top['value'],
            'conflict' => $conflict,
        ];
    }
}
