<?php

namespace App\Services\FrexoAi\Validation;

use DateTime;

class DateLogicValidator
{
    /**
     * Validate date formatting, detect ambiguous day/month order, and check chronology.
     * Chronology checks give warnings, NEVER edits.
     */
    public function validateDate(?string $raw, string $fieldPath = 'mbl.etd'): ValidationResult
    {
        if (empty($raw)) {
            return ValidationResult::fail('EMPTY_DATE', 'Date value is empty', $fieldPath, null);
        }

        $trimmed = trim($raw);

        // Check for ambiguous slash formats e.g. 03/04/2026 vs 25/04/2026
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $trimmed, $m)) {
            $n1 = (int) $m[1];
            $n2 = (int) $m[2];
            $year = (int) $m[3];

            if ($n1 <= 12 && $n2 <= 12 && $n1 !== $n2) {
                return ValidationResult::warn(
                    'AMBIGUOUS_DATE_FORMAT',
                    "Date '$raw' has ambiguous day/month order ($n1 vs $n2). Preserved as raw.",
                    $fieldPath,
                    $raw
                );
            }

            // If n1 > 12, then n1 must be day: DD/MM/YYYY
            if ($n1 > 12) {
                $iso = sprintf('%04d-%02d-%02d', $year, $n2, $n1);

                return ValidationResult::pass($fieldPath, $iso);
            }

            // If n2 > 12, then n2 must be day: MM/DD/YYYY
            if ($n2 > 12) {
                $iso = sprintf('%04d-%02d-%02d', $year, $n1, $n2);

                return ValidationResult::pass($fieldPath, $iso);
            }
        }

        // Standard ISO Check
        $d = DateTime::createFromFormat('Y-m-d', substr($trimmed, 0, 10));
        if ($d && $d->format('Y-m-d') === substr($trimmed, 0, 10)) {
            return ValidationResult::pass($fieldPath, substr($trimmed, 0, 10));
        }

        return ValidationResult::warn(
            'NON_STANDARD_DATE_FORMAT',
            "Date '$raw' is in non-standard format. Preserved without edit.",
            $fieldPath,
            $raw
        );
    }

    /**
     * Check chronological ordering between departure and arrival.
     * Emits warning, NEVER swaps values.
     */
    public function validateChronology(?string $etd, ?string $eta, string $prefix = 'mbl.'): ?ValidationResult
    {
        if (empty($etd) || empty($eta)) {
            return null;
        }

        $dEtd = strtotime($etd);
        $dEta = strtotime($eta);

        if ($dEtd && $dEta && $dEtd > $dEta) {
            return ValidationResult::warn(
                'DEPARTURE_AFTER_ARRIVAL',
                "Departure ($etd) is later than arrival ($eta). Values kept as stated without swap.",
                $prefix.'etd'
            );
        }

        return null;
    }
}
