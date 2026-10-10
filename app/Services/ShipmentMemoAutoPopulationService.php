<?php

namespace App\Services;

use App\Models\ShipmentMemoConfig;
use App\Models\OceanExport;
use App\Models\OceanImport;
use App\Models\AirExport;
use App\Models\AirImport;

/**
 * Service to auto-populate invoice/memo fields based on Shipment Memo Auto-Load configuration
 */
class ShipmentMemoAutoPopulationService
{
    /**
     * Get auto-populated data for invoice generation
     * 
     * @param string $module Module type (ocean-export, ocean-import, air-export, air-import, trucker, misc)
     * @param object $shipment Shipment model instance
     * @param string $section Section type (master_bl or house_bl)
     * @return array Auto-populated data based on configuration
     */
    public function getAutoPopulatedData($module, $shipment, $section = 'master_bl')
    {
        // Load enabled fields from configuration
        $enabledFields = ShipmentMemoConfig::where('module', $module)
            ->where('section', $section)
            ->where('is_enabled', true)
            ->pluck('field_name')
            ->toArray();

        // If no fields are enabled, return empty array
        if (empty($enabledFields)) {
            return [];
        }

        // Get data based on enabled fields
        $data = [];
        
        foreach ($enabledFields as $field) {
            $value = $this->extractFieldValue($shipment, $field, $module, $section);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    /**
     * Extract field value from shipment based on field name
     * 
     * @param object $shipment Shipment model instance
     * @param string $field Field name from configuration
     * @param string $module Module type
     * @param string $section Section type
     * @return mixed Field value or null
     */
    private function extractFieldValue($shipment, $field, $module, $section)
    {
        // Map configuration field names to shipment attributes/relationships
        $fieldMappings = $this->getFieldMappings($module);

        if (!isset($fieldMappings[$field])) {
            return null;
        }

        $mapping = $fieldMappings[$field];

        // Handle direct attribute access
        if (is_string($mapping)) {
            return $this->getNestedValue($shipment, $mapping);
        }

        // Handle callable mapping
        if (is_callable($mapping)) {
            return $mapping($shipment);
        }

        return null;
    }

    /**
     * Get nested value from object using dot notation
     * 
     * @param object $object Object to extract value from
     * @param string $path Dot notation path (e.g., 'vessel.name')
     * @return mixed Value or null
     */
    private function getNestedValue($object, $path)
    {
        if (!$object) return null;
        $keys = explode('.', $path);
        $value = $object;

        foreach ($keys as $key) {
            try {
                if (is_object($value)) {
                    $value = $value->{$key} ?? null;
                } elseif (is_array($value) && isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return null;
                }
            } catch (\Throwable $e) {
                return null;
            }
            if ($value === null) return null;
        }

        return $value;
    }

    /**
     * Get field mappings for each module
     * Maps configuration field names to shipment model attributes
     * 
     * @param string $module Module type
     * @return array Field mappings
     */
    private function getFieldMappings($module)
    {
        switch ($module) {
            case 'ocean-export':
                return [
                    // Master B/L fields
                    'oversea_agent' => 'forwardingAgent.name',
                    'carrier' => function($s) {
                        return is_object($s->carrier) ? $s->carrier->name : $s->carrier;
                    },
                    'shipper' => 'dmShipper.name',
                    'consignee' => 'dmConsignee.name',
                    'notify' => 'dmNotify.name',
                    'sales' => 'salesPerson.name',
                    
                    // House B/L fields
                    'mbl_shipper' => 'dmShipper.name',
                    'mbl_consignee' => 'dmConsignee.name',
                    'mbl_notify' => 'dmNotify.name',
                    'hbl_shipper' => function($s) {
                        return $s->hbls->first()?->shipper ?? $s->dmShipper?->name;
                    },
                    'hbl_consignee' => function($s) {
                        return $s->hbls->first()?->consignee ?? $s->dmConsignee?->name;
                    },
                    'hbl_notify' => function($s) {
                        return $s->hbls->first()?->notify ?? $s->dmNotify?->name;
                    },
                    'delivery_agent' => 'deliveryAgent.name',
                    'customer' => 'dmCustomer.name',
                ];

            case 'ocean-import':
                return [
                    // Master B/L fields
                    'oversea_agent' => 'forwardingAgent.name',
                    'carrier' => function($s) {
                        return is_object($s->carrier) ? $s->carrier->name : $s->carrier;
                    },
                    'shipper' => 'dmShipper.name',
                    'consignee' => 'dmConsignee.name',
                    'notify' => 'dmNotify.name',
                    'delivery_agent' => 'deliveryAgent.name',
                    'sales' => 'salesPerson.name',
                    
                    // House B/L fields
                    'mbl_shipper' => 'dmShipper.name',
                    'mbl_consignee' => 'dmConsignee.name',
                    'mbl_notify' => 'dmNotify.name',
                    'hbl_shipper' => function($s) {
                        return $s->hbls->first()?->shipper ?? $s->dmShipper?->name;
                    },
                    'hbl_consignee' => function($s) {
                        return $s->hbls->first()?->consignee ?? $s->dmConsignee?->name;
                    },
                    'hbl_notify' => function($s) {
                        return $s->hbls->first()?->notify ?? $s->dmNotify?->name;
                    },
                    'customer' => 'dmCustomer.name',
                ];

            case 'air-export':
                return [
                    // Master B/L fields
                    'oversea_agent' => 'forwardingAgent.name',
                    'carrier' => 'carrier.name',
                    'shipper' => 'dmShipper.name',
                    'consignee' => 'dmConsignee.name',
                    'sales' => 'salesPerson.name',
                    'agent' => 'agent.name',
                    
                    // House B/L fields
                    'mbl_shipper' => 'dmShipper.name',
                    'mbl_consignee' => 'dmConsignee.name',
                    'mbl_notify' => 'dmNotify.name',
                    'hbl_shipper' => function($s) {
                        return $s->hbls->first()?->shipper ?? $s->dmShipper?->name;
                    },
                    'hbl_consignee' => function($s) {
                        return $s->hbls->first()?->consignee ?? $s->dmConsignee?->name;
                    },
                    'hbl_notify' => function($s) {
                        return $s->hbls->first()?->notify ?? $s->dmNotify?->name;
                    },
                    'co_loader' => 'coLoader.name',
                    'customer' => 'dmCustomer.name',
                ];

            case 'air-import':
                return [
                    // Master B/L fields
                    'oversea_agent' => 'forwardingAgent.name',
                    'carrier' => 'carrier.name',
                    'shipper' => 'dmShipper.name',
                    'consignee' => 'dmConsignee.name',
                    'notify' => 'dmNotify.name',
                    'delivery_agent' => 'deliveryAgent.name',
                    'sales' => 'salesPerson.name',
                    
                    // House B/L fields
                    'mbl_shipper' => 'dmShipper.name',
                    'mbl_consignee' => 'dmConsignee.name',
                    'hbl_shipper' => function($s) {
                        return $s->hbls->first()?->shipper ?? $s->dmShipper?->name;
                    },
                    'hbl_consignee' => function($s) {
                        return $s->hbls->first()?->consignee ?? $s->dmConsignee?->name;
                    },
                    'hbl_notify' => function($s) {
                        return $s->hbls->first()?->notify ?? $s->dmNotify?->name;
                    },
                    'customer' => 'dmCustomer.name',
                ];

            case 'trucker':
                return [
                    // Master fields only (no House B/L for trucker)
                    'customer' => 'customer.name',
                    'shipper' => 'shipper.name',
                    'consignee' => 'consignee.name',
                    'sales' => 'salesPerson.name',
                    'trucker' => 'trucker.name',
                ];

            case 'misc':
                return [
                    // Master fields only
                    'customer' => 'customer.name',
                    'agent' => 'agent.name',
                    'shipper' => 'shipper.name',
                    'consignee' => 'consignee.name',
                    'sales' => 'salesPerson.name',
                    'office' => 'office.name',
                ];

            default:
                return [];
        }
    }

    /**
     * Check if auto-population is enabled for a module and section
     * 
     * @param string $module Module type
     * @param string $section Section type
     * @return bool True if any fields are enabled
     */
    public function isAutoPopulationEnabled($module, $section = 'master_bl')
    {
        return ShipmentMemoConfig::where('module', $module)
            ->where('section', $section)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Get list of enabled field names for a module and section
     * 
     * @param string $module Module type
     * @param string $section Section type
     * @return array Array of enabled field names
     */
    public function getEnabledFields($module, $section = 'master_bl')
    {
        return ShipmentMemoConfig::where('module', $module)
            ->where('section', $section)
            ->where('is_enabled', true)
            ->orderBy('order')
            ->pluck('field_name')
            ->toArray();
    }
}
