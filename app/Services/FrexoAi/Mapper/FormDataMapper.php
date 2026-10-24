<?php

namespace App\Services\FrexoAi\Mapper;

use App\Services\FrexoAi\Registry\FieldRegistry;

class FormDataMapper
{
    /**
     * Map validated extraction form_data to the exact application payload structure,
     * strictly stripping any INTERNAL_WORKFLOW fields.
     */
    public static function mapToApplicationStructure(string $module, array $formData): array
    {
        $isAir = str_starts_with($module, 'air_');

        $result = [
            'mbl' => [],
            'master' => [],
            'hbls' => [],
            'hawbs' => [],
            'containers' => [],
            'routes' => [],
            'flights' => [],
        ];

        // 1. Process Master / MBL
        $masterSource = ! empty($formData['master']) ? $formData['master'] : ($formData['mbl'] ?? []);
        if (is_array($masterSource)) {
            foreach ($masterSource as $key => $val) {
                $path = $isAir ? "master.{$key}" : "mbl.{$key}";
                if (FieldRegistry::isInternalWorkflow($module, $path) || FieldRegistry::isInternalWorkflow($module, "mbl.{$key}")) {
                    // Strictly skip any internal workflow field
                    continue;
                }
                $result['master'][$key] = $val;
                $result['mbl'][$key] = $val;
            }
        }

        // 2. Process Containers
        if (isset($formData['containers']) && is_array($formData['containers'])) {
            foreach ($formData['containers'] as $cont) {
                if (! is_array($cont)) {
                    continue;
                }
                $cleanedCont = [];
                foreach ($cont as $k => $v) {
                    $path = "containers[].{$k}";
                    if (FieldRegistry::isInternalWorkflow($module, $path)) {
                        continue;
                    }
                    $cleanedCont[$k] = $v;
                }
                if (! empty($cleanedCont)) {
                    $result['containers'][] = $cleanedCont;
                }
            }
        }

        // 3. Process HBLs / HAWBs
        $houseSources = $formData['hawbs'] ?? ($formData['hbls'] ?? []);
        if (empty($houseSources) && isset($formData['hawb']) && is_array($formData['hawb'])) {
            $houseSources = [$formData['hawb']];
        }

        if (is_array($houseSources)) {
            foreach ($houseSources as $h) {
                if (! is_array($h)) {
                    continue;
                }
                $cleanedHouse = [];
                foreach ($h as $k => $v) {
                    $path = $isAir ? "hawb.{$k}" : "hbls[].{$k}";
                    if (FieldRegistry::isInternalWorkflow($module, $path)) {
                        continue;
                    }
                    $cleanedHouse[$k] = $v;
                }
                if (! empty($cleanedHouse)) {
                    $result['hbls'][] = $cleanedHouse;
                    $result['hawbs'][] = $cleanedHouse;
                }
            }
        }

        // 4. Process Routes / Flights
        $routeSources = $formData['routes'] ?? ($formData['flights'] ?? []);
        if (is_array($routeSources)) {
            foreach ($routeSources as $r) {
                if (! is_array($r)) {
                    continue;
                }
                $cleanedRoute = [];
                foreach ($r as $k => $v) {
                    $cleanedRoute[$k] = $v;
                }
                if (! empty($cleanedRoute)) {
                    $result['routes'][] = $cleanedRoute;
                    $result['flights'][] = $cleanedRoute;
                }
            }
        }

        return $result;
    }
}
