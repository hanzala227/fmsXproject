<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IataMawbRule implements ValidationRule
{
    /**
     * Run the validation rule for IATA Resolution 600a Master Air Waybills.
     * 
     * Standard: 11 digits (3-digit airline prefix + 7-digit serial + 1 check-digit).
     * The 8th digit of the serial portion (check-digit) must equal the 7-digit serial % 7.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        if (strlen($clean) !== 11) {
            $fail('The :attribute must be an 11-digit IATA Air Waybill number (3-digit airline prefix + 7-digit serial + 1 check digit, e.g. 176-12345675).');
            return;
        }

        $serial = (int) substr($clean, 3, 7);
        $expectedCheck = $serial % 7;
        $actualCheck = (int) substr($clean, 10, 1);

        if ($actualCheck !== $expectedCheck) {
            $fail("The :attribute check-digit is invalid (IATA Res 600a Mod-7). Expected {$expectedCheck}, got {$actualCheck}.");
        }
    }
}

