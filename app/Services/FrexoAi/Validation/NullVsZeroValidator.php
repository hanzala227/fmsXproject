<?php

namespace App\Services\FrexoAi\Validation;

class NullVsZeroValidator
{
    /**
     * Ensure null values are not coerced into 0 or empty strings.
     * Zero is accepted only when the source explicitly contains 0.
     */
    public function validate(mixed $value, string $fieldPath, bool $isExplicitZeroInDoc = false): ValidationResult
    {
        if ($value === null || $value === '' || $value === 'N/A' || $value === 'null') {
            return ValidationResult::pass($fieldPath, null, 'EXPLICIT_NULL');
        }

        if ($value === 0 || $value === 0.0 || $value === '0') {
            if ($isExplicitZeroInDoc) {
                return ValidationResult::pass($fieldPath, 0, 'EXPLICIT_ZERO_STATED');
            }

            return ValidationResult::warn(
                'ZERO_VALUE_REQUIRES_EVIDENCE',
                "Field '$fieldPath' has value 0 without explicit evidence in document. Null preserved.",
                $fieldPath,
                null
            );
        }

        return ValidationResult::pass($fieldPath, $value);
    }
}
