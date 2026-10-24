<?php

namespace App\Services\FrexoAi\Registry;

use InvalidArgumentException;

class FieldRegistry
{
    public const MODULE_OCEAN_IMPORT = 'ocean_import';

    public const MODULE_OCEAN_EXPORT = 'ocean_export';

    public const MODULE_AIR_IMPORT = 'air_import';

    public const MODULE_AIR_EXPORT = 'air_export';

    /**
     * @var array<string, array<string, FieldDefinition>>
     */
    protected static array $registry = [];

    public static function getFields(string $module): array
    {
        self::ensureInitialized($module);

        return self::$registry[$module] ?? [];
    }

    public static function getField(string $module, string $path): ?FieldDefinition
    {
        self::ensureInitialized($module);

        return self::$registry[$module][$path] ?? null;
    }

    public static function isExtractable(string $module, string $path): bool
    {
        $field = self::getField($module, $path);

        return $field ? $field->isExtractable() : false;
    }

    public static function isInternalWorkflow(string $module, string $path): bool
    {
        $field = self::getField($module, $path);

        return $field ? $field->isInternalWorkflow() : false;
    }

    public static function getAllModules(): array
    {
        return [
            self::MODULE_OCEAN_IMPORT,
            self::MODULE_OCEAN_EXPORT,
            self::MODULE_AIR_IMPORT,
            self::MODULE_AIR_EXPORT,
        ];
    }

    protected static function ensureInitialized(string $module): void
    {
        if (isset(self::$registry[$module])) {
            return;
        }

        switch ($module) {
            case self::MODULE_OCEAN_IMPORT:
                self::$registry[$module] = self::buildOceanImportRegistry();
                break;
            case self::MODULE_OCEAN_EXPORT:
                self::$registry[$module] = self::buildOceanExportRegistry();
                break;
            case self::MODULE_AIR_IMPORT:
                self::$registry[$module] = self::buildAirImportRegistry();
                break;
            case self::MODULE_AIR_EXPORT:
                self::$registry[$module] = self::buildAirExportRegistry();
                break;
            default:
                throw new InvalidArgumentException("Unknown module '$module'.");
        }
    }

    protected static function buildCommonInternalFields(string $masterScope): array
    {
        return [
            'mbl.file_no' => new FieldDefinition('mbl.file_no', 'string', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'System auto-generated file reference number'),
            'mbl.post_date' => new FieldDefinition('mbl.post_date', 'date', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Posting date in company workflow'),
            'mbl.op_id' => new FieldDefinition('mbl.op_id', 'integer', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Internal Operator User ID'),
            'mbl.op_name' => new FieldDefinition('mbl.op_name', 'string', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Internal Operator Name'),
            'mbl.dm_sales_person_id' => new FieldDefinition('mbl.dm_sales_person_id', 'integer', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Internal Sales Person User ID'),
            'mbl.dm_sales_person_name' => new FieldDefinition('mbl.dm_sales_person_name', 'string', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Internal Sales Person Name'),
            'mbl.company_id' => new FieldDefinition('mbl.company_id', 'integer', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Tenant Company ID'),
            'mbl.id' => new FieldDefinition('mbl.id', 'integer', $masterScope, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Primary Database Key'),
        ];
    }

    protected static function buildOceanImportRegistry(): array
    {
        $fields = self::buildCommonInternalFields(FieldDefinition::SCOPE_MASTER);

        // MBL Extractable
        $extractableMbl = [
            'mbl.mbl_no' => new FieldDefinition('mbl.mbl_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_USER_CONFIRMED, null, null, null, 'mbl_format', 100, 'Master Bill of Lading Number'),
            'mbl.booking_no' => new FieldDefinition('mbl.booking_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 80, 'Carrier Booking Confirmation Number'),
            'mbl.carrier_name' => new FieldDefinition('mbl.carrier_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'Ocean Shipping Line'),
            'mbl.forwarding_agent_name' => new FieldDefinition('mbl.forwarding_agent_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Origin Forwarding Agent'),
            'mbl.oversea_agent_name' => new FieldDefinition('mbl.oversea_agent_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Overseas Agent'),
            'mbl.co_loader_name' => new FieldDefinition('mbl.co_loader_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 60, 'Co-loader Entity'),
            'mbl.agent_ref_no' => new FieldDefinition('mbl.agent_ref_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Agent / Customer Reference Number'),
            'mbl.contract_no' => new FieldDefinition('mbl.contract_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Service Contract Number'),
            'mbl.sales_type' => new FieldDefinition('mbl.sales_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Sales Type (e.g. FOB, CIF, Freehand)'),
            'mbl.bl_type' => new FieldDefinition('mbl.bl_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'ORIGINAL,EXPRESS,SEAWAY', 'uppercase', null, 80, 'Type of BL'),
            'mbl.sub_bl_no' => new FieldDefinition('mbl.sub_bl_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Sub BL Number'),
            'mbl.dm_notify_name' => new FieldDefinition('mbl.dm_notify_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Notify Party on Master'),
            'mbl.cargo_type' => new FieldDefinition('mbl.cargo_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 50, 'Cargo Type (e.g. GENERAL CARGO, HAZARDOUS)'),
            'mbl.vessel_name' => new FieldDefinition('mbl.vessel_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 85, 'Ocean Mother Vessel Name'),
            'mbl.voyage' => new FieldDefinition('mbl.voyage', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 85, 'Voyage Identification'),
            'mbl.pol_name' => new FieldDefinition('mbl.pol_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', 'unlocode_or_name', 90, 'Port of Loading'),
            'mbl.pod_name' => new FieldDefinition('mbl.pod_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', 'unlocode_or_name', 90, 'Port of Discharge'),
            'mbl.del_name' => new FieldDefinition('mbl.del_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 80, 'Place of Delivery'),
            'mbl.fdest_name' => new FieldDefinition('mbl.fdest_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 80, 'Final Destination'),
            'mbl.cy_location_name' => new FieldDefinition('mbl.cy_location_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Container Yard Location'),
            'mbl.cfs_location_name' => new FieldDefinition('mbl.cfs_location_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Container Freight Station Location'),
            'mbl.etd' => new FieldDefinition('mbl.etd', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Estimated Time of Departure'),
            'mbl.eta' => new FieldDefinition('mbl.eta', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Estimated Time of Arrival'),
            'mbl.business_referred_by_name' => new FieldDefinition('mbl.business_referred_by_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 40, 'Business Reference / Referral'),
            'mbl.freight_term' => new FieldDefinition('mbl.freight_term', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'PREPAID,COLLECT', 'uppercase', null, 85, 'Freight Payment Term'),
            'mbl.incoterm' => new FieldDefinition('mbl.incoterm', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'EXW,FCA,CPT,CIP,DAP,DPU,DDP,FAS,FOB,CFR,CIF', 'uppercase', 'incoterm_valid', 80, 'Incoterm Code'),
            'mbl.description' => new FieldDefinition('mbl.description', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Master Cargo Description'),
        ];

        // Containers
        $containers = [
            'containers[].container_no' => new FieldDefinition('containers[].container_no', 'string', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_USER_CONFIRMED, null, null, 'uppercase', 'iso_6346_container', 95, 'ISO 6346 Container Number'),
            'containers[].seal_no' => new FieldDefinition('containers[].seal_no', 'string', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 80, 'Container Seal Number'),
            'containers[].container_type' => new FieldDefinition('containers[].container_type', 'string', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'container_iso_type', null, 85, 'Container Equipment Code'),
            'containers[].package_unit' => new FieldDefinition('containers[].package_unit', 'string', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'unece_package_unit', null, 70, 'Package Unit (CTN, PLT, PCS)'),
            'containers[].weight' => new FieldDefinition('containers[].weight', 'number', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 80, 'Container Gross Weight'),
            'containers[].volume' => new FieldDefinition('containers[].volume', 'number', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'CBM', null, 'float_val', 'positive_number', 80, 'Container Volume Measurement'),
            'containers[].pieces' => new FieldDefinition('containers[].pieces', 'integer', FieldDefinition::SCOPE_CONTAINER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'PCS', null, 'int_val', 'positive_integer', 80, 'Container Package Count'),
        ];

        // HBLs
        $hbls = [
            'hbls[].hbl_no' => new FieldDefinition('hbls[].hbl_no', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_USER_CONFIRMED, null, null, null, null, 100, 'House Bill of Lading Number'),
            'hbls[].shipper_name' => new FieldDefinition('hbls[].shipper_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 95, 'Shipper Company Name'),
            'hbls[].consignee_name' => new FieldDefinition('hbls[].consignee_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 95, 'Consignee Company Name'),
            'hbls[].notify_party_name' => new FieldDefinition('hbls[].notify_party_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'Notify Party Company Name'),
            'hbls[].customer_name' => new FieldDefinition('hbls[].customer_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Customer / Booking Party'),
            'hbls[].sales_person_name' => new FieldDefinition('hbls[].sales_person_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_INTERNAL_WORKFLOW, FieldDefinition::OVERWRITE_NEVER, null, null, null, null, 0, 'Internal Sales Person'),
            'hbls[].bl_type' => new FieldDefinition('hbls[].bl_type', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'ORIGINAL,EXPRESS', 'uppercase', null, 75, 'House B/L Type'),
            'hbls[].cargo_type' => new FieldDefinition('hbls[].cargo_type', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 60, 'House Cargo Type'),
            'hbls[].freight_term' => new FieldDefinition('hbls[].freight_term', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'PREPAID,COLLECT', 'uppercase', null, 85, 'House Freight Term'),
            'hbls[].pieces' => new FieldDefinition('hbls[].pieces', 'integer', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'PCS', null, 'int_val', 'positive_integer', 90, 'House Total Pieces'),
            'hbls[].weight' => new FieldDefinition('hbls[].weight', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 90, 'House Gross Weight'),
            'hbls[].volume' => new FieldDefinition('hbls[].volume', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'CBM', null, 'float_val', 'positive_number', 90, 'House Volume'),
            'hbls[].hbl_description' => new FieldDefinition('hbls[].hbl_description', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 70, 'House Goods Description'),
            'hbls[].hbl_mark' => new FieldDefinition('hbls[].hbl_mark', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 70, 'Marks and Numbers'),
            'hbls[].vessel_name' => new FieldDefinition('hbls[].vessel_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 75, 'House Vessel'),
            'hbls[].voyage' => new FieldDefinition('hbls[].voyage', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 75, 'House Voyage'),
            'hbls[].pol_name' => new FieldDefinition('hbls[].pol_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 85, 'House Port of Loading'),
            'hbls[].pod_name' => new FieldDefinition('hbls[].pod_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 85, 'House Port of Discharge'),
            'hbls[].receipt_name' => new FieldDefinition('hbls[].receipt_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 75, 'House Place of Receipt'),
            'hbls[].del_name' => new FieldDefinition('hbls[].del_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 75, 'House Place of Delivery'),
            'hbls[].fdest_name' => new FieldDefinition('hbls[].fdest_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 75, 'House Final Destination'),
            'hbls[].pre_carriage_by' => new FieldDefinition('hbls[].pre_carriage_by', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Pre-Carriage Carrier / Mode'),
            'hbls[].date_of_issue' => new FieldDefinition('hbls[].date_of_issue', 'date', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 80, 'Date of Issue of House B/L'),
            'hbls[].sc_no' => new FieldDefinition('hbls[].sc_no', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Service Contract / Export Ref'),
            'hbls[].ship_mode' => new FieldDefinition('hbls[].ship_mode', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'FCL,LCL', 'uppercase', null, 75, 'Shipping Mode'),
            'hbls[].incoterms_id' => new FieldDefinition('hbls[].incoterms_id', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'EXW,FCA,CPT,CIP,DAP,DPU,DDP,FAS,FOB,CFR,CIF', 'uppercase', 'incoterm_valid', 80, 'House Incoterm'),
        ];

        // Routes
        $routes = [
            'routes[].vessel_name' => new FieldDefinition('routes[].vessel_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 80, 'Route Leg Vessel'),
            'routes[].voyage' => new FieldDefinition('routes[].voyage', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 80, 'Route Leg Voyage'),
            'routes[].pol_name' => new FieldDefinition('routes[].pol_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 85, 'Leg Port of Loading'),
            'routes[].pod_name' => new FieldDefinition('routes[].pod_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 85, 'Leg Port of Discharge'),
            'routes[].etd' => new FieldDefinition('routes[].etd', 'date', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Leg ETD'),
            'routes[].eta' => new FieldDefinition('routes[].eta', 'date', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Leg ETA'),
        ];

        return array_merge($fields, $extractableMbl, $containers, $hbls, $routes);
    }

    protected static function buildOceanExportRegistry(): array
    {
        $base = self::buildOceanImportRegistry();

        // Additional Export MBL fields
        $exportMbl = [
            'mbl.receipt_name' => new FieldDefinition('mbl.receipt_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'port_name', null, 75, 'Place of Receipt'),
            'mbl.return_location_name' => new FieldDefinition('mbl.return_location_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Empty Return Depot'),
            'mbl.empty_pickup_name' => new FieldDefinition('mbl.empty_pickup_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Empty Container Pickup Location'),
            'mbl.delivery_location_name' => new FieldDefinition('mbl.delivery_location_name', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Cargo Delivery Location'),
            'mbl.atd' => new FieldDefinition('mbl.atd', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 85, 'Actual Time of Departure'),
            'mbl.ata' => new FieldDefinition('mbl.ata', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 85, 'Actual Time of Arrival'),
            'mbl.receipt_etd' => new FieldDefinition('mbl.receipt_etd', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 60, 'Receipt Cutoff/ETD'),
            'mbl.final_eta' => new FieldDefinition('mbl.final_eta', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 70, 'Final Destination ETA'),
            'mbl.etb' => new FieldDefinition('mbl.etb', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 60, 'Estimated Time of Berthing'),
            'mbl.port_cutoff' => new FieldDefinition('mbl.port_cutoff', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Port Cargo Cutoff'),
            'mbl.doc_cutoff' => new FieldDefinition('mbl.doc_cutoff', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Shipping Instructions Cutoff'),
            'mbl.vgm_cutoff' => new FieldDefinition('mbl.vgm_cutoff', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'VGM Cutoff'),
            'mbl.rail_cutoff' => new FieldDefinition('mbl.rail_cutoff', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 60, 'Rail Cutoff'),
            'mbl.latest_gate_in' => new FieldDefinition('mbl.latest_gate_in', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 65, 'Latest Gate In Date'),
            'mbl.obl_received_date' => new FieldDefinition('mbl.obl_received_date', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 70, 'Original B/L Received Date'),
            'mbl.released_date' => new FieldDefinition('mbl.released_date', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 70, 'Cargo Release Date'),
            'mbl.obl_type' => new FieldDefinition('mbl.obl_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'OBL Type'),
            'mbl.ship_mode' => new FieldDefinition('mbl.ship_mode', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'FCL,LCL', 'uppercase', null, 75, 'Shipping Mode'),
            'mbl.is_express_bl' => new FieldDefinition('mbl.is_express_bl', 'boolean', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'bool_val', null, 70, 'Express B/L Indicator'),
        ];

        return array_merge($base, $exportMbl);
    }

    protected static function buildAirImportRegistry(): array
    {
        $fields = self::buildCommonInternalFields(FieldDefinition::SCOPE_MASTER);

        $airMbl = [
            'mbl.mawb_no' => new FieldDefinition('mbl.mawb_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_USER_CONFIRMED, null, null, 'mawb_sanitizer', 'iata_mawb_11digit', 100, 'IATA 11-Digit Master Air Waybill Number'),
            'mbl.booking_no' => new FieldDefinition('mbl.booking_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 80, 'Airline Booking Reference'),
            'mbl.carrier_name' => new FieldDefinition('mbl.carrier_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'Airline Carrier Name'),
            'mbl.flight_no' => new FieldDefinition('mbl.flight_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', 'flight_no_format', 85, 'Flight Number'),
            'mbl.departure_name' => new FieldDefinition('mbl.departure_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', 'iata_airport_code', 95, 'Departure Airport'),
            'mbl.destination_name' => new FieldDefinition('mbl.destination_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', 'iata_airport_code', 95, 'Destination Airport'),
            'mbl.etd' => new FieldDefinition('mbl.etd', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Estimated Time of Departure'),
            'mbl.eta' => new FieldDefinition('mbl.eta', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Estimated Time of Arrival'),
            'mbl.atd' => new FieldDefinition('mbl.atd', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 85, 'Actual Time of Departure'),
            'mbl.ata' => new FieldDefinition('mbl.ata', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 85, 'Actual Time of Arrival'),
            'mbl.shipper_name' => new FieldDefinition('mbl.shipper_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'Master Shipper Name'),
            'mbl.consignee_name' => new FieldDefinition('mbl.consignee_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'Master Consignee Name'),
            'mbl.notify_party_name' => new FieldDefinition('mbl.notify_party_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 85, 'Master Notify Party'),
            'mbl.forwarding_agent_name' => new FieldDefinition('mbl.forwarding_agent_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Forwarding Agent'),
            'mbl.oversea_agent_name' => new FieldDefinition('mbl.oversea_agent_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Overseas Agent'),
            'mbl.co_loader_name' => new FieldDefinition('mbl.co_loader_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 60, 'Co-loader'),
            'mbl.agent_ref_no' => new FieldDefinition('mbl.agent_ref_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Agent Reference Number'),
            'mbl.contract_no' => new FieldDefinition('mbl.contract_no', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Contract Number'),
            'mbl.sales_type' => new FieldDefinition('mbl.sales_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 50, 'Sales Type'),
            'mbl.freight_term' => new FieldDefinition('mbl.freight_term', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'PREPAID,COLLECT', 'uppercase', null, 85, 'Freight Term'),
            'mbl.incoterms' => new FieldDefinition('mbl.incoterms', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'EXW,FCA,CPT,CIP,DAP,DPU,DDP,FAS,FOB,CFR,CIF', 'uppercase', 'incoterm_valid', 80, 'Incoterms'),
            'mbl.cargo_type' => new FieldDefinition('mbl.cargo_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 50, 'Cargo Type'),
            'mbl.gross_weight' => new FieldDefinition('mbl.gross_weight', 'number', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 95, 'Total Gross Weight in KG'),
            'mbl.chargeable_weight' => new FieldDefinition('mbl.chargeable_weight', 'number', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 95, 'Total Chargeable Weight in KG'),
            'mbl.volume' => new FieldDefinition('mbl.volume', 'number', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'CBM', null, 'float_val', 'positive_number', 90, 'Total Volume in CBM'),
            'mbl.volume_weight' => new FieldDefinition('mbl.volume_weight', 'number', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_CALCULATED, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 80, 'IATA Volumetric Weight in KG (1 CBM = 166.67 KG)'),
            'mbl.pieces' => new FieldDefinition('mbl.pieces', 'integer', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'PCS', null, 'int_val', 'positive_integer', 90, 'Total Number of Packages/Pieces'),
            'mbl.description' => new FieldDefinition('mbl.description', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 60, 'Cargo Nature / Description'),
        ];

        $flights = [
            'flights[].flight_no' => new FieldDefinition('flights[].flight_no', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', 'flight_no_format', 85, 'Flight Number'),
            'flights[].carrier_name' => new FieldDefinition('flights[].carrier_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 80, 'Leg Airline'),
            'flights[].departure_name' => new FieldDefinition('flights[].departure_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', null, 90, 'Leg Departure Airport'),
            'flights[].destination_name' => new FieldDefinition('flights[].destination_name', 'string', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', null, 90, 'Leg Destination Airport'),
            'flights[].etd' => new FieldDefinition('flights[].etd', 'date', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Leg ETD'),
            'flights[].eta' => new FieldDefinition('flights[].eta', 'date', FieldDefinition::SCOPE_LEG, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 75, 'Leg ETA'),
        ];

        $hawbs = [
            'hbls[].hawb_no' => new FieldDefinition('hbls[].hawb_no', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_USER_CONFIRMED, null, null, null, null, 100, 'House Air Waybill Number'),
            'hbls[].shipper_name' => new FieldDefinition('hbls[].shipper_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 95, 'House Shipper Name'),
            'hbls[].consignee_name' => new FieldDefinition('hbls[].consignee_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 95, 'House Consignee Name'),
            'hbls[].notify_party_name' => new FieldDefinition('hbls[].notify_party_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 90, 'House Notify Party'),
            'hbls[].gross_weight' => new FieldDefinition('hbls[].gross_weight', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 95, 'House Gross Weight'),
            'hbls[].chargeable_weight' => new FieldDefinition('hbls[].chargeable_weight', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 95, 'House Chargeable Weight'),
            'hbls[].volume' => new FieldDefinition('hbls[].volume', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'CBM', null, 'float_val', 'positive_number', 90, 'House Volume'),
            'hbls[].volume_weight' => new FieldDefinition('hbls[].volume_weight', 'number', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_CALCULATED, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'KG', null, 'float_val', 'positive_number', 80, 'House Volumetric Weight'),
            'hbls[].pieces' => new FieldDefinition('hbls[].pieces', 'integer', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, 'PCS', null, 'int_val', 'positive_integer', 90, 'House Pieces Count'),
            'hbls[].description' => new FieldDefinition('hbls[].description', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, null, null, 70, 'House Goods Description'),
        ];

        return array_merge($fields, $airMbl, $flights, $hawbs);
    }

    protected static function buildAirExportRegistry(): array
    {
        $base = self::buildAirImportRegistry();

        $airExportAdditions = [
            'mbl.awb_date' => new FieldDefinition('mbl.awb_date', 'date', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'date_iso', 'date_format', 80, 'Date of AWB Issue'),
            'mbl.issuing_carrier' => new FieldDefinition('mbl.issuing_carrier', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 75, 'Issuing Airline / Agent'),
            'mbl.actual_shipper_name' => new FieldDefinition('mbl.actual_shipper_name', 'string', FieldDefinition::SCOPE_PARTY, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'trade_partner_name', null, 70, 'Actual Shipper'),
            'mbl.awb_type' => new FieldDefinition('mbl.awb_type', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'NORMAL,DIRECT', 'uppercase', null, 70, 'AWB Type'),
            'mbl.pkg_unit' => new FieldDefinition('mbl.pkg_unit', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'unece_package_unit', null, 70, 'Package Unit (PCS, CTN, PLT)'),
            'mbl.dv_carriage' => new FieldDefinition('mbl.dv_carriage', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 70, 'Declared Value for Carriage (e.g. N.V.D.)'),
            'mbl.dv_customs' => new FieldDefinition('mbl.dv_customs', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 70, 'Declared Value for Customs (e.g. N.C.V.)'),
            'mbl.wt_val' => new FieldDefinition('mbl.wt_val', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'P,C', 'uppercase', null, 75, 'Weight/Valuation Charge Term (P=Prepaid, C=Collect)'),
            'mbl.other_term' => new FieldDefinition('mbl.other_term', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'P,C', 'uppercase', null, 75, 'Other Charges Term (P=Prepaid, C=Collect)'),
            'mbl.insurance' => new FieldDefinition('mbl.insurance', 'string', FieldDefinition::SCOPE_MASTER, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'uppercase', null, 70, 'Insurance Stated Amount or N.I.L.'),
            'hbls[].pkg_unit' => new FieldDefinition('hbls[].pkg_unit', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'unece_package_unit', null, 70, 'House Package Unit'),
            'hbls[].departure_name' => new FieldDefinition('hbls[].departure_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', null, 80, 'House Departure Airport'),
            'hbls[].destination_name' => new FieldDefinition('hbls[].destination_name', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, null, 'airport_name', null, 80, 'House Destination Airport'),
            'hbls[].incoterms_id' => new FieldDefinition('hbls[].incoterms_id', 'string', FieldDefinition::SCOPE_HOUSE, FieldDefinition::POLICY_EXTRACTABLE, FieldDefinition::OVERWRITE_ONLY_IF_EMPTY, null, 'EXW,FCA,CPT,CIP,DAP,DPU,DDP,FAS,FOB,CFR,CIF', 'uppercase', 'incoterm_valid', 80, 'House Incoterm'),
        ];

        return array_merge($base, $airExportAdditions);
    }
}
