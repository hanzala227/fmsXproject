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
        $result = [
            'mbl' => [],
            'hbls' => [],
            'containers' => [],
            'routes' => [],
        ];

        // 1. Process MBL
        if (isset($formData['mbl']) && is_array($formData['mbl'])) {
            foreach ($formData['mbl'] as $key => $val) {
                $path = "mbl.$key";
                if (FieldRegistry::isInternalWorkflow($module, $path)) {
                    // Strictly skip any internal workflow field
                    continue;
                }
                $result['mbl'][$key] = $val;
            }
        }

        // 2. Process Containers
        if (isset($formData['containers']) && is_array($formData['containers'])) {
            foreach ($formData['containers'] as $cont) {
                if (!is_array($cont)) continue;
                $cleanedCont = [];
                foreach ($cont as $k => $v) {
                    $path = "containers[].$k";
                    if (FieldRegistry::isInternalWorkflow($module, $path)) continue;
                    $cleanedCont[$k] = $v;
                }
                if (!empty($cleanedCont)) {
                    $result['containers'][] = $cleanedCont;
                }
            }
        }

        // 3. Process HBLs / HAWBs
        if (isset($formData['hbls']) && is_array($formData['hbls'])) {
            foreach ($formData['hbls'] as $hbl) {
                if (!is_array($hbl)) continue;
                $cleanedHbl = [];
                foreach ($hbl as $k => $v) {
                    $path = "hbls[].$k";
                    if (FieldRegistry::isInternalWorkflow($module, $path)) continue;
                    $cleanedHbl[$k] = $v;
                }
                if (!empty($cleanedHbl)) {
                    $result['hbls'][] = $cleanedHbl;
                }
            }
        }

        // 4. Process Routes / Flights
        $routeSources = $formData['routes'] ?? ($formData['flights'] ?? []);
        if (is_array($routeSources)) {
            foreach ($routeSources as $r) {
                if (!is_array($r)) continue;
                $cleanedRoute = [];
                foreach ($r as $k => $v) {
                    $cleanedRoute[$k] = $v;
                }
                if (!empty($cleanedRoute)) {
                    $result['routes'][] = $cleanedRoute;
                }
            }
        }

        if (str_starts_with($module, 'air_')) {
            $result['flights'] = $result['routes'];
        }

        return $result;
    }
}
