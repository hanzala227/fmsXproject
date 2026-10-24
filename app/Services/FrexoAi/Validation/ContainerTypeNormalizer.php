<?php

namespace App\Services\FrexoAi\Validation;

class ContainerTypeNormalizer
{
    private static array $mapping = [
        // 40 High Cube
        '40HC' => '40HQ',
        '40HQ' => '40HQ',
        '40 HIGH CUBE' => '40HQ',
        '40\' HIGH CUBE' => '40HQ',
        '40\'HC' => '40HQ',
        '40\'HQ' => '40HQ',
        '40HC HC' => '40HQ',
        '45G1' => '40HQ',
        '40 HIGH' => '40HQ',

        // 20 General Purpose
        '20GP' => '20GP',
        '20DC' => '20GP',
        '20DV' => '20GP',
        '20 STANDARD' => '20GP',
        '20\' STANDARD' => '20GP',
        '20\' DRY' => '20GP',
        '20 DRY' => '20GP',
        '22G1' => '20GP',

        // 40 General Purpose
        '40GP' => '40GP',
        '40DC' => '40GP',
        '40DV' => '40GP',
        '40 STANDARD' => '40GP',
        '40\' STANDARD' => '40GP',
        '40\' DRY' => '40GP',
        '42G1' => '40GP',

        // 45 High Cube
        '45HC' => '45HQ',
        '45HQ' => '45HQ',
        '45 HIGH CUBE' => '45HQ',

        // Reefer
        '20RF' => '20RF',
        '40RF' => '40RF',
        '40HR' => '40RF',
        'REEFER' => '40RF',

        // Open Top / Flat Rack
        '20OT' => '20OT',
        '40OT' => '40OT',
        '20FR' => '20FR',
        '40FR' => '40FR',
    ];

    /**
     * Normalize container type while keeping the original raw text.
     */
    public function normalize(?string $raw, string $fieldPath = 'containers[].container_type'): ValidationResult
    {
        if (empty($raw)) {
            return ValidationResult::fail('EMPTY_CONTAINER_TYPE', 'Container type is empty', $fieldPath, null);
        }

        $clean = strtoupper(trim($raw));
        $clean = preg_replace('/\s+/', ' ', $clean);

        if (isset(self::$mapping[$clean])) {
            return ValidationResult::pass($fieldPath, self::$mapping[$clean]);
        }

        // Fuzzy submatch
        foreach (self::$mapping as $alias => $std) {
            if (str_contains($clean, $alias)) {
                return ValidationResult::pass($fieldPath, $std);
            }
        }

        // Keep raw text if not in dictionary
        return ValidationResult::warn(
            'UNRECOGNIZED_CONTAINER_TYPE',
            "Unrecognized container equipment type '$raw'. Preserved without mapping.",
            $fieldPath,
            $raw
        );
    }
}
