<?php

namespace App\Services\FrexoAi\Validation;

class DuplicateContainerValidator
{
    /**
     * Inspect containers for duplicates across HBLs and within the same HBL.
     * Flags warnings, NEVER auto-deletes records.
     *
     * @param  array<array{container_no: string, hbl_no?: string}>  $containers
     * @return ValidationResult[]
     */
    public function validate(array $containers): array
    {
        $results = [];
        $seen = [];

        foreach ($containers as $idx => $cont) {
            $rawNo = $cont['container_no'] ?? null;
            if (empty($rawNo)) {
                continue;
            }

            $no = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawNo));
            $hbl = $cont['hbl_no'] ?? 'UNLINKED';

            if (isset($seen[$no])) {
                $prev = $seen[$no];
                if ($prev['hbl'] === $hbl) {
                    $results[] = ValidationResult::warn(
                        'SUSPECT_DUPLICATE_CONTAINER_SAME_HBL',
                        "Container '$no' appears twice on the same House Bill '$hbl'. Flagged for review; not deleted.",
                        "containers[$idx].container_no",
                        $no
                    );
                } else {
                    $results[] = ValidationResult::warn(
                        'CONTAINER_ACROSS_MULTIPLE_HBLS',
                        "Container '$no' is shared across multiple House Bills ('{$prev['hbl']}' and '$hbl'). Flagged for review; not deleted.",
                        "containers[$idx].container_no",
                        $no
                    );
                }
            } else {
                $seen[$no] = ['hbl' => $hbl, 'idx' => $idx];
            }
        }

        return $results;
    }
}
