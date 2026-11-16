<?php

namespace Database\Seeders;

use App\Models\OceanExport;
use App\Models\OceanExportContainer;
use App\Models\OceanImport;
use App\Models\OceanImportContainer;
use App\Models\ShipmentTracking;
use App\Models\TrackingEvent;
use App\Models\TradePartner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $shawon = User::where('email', 'shawon@fms.com')->first();
        $companyId = $shawon ? $shawon->company_id : 46;
        $adminId = $shawon ? $shawon->id : 74;

        $this->command?->info("Seeding customers and shipments for Company ID: {$companyId} (Shawon)...");

        // =========================================================================
        // CUSTOMER 1: Apex Global Logistics Ltd
        // =========================================================================
        $customer1 = TradePartner::updateOrCreate(
            ['company_id' => $companyId, 'code' => 'CUST-APEX'],
            [
                'type' => 'CLIENT',
                'name' => 'Apex Global Logistics Ltd',
                'print_name' => 'Apex Global Logistics Ltd',
                'alias' => 'Apex Logistics',
                'email' => 'customer1@freitx.com',
                'phone' => '+1 (415) 890-2311',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip_code' => '94105',
                'local_address' => '500 Howard Street, San Francisco, CA 94105',
                'status' => 'BUSINESS',
                'payment_type' => 'Prepaid',
                'country_id' => 233,
            ]
        );

        $user1 = User::updateOrCreate(
            ['email' => 'customer1@freitx.com'],
            [
                'company_id' => $companyId,
                'user_id' => 'USR-APEX-01',
                'first_name' => 'Apex',
                'last_name' => 'Customer',
                'name' => 'Apex Global Logistics',
                'role' => 'client',
                'trade_partner_id' => $customer1->id,
                'status' => 'Enable',
                'password' => Hash::make('password'),
            ]
        );

        // =========================================================================
        // CUSTOMER 2: BlueWave Maritime Traders
        // =========================================================================
        $customer2 = TradePartner::updateOrCreate(
            ['company_id' => $companyId, 'code' => 'CUST-BWAVE'],
            [
                'type' => 'CLIENT',
                'name' => 'BlueWave Maritime Traders',
                'print_name' => 'BlueWave Maritime Traders',
                'alias' => 'BlueWave Traders',
                'email' => 'customer2@freitx.com',
                'phone' => '+1 (212) 770-9410',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10271',
                'local_address' => '120 Broadway, Suite 1500, New York, NY 10271',
                'status' => 'BUSINESS',
                'payment_type' => 'Prepaid',
                'country_id' => 233,
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'customer2@freitx.com'],
            [
                'company_id' => $companyId,
                'user_id' => 'USR-BWAVE-01',
                'first_name' => 'BlueWave',
                'last_name' => 'Customer',
                'name' => 'BlueWave Maritime Traders',
                'role' => 'client',
                'trade_partner_id' => $customer2->id,
                'status' => 'Enable',
                'password' => Hash::make('password'),
            ]
        );

        // =========================================================================
        // SHIPMENTS FOR CUSTOMER 1 (Apex Global Logistics)
        // =========================================================================
        // 1. Ocean Import: Singapore (SGSIN) -> Los Angeles (USLAX)
        $oi1 = OceanImport::updateOrCreate(
            ['company_id' => $companyId, 'file_no' => 'OI-FX-2601'],
            [
                'mbl_no' => 'MEDU91823746',
                'sub_bl_no' => 'BKG-APEX-01',
                'post_date' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'office_id' => 1,
                'op_id' => $adminId,
                'carrier_id' => 248, // MSC
                'dm_customer_id' => $customer1->id,
                'dm_consignee_id' => $customer1->id,
                'pol_id' => 4, // SGSIN (Singapore)
                'pod_id' => 1, // USLAX (Los Angeles)
                'vessel_id' => 2, // EVER GIVEN
                'voyage' => '049W',
                'etd' => Carbon::now()->subDays(6),
                'eta' => Carbon::now()->addDays(12),
                'bl_type' => 'NORMAL',
                'cargo_type' => 'GENERAL CARGO',
                'ship_mode' => 'FCL',
                'freight_term' => 'Prepaid',
                'description' => 'High-Tech Solar Inverters & Telecom Hardware - 2x40HC',
                'internal_remark' => 'Client: Apex Global Logistics (Shawon FMS Company)',
            ]
        );

        OceanImportContainer::where('ocean_import_id', $oi1->id)->delete();
        OceanImportContainer::create([
            'company_id' => $companyId,
            'ocean_import_id' => $oi1->id,
            'container_no' => 'CRSU9148090',
            'container_type_id' => 3, // 40HC
            'seal_no' => 'SL-884102',
            'pkg_qty' => 480,
            'pkg_unit_id' => 2, // CTN
            'weight_kg' => 19500.00,
            'measure_cbm' => 58.4,
            'gate_in_date' => Carbon::now()->subDays(7),
        ]);
        OceanImportContainer::create([
            'company_id' => $companyId,
            'ocean_import_id' => $oi1->id,
            'container_no' => 'MSCU4918291',
            'container_type_id' => 3, // 40HC
            'seal_no' => 'SL-884103',
            'pkg_qty' => 420,
            'pkg_unit_id' => 2, // CTN
            'weight_kg' => 18200.00,
            'measure_cbm' => 54.0,
            'gate_in_date' => Carbon::now()->subDays(7),
        ]);

        // 2. Ocean Export: Chattogram (BDCGP) -> Singapore (SGSIN)
        $solMalaysiaVessel = \App\Models\Vessel::firstOrCreate(
            ['name' => 'SOL MALAYSIA'],
            ['flag' => 'SG']
        );

        $oe1 = OceanExport::updateOrCreate(
            ['company_id' => $companyId, 'file_no' => 'OE-FX-2602'],
            [
                'mbl_no' => 'YMLU60836952',
                'booking_no' => 'BKG-APEX-02',
                'post_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'office_id' => 1,
                'op_id' => $adminId,
                'carrier_id' => 2, // Yang Ming
                'dm_customer_id' => $customer1->id,
                'dm_shipper_id' => $customer1->id,
                'pol_id' => 19851, // BDCGP (Chittagong / Chattogram Port)
                'pod_id' => 4, // SGSIN (Singapore Port)
                'vessel_id' => $solMalaysiaVessel->id,
                'voyage' => '112E',
                'etd' => '2026-11-14',
                'eta' => '2026-11-19',
                'bl_type' => 'NORMAL',
                'cargo_type' => 'GENERAL CARGO',
                'ship_mode' => 'FCL',
                'freight_term' => 'Prepaid',
                'description' => 'Precision Machined Auto Parts & Assembly Line Spares - 1x40HC',
                'internal_remark' => 'Client: Apex Global Logistics (Shawon FMS Company)',
            ]
        );

        OceanExportContainer::where('ocean_export_id', $oe1->id)->delete();
        OceanExportContainer::create([
            'company_id' => $companyId,
            'ocean_export_id' => $oe1->id,
            'container_no' => 'YMMU6083695',
            'container_type_id' => 3, // 40HC
            'seal_no' => 'YM-991204',
            'pkg_qty' => 290,
            'pkg_unit_id' => 3, // PLT
            'weight_kg' => 21400.00,
            'measure_cbm' => 61.2,
            'gate_in_date' => Carbon::now()->subDays(4),
        ]);

        // =========================================================================
        // SHIPMENTS FOR CUSTOMER 2 (BlueWave Maritime Traders)
        // =========================================================================
        // 1. Ocean Import: Savannah (USSAV) -> New York (USNYC)
        $oi2 = OceanImport::updateOrCreate(
            ['company_id' => $companyId, 'file_no' => 'OI-FX-2603'],
            [
                'mbl_no' => 'COSU63219045',
                'sub_bl_no' => 'BKG-BWAVE-01',
                'post_date' => Carbon::now()->subDays(6)->format('Y-m-d'),
                'office_id' => 1,
                'op_id' => $adminId,
                'carrier_id' => 248,
                'dm_customer_id' => $customer2->id,
                'dm_consignee_id' => $customer2->id,
                'pol_id' => 11, // USSAV (Savannah)
                'pod_id' => 3,  // USNYC (New York / New Jersey)
                'vessel_id' => 5, // COSCO SHIPPING UNIVERSE
                'voyage' => '018N',
                'etd' => Carbon::now()->subDays(4),
                'eta' => Carbon::now()->addDays(8),
                'bl_type' => 'NORMAL',
                'cargo_type' => 'GENERAL CARGO',
                'ship_mode' => 'FCL',
                'freight_term' => 'Collect',
                'description' => 'Organic Finished Textiles & Fashion Apparel - 2 Containers',
                'internal_remark' => 'Client: BlueWave Maritime (Shawon FMS Company)',
            ]
        );

        OceanImportContainer::where('ocean_import_id', $oi2->id)->delete();
        OceanImportContainer::create([
            'company_id' => $companyId,
            'ocean_import_id' => $oi2->id,
            'container_no' => 'COSU6321904',
            'container_type_id' => 3, // 40HC
            'seal_no' => 'COS-81920',
            'pkg_qty' => 520,
            'pkg_unit_id' => 2, // CTN
            'weight_kg' => 17800.00,
            'measure_cbm' => 56.5,
            'gate_in_date' => Carbon::now()->subDays(5),
        ]);
        OceanImportContainer::create([
            'company_id' => $companyId,
            'ocean_import_id' => $oi2->id,
            'container_no' => 'TLLU8291045',
            'container_type_id' => 1, // 20GP
            'seal_no' => 'TL-30192',
            'pkg_qty' => 210,
            'pkg_unit_id' => 2, // CTN
            'weight_kg' => 10400.00,
            'measure_cbm' => 28.0,
            'gate_in_date' => Carbon::now()->subDays(5),
        ]);

        // 2. Ocean Export: Long Beach (USLGB) -> Singapore (SGSIN)
        $oe2 = OceanExport::updateOrCreate(
            ['company_id' => $companyId, 'file_no' => 'OE-FX-2604'],
            [
                'mbl_no' => 'MAEU91240199',
                'booking_no' => 'BKG-BWAVE-02',
                'post_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'office_id' => 1,
                'op_id' => $adminId,
                'carrier_id' => 273, // Maersk
                'dm_customer_id' => $customer2->id,
                'dm_shipper_id' => $customer2->id,
                'pol_id' => 2, // USLGB (Long Beach)
                'pod_id' => 4, // SGSIN (Singapore)
                'vessel_id' => 2, // EVER GIVEN
                'voyage' => '112W',
                'etd' => Carbon::now()->subDays(1),
                'eta' => Carbon::now()->addDays(20),
                'bl_type' => 'NORMAL',
                'cargo_type' => 'GENERAL CARGO',
                'ship_mode' => 'FCL',
                'freight_term' => 'Prepaid',
                'description' => 'Premium California Almonds & Food Ingredients - 1x40HC',
                'internal_remark' => 'Client: BlueWave Maritime (Shawon FMS Company)',
            ]
        );

        OceanExportContainer::where('ocean_export_id', $oe2->id)->delete();
        OceanExportContainer::create([
            'company_id' => $companyId,
            'ocean_export_id' => $oe2->id,
            'container_no' => 'MAEU9124019',
            'container_type_id' => 3, // 40HC
            'seal_no' => 'MSK-77192',
            'pkg_qty' => 600,
            'pkg_unit_id' => 5, // BOX
            'weight_kg' => 24200.00,
            'measure_cbm' => 65.0,
            'gate_in_date' => Carbon::now()->subDays(2),
        ]);

        // =========================================================================
        // SEED COMPLETE SHIPMENT TRACKING & EVENTS FOR ALL CONTAINERS
        // =========================================================================
        // Remove duplicate container assignments from older unassigned shipments
        OceanExportContainer::where('container_no', 'YMMU6083695')->where('ocean_export_id', '!=', $oe1->id)->delete();

        $trackingDefinitions = [
            [
                'number' => 'CRSU9148090',
                'trackable_type' => OceanImport::class,
                'trackable_id' => $oi1->id,
                'carrier' => 'MSCU',
                'carrier_name' => 'MSC',
                'origin_locode' => 'SGSIN',
                'origin_name' => 'Singapore Port, Singapore',
                'dest_locode' => 'USLAX',
                'dest_name' => 'Los Angeles Port, USA',
                'vessel' => 'EVER GIVEN',
                'voyage' => '049W',
                'status' => 'in_transit',
                'progress' => 45,
                'etd' => Carbon::now()->subDays(6),
                'eta' => Carbon::now()->addDays(12),
                'events' => [
                    [
                        'code' => 'BOOKED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(8),
                        'title' => 'Booking Confirmed (MSC Network)',
                        'desc' => 'Carrier confirmed 40HC space reservation at Singapore PSA terminal.',
                        'loc' => 'Singapore Terminal',
                    ],
                    [
                        'code' => 'GATE_IN',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(7),
                        'title' => 'Container Gate-In at Terminal',
                        'desc' => 'Full container received at PSA Singapore Terminal gate #4.',
                        'loc' => 'PSA Singapore Terminal',
                    ],
                    [
                        'code' => 'LOADED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(6)->addHours(4),
                        'title' => 'Vessel Loaded',
                        'desc' => 'Container stowed on vessel EVER GIVEN bay 42.',
                        'loc' => 'PSA Berth 12, Singapore',
                    ],
                    [
                        'code' => 'IN_TRANSIT',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(6),
                        'title' => 'Vessel Departed Port of Loading',
                        'desc' => 'Vessel departed Singapore port heading across trans-Pacific corridor to Los Angeles.',
                        'loc' => 'South China Sea / Pacific Ocean',
                    ],
                    [
                        'code' => 'DISCHARGED',
                        'classifier' => 'EST',
                        'time' => Carbon::now()->addDays(12),
                        'title' => 'Estimated Vessel Arrival & Discharge',
                        'desc' => 'Berthing scheduled at APM Terminals Pier 400, Los Angeles.',
                        'loc' => 'Los Angeles Port, USA',
                    ],
                ],
            ],
            [
                'number' => 'MSCU4918291',
                'trackable_type' => OceanImport::class,
                'trackable_id' => $oi1->id,
                'carrier' => 'MSCU',
                'carrier_name' => 'MSC',
                'origin_locode' => 'SGSIN',
                'origin_name' => 'Singapore Port, Singapore',
                'dest_locode' => 'USLAX',
                'dest_name' => 'Los Angeles Port, USA',
                'vessel' => 'EVER GIVEN',
                'voyage' => '049W',
                'status' => 'loaded',
                'progress' => 25,
                'etd' => Carbon::now()->subDays(6),
                'eta' => Carbon::now()->addDays(12),
                'events' => [
                    [
                        'code' => 'BOOKED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(8),
                        'title' => 'Booking Confirmed',
                        'desc' => 'FCL allocation confirmed by MSC.',
                        'loc' => 'Singapore',
                    ],
                    [
                        'code' => 'LOADED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(6)->addHours(2),
                        'title' => 'Loaded onto EVER GIVEN',
                        'desc' => 'Crane gantry loaded container onto deck.',
                        'loc' => 'PSA Singapore',
                    ],
                ],
            ],
            [
                'number' => 'YMMU6083695',
                'trackable_type' => OceanExport::class,
                'trackable_id' => $oe1->id,
                'carrier' => 'YMLU',
                'carrier_name' => 'Yang Ming',
                'origin_locode' => 'BDCGP',
                'origin_name' => 'Chattogram, Bangladesh',
                'dest_locode' => 'SGSIN',
                'dest_name' => 'Singapore, Singapore',
                'vessel' => 'SOL MALAYSIA',
                'voyage' => '112E',
                'status' => 'loaded',
                'progress' => 35,
                'etd' => '2026-11-14 18:00:00',
                'eta' => '2026-11-19 12:00:00',
                'events' => [
                    [
                        'code' => 'GATE_OUT',
                        'classifier' => 'ACT',
                        'time' => '2026-11-09 10:00:00',
                        'title' => 'Gate out empty by Truck',
                        'desc' => 'Empty container picked up from Incontrade Limited depot for packing.',
                        'loc' => 'Chattogram, Bangladesh (INCONTRADE LIMITED)',
                    ],
                    [
                        'code' => 'GATE_IN',
                        'classifier' => 'ACT',
                        'time' => '2026-11-14 08:30:00',
                        'title' => 'Gate in full by Truck',
                        'desc' => 'Full export container gated in at Chittagong Port terminal storage yard.',
                        'loc' => 'Chattogram, Bangladesh (CHITTAGONG PORT)',
                    ],
                    [
                        'code' => 'LOADED',
                        'classifier' => 'ACT',
                        'time' => '2026-11-14 14:15:00',
                        'title' => 'Loaded on Vessel SOL MALAYSIA',
                        'desc' => 'Container lifted and stowed aboard feeder vessel SOL MALAYSIA.',
                        'loc' => 'Chattogram, Bangladesh (CHITTAGONG PORT)',
                    ],
                    [
                        'code' => 'DEPARTED',
                        'classifier' => 'ACT',
                        'time' => '2026-11-14 18:00:00',
                        'title' => 'Departed by Vessel SOL MALAYSIA',
                        'desc' => 'Vessel departed Chittagong Port berth en route to Singapore Port.',
                        'loc' => 'Chattogram, Bangladesh (CHITTAGONG PORT)',
                    ],
                    [
                        'code' => 'ARRIVED',
                        'classifier' => 'EST',
                        'time' => '2026-11-19 12:00:00',
                        'title' => 'Arrived by Vessel SOL MALAYSIA',
                        'desc' => 'Estimated vessel arrival at PSA Singapore container terminal.',
                        'loc' => 'Singapore, Singapore',
                    ],
                ],
            ],
            [
                'number' => 'COSU6321904',
                'trackable_type' => OceanImport::class,
                'trackable_id' => $oi2->id,
                'carrier' => 'COSU',
                'carrier_name' => 'COSCO',
                'origin_locode' => 'USSAV',
                'origin_name' => 'Savannah Port, USA',
                'dest_locode' => 'USNYC',
                'dest_name' => 'New York / New Jersey Port, USA',
                'vessel' => 'COSCO SHIPPING UNIVERSE',
                'voyage' => '018N',
                'status' => 'in_transit',
                'progress' => 55,
                'etd' => Carbon::now()->subDays(4),
                'eta' => Carbon::now()->addDays(8),
                'events' => [
                    [
                        'code' => 'BOOKED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(6),
                        'title' => 'Booking Accepted by COSCO',
                        'desc' => 'Coastal service booking accepted.',
                        'loc' => 'Savannah Garden City Terminal',
                    ],
                    [
                        'code' => 'IN_TRANSIT',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(4),
                        'title' => 'Vessel Underway to New York',
                        'desc' => 'COSCO SHIPPING UNIVERSE en route off Atlantic Coast.',
                        'loc' => 'US Atlantic Seaboard',
                    ],
                ],
            ],
            [
                'number' => 'TLLU8291045',
                'trackable_type' => OceanImport::class,
                'trackable_id' => $oi2->id,
                'carrier' => 'COSU',
                'carrier_name' => 'COSCO',
                'origin_locode' => 'USSAV',
                'origin_name' => 'Savannah Port, USA',
                'dest_locode' => 'USNYC',
                'dest_name' => 'New York / New Jersey Port, USA',
                'vessel' => 'COSCO SHIPPING UNIVERSE',
                'voyage' => '018N',
                'status' => 'gate_in',
                'progress' => 15,
                'etd' => Carbon::now()->subDays(4),
                'eta' => Carbon::now()->addDays(8),
                'events' => [
                    [
                        'code' => 'GATE_IN',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(5),
                        'title' => 'Gate-In at Savannah Terminal',
                        'desc' => 'Container received into terminal storage yard.',
                        'loc' => 'Savannah Port, USA',
                    ],
                ],
            ],
            [
                'number' => 'MAEU9124019',
                'trackable_type' => OceanExport::class,
                'trackable_id' => $oe2->id,
                'carrier' => 'MAEU',
                'carrier_name' => 'Maersk',
                'origin_locode' => 'USLGB',
                'origin_name' => 'Long Beach Port, USA',
                'dest_locode' => 'SGSIN',
                'dest_name' => 'Singapore Port, Singapore',
                'vessel' => 'EVER GIVEN',
                'voyage' => '112W',
                'status' => 'booked',
                'progress' => 5,
                'etd' => Carbon::now()->subDays(1),
                'eta' => Carbon::now()->addDays(20),
                'events' => [
                    [
                        'code' => 'BOOKED',
                        'classifier' => 'ACT',
                        'time' => Carbon::now()->subDays(2),
                        'title' => 'Maersk Space Allocation Confirmed',
                        'desc' => 'Booking registered on Maersk Spot ocean network.',
                        'loc' => 'Long Beach Pier T',
                    ],
                ],
            ],
        ];

        foreach ($trackingDefinitions as $def) {
            $st = ShipmentTracking::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'tracking_number' => $def['number'],
                ],
                [
                    'trackable_type' => $def['trackable_type'],
                    'trackable_id' => $def['trackable_id'],
                    'carrier_code' => $def['carrier'],
                    'tracking_type' => 'container',
                    'current_status' => $def['status'],
                    'status_description' => "Carrier: {$def['carrier_name']} | Route: {$def['origin_name']} -> {$def['dest_name']}",
                    'progress_percentage' => $def['progress'],
                    'origin_locode' => $def['origin_locode'],
                    'origin_name' => $def['origin_name'],
                    'destination_locode' => $def['dest_locode'],
                    'destination_name' => $def['dest_name'],
                    'vessel_name' => $def['vessel'],
                    'voyage_no' => $def['voyage'],
                    'etd' => $def['etd'],
                    'eta' => $def['eta'],
                    'last_polled_at' => Carbon::now(),
                    'raw_payload' => [
                        'carrierCode' => $def['carrier'],
                        'carrierName' => $def['carrier_name'],
                        'trackingNumber' => $def['number'],
                        'status' => $def['status'],
                        'origin' => ['name' => $def['origin_name'], 'locode' => $def['origin_locode']],
                        'destination' => ['name' => $def['dest_name'], 'locode' => $def['dest_locode']],
                    ],
                ]
            );

            // Clean existing events for clean rerun
            TrackingEvent::where('shipment_tracking_id', $st->id)->delete();

            foreach ($def['events'] as $index => $evt) {
                TrackingEvent::create([
                    'shipment_tracking_id' => $st->id,
                    'event_id' => 'evt_' . strtolower($def['number']) . '_' . ($index + 1),
                    'event_type' => $evt['code'],
                    'event_classifier' => $evt['classifier'],
                    'event_code' => $evt['code'],
                    'event_date_time' => $evt['time'],
                    'title' => $evt['title'],
                    'description' => $evt['desc'],
                    'location_name' => $evt['loc'],
                    'location_locode' => $def['origin_locode'],
                    'vessel_name' => $def['vessel'],
                    'voyage_no' => $def['voyage'],
                    'is_current' => ($index === count($def['events']) - 1),
                    'is_manual' => false,
                ]);
            }
        }

        $this->command?->info("✓ Customer 1 created: Apex Global Logistics (customer1@freitx.com)");
        $this->command?->info("  - 1 Ocean Import (#OI-FX-2601) with containers: CRSU9148090, MSCU4918291");
        $this->command?->info("  - 1 Ocean Export (#OE-FX-2602) with container: YMMU6083695");
        $this->command?->info("✓ Customer 2 created: BlueWave Maritime Traders (customer2@freitx.com)");
        $this->command?->info("  - 1 Ocean Import (#OI-FX-2603) with containers: COSU6321904, TLLU8291045");
        $this->command?->info("  - 1 Ocean Export (#OE-FX-2604) with container: MAEU9124019");
        $this->command?->info("✓ All 6 containers seeded with live maritime coordinates, waypoints & milestone events.");
    }
}
