<?php

namespace App\Services\FrexoAi\Validation;

class ScopeIntegrityValidator
{
    /**
     * Verify sum integrity between itemized records (containers or HBLs) and master total.
     * Flags warnings if mismatch exceeds tolerance, NEVER redistributes or edits values.
     *
     * @param  array<float>  $itemizedValues
     */
    public function validateSum(
        ?float $masterTotal,
        array $itemizedValues,
        string $fieldPath = 'mbl.gross_weight',
        float $tolerancePercent = 2.0
    ): ?ValidationResult {
        if ($masterTotal === null || $masterTotal <= 0 || empty($itemizedValues)) {
            return null;
        }

        $sum = array_sum($itemizedValues);
        if ($sum <= 0) {
            return null;
        }

        $diff = abs($masterTotal - $sum);
        $allowedDiff = ($masterTotal * ($tolerancePercent / 100.0));

        if ($diff > $allowedDiff) {
            return ValidationResult::warn(
                'SCOPE_SUM_MISMATCH',
                "Master stated total ($masterTotal) differs from sum of items ($sum) by $diff. Kept as stated.",
                $fieldPath,
                $masterTotal
            );
        }

        return ValidationResult::pass($fieldPath, $masterTotal, 'SUM_RECONCILED');
    }

    /**
     * Verify that Master and House parties are kept at distinct scopes.
     * Master shipper can legitimately differ from House shipper.
     */
    public function validatePartyScope(
        ?string $masterShipper,
        ?string $houseShipper,
        string $fieldPath = 'hbls[].shipper_name'
    ): ValidationResult {
        if (empty($houseShipper)) {
            return ValidationResult::warn(
                'MISSING_HOUSE_SHIPPER',
                "House bill lacks explicit shipper. Must not silently inherit master shipper '$masterShipper'.",
                $fieldPath,
                null
            );
        }

        // Distinct is completely valid and standard in freight forwarding
        return ValidationResult::pass($fieldPath, $houseShipper);
    }
}
