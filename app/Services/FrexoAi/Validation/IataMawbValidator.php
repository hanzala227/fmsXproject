<?php

namespace App\Services\FrexoAi\Validation;

class IataMawbValidator
{
    /**
     * Validate IATA 11-digit Master Air Waybill number with Mod 7 check digit.
     * Check digit = (first 7 digits of serial) mod 7.
     */
    public function validate(?string $raw, string $fieldPath = 'mbl.mawb_no'): ValidationResult
    {
        if (empty($raw)) {
            return ValidationResult::fail('EMPTY_MAWB', 'MAWB number cannot be empty', $fieldPath, null);
        }

        $digits = preg_replace('/[^0-9]/', '', trim($raw));

        if (strlen($digits) !== 11) {
            return ValidationResult::fail(
                'INVALID_MAWB_LENGTH',
                "MAWB '$raw' must contain exactly 11 digits (3-digit prefix + 8-digit serial)",
                $fieldPath,
                $raw
            );
        }

        $prefix = substr($digits, 0, 3);
        $serial7 = substr($digits, 3, 7);
        $checkDigit = (int)substr($digits, 10, 1);

        $expectedCheckDigit = ((int)$serial7) % 7;
        $formatted = $prefix . '-' . substr($digits, 3);

        if ($checkDigit !== $expectedCheckDigit) {
            return ValidationResult::warn(
                'MAWB_CHECK_DIGIT_MISMATCH',
                "MAWB '$raw' check digit is $checkDigit, expected $expectedCheckDigit (Mod 7). Stored as '$formatted'.",
                $fieldPath,
                $formatted
            );
        }

        return ValidationResult::pass($fieldPath, $formatted, 'IATA_MAWB_VALID');
    }
}
