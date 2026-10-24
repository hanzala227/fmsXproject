<?php

namespace App\Services\FrexoAi\Validation;

class PortCodeValidator
{
    /**
     * Validate UN/LOCODE (5 characters) or IATA 3-letter Airport Code.
     * Never infers from ambiguous city names.
     */
    public function validate(?string $raw, string $type = 'UNLOCODE', string $fieldPath = 'mbl.pol_name'): ValidationResult
    {
        if (empty($raw)) {
            return ValidationResult::fail('EMPTY_PORT_CODE', 'Port/Airport value is empty', $fieldPath, null);
        }

        $clean = strtoupper(trim($raw));

        if ($type === 'IATA') {
            if (preg_match('/^[A-Z]{3}$/', $clean)) {
                return ValidationResult::pass($fieldPath, $clean, 'VALID_IATA_AIRPORT');
            }
            if (preg_match('/\b([A-Z]{3})\b/', $clean, $m)) {
                return ValidationResult::pass($fieldPath, $m[1], 'VALID_IATA_EXTRACTED');
            }
            return ValidationResult::warn(
                'AMBIGUOUS_AIRPORT_NAME',
                "Airport identifier '$raw' is not a standard 3-letter IATA code. Inference skipped.",
                $fieldPath,
                $raw
            );
        }

        // UN/LOCODE standard: 2-letter country + 3-character location (e.g. USLAX, CNNBO, PKKHI)
        if (preg_match('/^[A-Z]{2}[A-Z0-9]{3}$/', $clean)) {
            return ValidationResult::pass($fieldPath, $clean, 'VALID_UNLOCODE');
        }

        if (preg_match('/\b([A-Z]{2}[A-Z0-9]{3})\b/', $clean, $m)) {
            return ValidationResult::pass($fieldPath, $m[1], 'VALID_UNLOCODE_EXTRACTED');
        }

        return ValidationResult::warn(
            'AMBIGUOUS_SEAPORT_NAME',
            "Seaport '$raw' is not a validated 5-character UN/LOCODE. Preserved as raw name without inference.",
            $fieldPath,
            $raw
        );
    }
}
