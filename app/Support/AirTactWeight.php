<?php

namespace App\Support;

final class AirTactWeight
{
    public const VOLUMETRIC_FACTOR = 166.6667;

    /**
     * Calculate IATA TACT 1:6 volumetric weight (kg) rounded up to the next 0.5 kg step.
     */
    public static function volumetricWeight(float $cbm): float
    {
        if ($cbm <= 0) {
            return 0.0;
        }

        $raw = round($cbm * self::VOLUMETRIC_FACTOR, 2);

        return ceil($raw * 2) / 2;
    }

    /**
     * Minimum chargeable weight per IATA TACT 1:6: max(gross kg, volumetric kg).
     */
    public static function minimumChargeable(float $grossKg, float $cbm): float
    {
        $gross = max(0.0, $grossKg);

        return max($gross, self::volumetricWeight($cbm));
    }

    /**
     * Clamp entered chargeable weight up to IATA TACT minimum when tampered below minimum or zero.
     */
    public static function clampChargeable(float $grossKg, float $cbm, float $current): float
    {
        $minRequired = self::minimumChargeable($grossKg, $cbm);

        if ($minRequired > 0 && ($current < $minRequired || $current == 0.0)) {
            return $minRequired;
        }

        return $current;
    }

    /**
     * Clamp nested HBL / HAWB rows so line-level chargeable weight cannot be tampered below IATA TACT minimum.
     */
    public static function clampHbls(array $hbls, bool $setKgAlias = false): array
    {
        foreach ($hbls as $idx => $hbl) {
            if (!is_array($hbl)) {
                continue;
            }
            $gross = floatval($hbl['gross_weight_kg'] ?? $hbl['gross_weight'] ?? 0);
            $cbm = floatval($hbl['volume_cbm'] ?? $hbl['volume'] ?? 0);
            $current = floatval($hbl['chargeable_weight_kg'] ?? $hbl['chargeable_weight'] ?? 0);
            $clamped = self::clampChargeable($gross, $cbm, $current);
            $minRequired = self::minimumChargeable($gross, $cbm);

            if ($minRequired > 0 && ($current < $minRequired || $current == 0.0 || array_key_exists('chargeable_weight', $hbl))) {
                $hbls[$idx]['chargeable_weight'] = $clamped;
            }
            if ($setKgAlias && (array_key_exists('chargeable_weight_kg', $hbl) || $clamped > 0)) {
                $hbls[$idx]['chargeable_weight_kg'] = $clamped;
            }
        }

        return $hbls;
    }
}
