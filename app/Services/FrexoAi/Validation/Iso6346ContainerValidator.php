<?php

namespace App\Services\FrexoAi\Validation;

class Iso6346ContainerValidator
{
    private static array $letterValues = [
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19,
        'J' => 20, 'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29,
        'S' => 30, 'T' => 31, 'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38,
    ];

    /**
     * Validate an ISO 6346 container number.
     * Flag invalid, never auto-correct, keep original text.
     */
    public function validate(?string $raw, string $fieldPath = 'containers[].container_no'): ValidationResult
    {
        if (empty($raw)) {
            return ValidationResult::fail('EMPTY_CONTAINER_NO', 'Container number cannot be empty', $fieldPath, null);
        }

        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($raw)));

        if (strlen($clean) !== 11) {
            return ValidationResult::fail(
                'INVALID_CONTAINER_LENGTH',
                "Container number '$raw' must be exactly 11 characters (4 letters, 7 digits)",
                $fieldPath,
                $raw
            );
        }

        $prefix = substr($clean, 0, 4);
        $serial = substr($clean, 4, 6);
        $checkDigit = substr($clean, 10, 1);

        if (! ctype_alpha($prefix) || ! ctype_digit($serial) || ! ctype_digit($checkDigit)) {
            return ValidationResult::fail(
                'INVALID_CONTAINER_FORMAT',
                "Container number '$raw' does not match format (4 letters + 7 digits)",
                $fieldPath,
                $raw
            );
        }

        // Calculate ISO 6346 check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $char = $clean[$i];
            $val = ctype_alpha($char) ? (self::$letterValues[$char] ?? 0) : (int) $char;
            $sum += $val * (2 ** $i);
        }

        $calculatedCheckDigit = ($sum % 11) % 10;

        if ((int) $checkDigit !== $calculatedCheckDigit) {
            return ValidationResult::warn(
                'CONTAINER_CHECK_DIGIT_MISMATCH',
                "Container '$raw' has check digit '$checkDigit', expected '$calculatedCheckDigit'. Stored without correction.",
                $fieldPath,
                $clean
            );
        }

        return ValidationResult::pass($fieldPath, $clean, 'ISO_6346_VALID');
    }
}
