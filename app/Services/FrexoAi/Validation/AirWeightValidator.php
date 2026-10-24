<?php

namespace App\Services\FrexoAi\Validation;

class AirWeightValidator
{
    protected float $defaultFactor;

    public function __construct(?float $defaultFactor = null)
    {
        $this->defaultFactor = $defaultFactor ?? (float) config('frexo_ai.air_volumetric_factor', 166.67);
    }

    /**
     * Validate and balance air freight weights according to IATA standards.
     * Stated chargeable weight is NEVER overwritten with a calculated value.
     */
    public function validate(
        ?float $grossWeight,
        ?float $volumeCbm,
        ?float $statedChargeableWeight,
        ?float $customFactor = null,
        string $prefix = 'mbl.'
    ): array {
        $factor = $customFactor ?: $this->defaultFactor;
        $results = [];

        $calcVolumetric = null;
        if ($volumeCbm !== null && $volumeCbm > 0) {
            $calcVolumetric = round($volumeCbm * $factor, 2);
            $results['volume_weight'] = ValidationResult::pass(
                $prefix.'volume_weight',
                $calcVolumetric,
                'VOLUMETRIC_WEIGHT_CALCULATED'
            );
        }

        if ($grossWeight !== null && $grossWeight > 0) {
            $results['gross_weight'] = ValidationResult::pass(
                $prefix.'gross_weight',
                $grossWeight
            );
        }

        if ($statedChargeableWeight !== null && $statedChargeableWeight > 0) {
            // Document explicitly stated chargeable weight: NEVER overwrite!
            $expectedMin = max($grossWeight ?? 0, $calcVolumetric ?? 0);
            if ($expectedMin > 0 && $statedChargeableWeight < ($expectedMin * 0.95)) {
                $results['chargeable_weight'] = ValidationResult::warn(
                    'CHARGEABLE_WEIGHT_LOWER_THAN_MINIMUM',
                    "Document stated chargeable weight ({$statedChargeableWeight} KG) is lower than max(gross: {$grossWeight}, volumetric: {$calcVolumetric}). Preserved as stated.",
                    $prefix.'chargeable_weight',
                    $statedChargeableWeight
                );
            } else {
                $results['chargeable_weight'] = ValidationResult::pass(
                    $prefix.'chargeable_weight',
                    $statedChargeableWeight,
                    'DOCUMENT_STATED_CHARGEABLE_WEIGHT'
                );
            }
        } elseif ($grossWeight !== null || $calcVolumetric !== null) {
            // Not stated in document: derive rule max(gross, volumetric)
            $derived = max($grossWeight ?? 0, $calcVolumetric ?? 0);
            $results['chargeable_weight'] = ValidationResult::pass(
                $prefix.'chargeable_weight',
                $derived,
                'DERIVED_CHARGEABLE_WEIGHT'
            );
        } else {
            $results['chargeable_weight'] = ValidationResult::fail(
                'MISSING_AIR_WEIGHTS',
                'Neither gross weight nor volume is provided to determine chargeable weight',
                $prefix.'chargeable_weight',
                null
            );
        }

        return $results;
    }
}
