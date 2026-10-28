<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Iso6346ContainerRule implements ValidationRule
{
    private static array $letterValues = [
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19,
        'J' => 20, 'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29,
        'S' => 30, 'T' => 31, 'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38,
    ];

    /**
     * Run the validation rule for ISO 6346 Intermodal Shipping Container numbers.
     * Format: 4 uppercase letters + 6 digits + 1 Mod-11 check digit (11 characters total).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim((string) $value)));

        if (strlen($clean) !== 11) {
            $fail('The :attribute must be an 11-character ISO 6346 container number (4 letters + 6 digits + 1 check digit, e.g. MSCU1234567).');
            return;
        }

        $prefix = substr($clean, 0, 4);
        $serial = substr($clean, 4, 6);
        $checkDigit = substr($clean, 10, 1);

        if (!ctype_alpha($prefix) || !ctype_digit($serial) || !ctype_digit($checkDigit)) {
            $fail('The :attribute does not match standard container format (4 letters + 6 digits + 1 check digit).');
            return;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $char = $clean[$i];
            $val = ctype_alpha($char) ? (self::$letterValues[$char] ?? 0) : (int) $char;
            $sum += $val * (2 ** $i);
        }

        $expected = ($sum % 11) % 10;
        if ((int) $checkDigit !== $expected) {
            $fail("The :attribute has an invalid ISO 6346 Mod-11 check digit. Expected {$expected}, but got {$checkDigit}.");
        }
    }
}

