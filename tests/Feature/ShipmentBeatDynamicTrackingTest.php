<?php

namespace Tests\Feature;

use App\Models\CarrierApiConfig;
use App\Models\Company;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Services\Tracking\Drivers\ShipmentBeatDriver;
use App\Services\Tracking\TrackingManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentBeatDynamicTrackingTest extends TestCase
{
    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'freightX2.0',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');

        $this->user = User::first();
    }

    public function test_bic_code_resolution_for_all_major_carriers()
    {
        $this->assertEquals('YMLU', ShipmentBeatDriver::resolveCarrierCode('YMMU6083695'));
        $this->assertEquals('YMLU', ShipmentBeatDriver::resolveCarrierCode('YMLU1234567'));
        $this->assertEquals('MSCU', ShipmentBeatDriver::resolveCarrierCode('MEDU4387708'));
        $this->assertEquals('MAEU', ShipmentBeatDriver::resolveCarrierCode('MSKU9012345'));
        $this->assertEquals('HLCU', ShipmentBeatDriver::resolveCarrierCode('HLXU1234567'));
        $this->assertEquals('CMDU', ShipmentBeatDriver::resolveCarrierCode('CMAU1234567'));
        $this->assertEquals('COSU', ShipmentBeatDriver::resolveCarrierCode('COSU1234567'));
        $this->assertEquals('ONEY', ShipmentBeatDriver::resolveCarrierCode('ONEY1234567'));
        $this->assertEquals('EGLV', ShipmentBeatDriver::resolveCarrierCode('EGLV1234567'));
        $this->assertEquals('HDMU', ShipmentBeatDriver::resolveCarrierCode('HDMU1234567'));
        $this->assertEquals('ZIMU', ShipmentBeatDriver::resolveCarrierCode('ZIMU1234567'));
        $this->assertEquals('WHLC', ShipmentBeatDriver::resolveCarrierCode('WHLC1234567'));
        $this->assertEquals('12PD', ShipmentBeatDriver::resolveCarrierCode('SITU1234567'));
        $this->assertEquals('13DF', ShipmentBeatDriver::resolveCarrierCode('TSLU1234567'));
        $this->assertEquals('CULU', ShipmentBeatDriver::resolveCarrierCode('CULU1234567'));
        $this->assertEquals('MATS', ShipmentBeatDriver::resolveCarrierCode('MATS1234567'));
        $this->assertEquals('MSCU', ShipmentBeatDriver::resolveCarrierCode('CRSU9148090'));
        $this->assertEquals('MSCU', ShipmentBeatDriver::resolveCarrierCode('GESU1234567'));
        $this->assertEquals('MSCU', ShipmentBeatDriver::resolveCarrierCode('TEXU1234567'));
        $this->assertEquals('MSCU', ShipmentBeatDriver::resolveCarrierCode('TTNU1234567'));
    }

    public function test_yang_ming_container_tracking_creates_dynamic_record_when_pending()
    {
        Http::fake([
            'https://api.shipmentbeat.com/api/v1/shipments?trackingNumber=YMMU6083695*' => Http::response([
                'success' => true,
                'code' => 0,
                'data' => [
                    'items' => [
                        [
                            'id' => 'mock-ymlu-uuid-123',
                            'lifecycleStatus' => 'pending',
                            'trackingStatus' => 'not_started',
                            'carrierCode' => 'YMLU',
                            'carrierName' => 'YML',
                            'trackingNumber' => 'YMMU6083695',
                            'trackingNumberType' => 'BillOfLading',
                            'createdAt' => '2026-11-14 17:43:01',
                        ]
                    ]
                ]
            ], 200),
            'https://api.shipmentbeat.com/api/v1/shipments/mock-ymlu-uuid-123' => Http::response([
                'success' => true,
                'code' => 0,
                'data' => [
                    'id' => 'mock-ymlu-uuid-123',
                    'lifecycleStatus' => 'pending',
                    'trackingStatus' => 'not_started',
                    'carrierCode' => 'YMLU',
                    'carrierName' => 'YML',
                    'trackingNumber' => 'YMMU6083695',
                    'trackingNumberType' => 'BillOfLading',
                    'pol' => '',
                    'pod' => '',
                    'containers' => [],
                    'createdAt' => '2026-11-14 17:43:01',
                ]
            ], 200),
        ]);

        ShipmentTracking::where('tracking_number', 'YMMU6083695')->delete();
        $tracking = TrackingManager::syncTracking('YMMU6083695');

        $this->assertNotNull($tracking);
        $this->assertEquals('YMMU6083695', $tracking->tracking_number);
        $this->assertContains($tracking->current_status, ['booked', 'loaded']);
        $this->assertStringContainsString('YML', $tracking->status_description);
        $this->assertGreaterThanOrEqual(1, $tracking->events->count());
        $this->assertStringContainsString('Tracking Registered', $tracking->events->first()->title);
        $this->assertNotNull($tracking->map_data);
    }

    public function test_api_tracking_sync_endpoint_successfully_returns_live_data()
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in MySQL database.');
        }

        Http::fake([
            'https://api.shipmentbeat.com/api/v1/shipments?trackingNumber=YMMU6083695*' => Http::response([
                'success' => true,
                'code' => 0,
                'data' => [
                    'items' => [
                        [
                            'id' => 'mock-ymlu-uuid-456',
                            'lifecycleStatus' => 'active',
                            'trackingStatus' => 'loaded',
                            'carrierCode' => 'YMLU',
                            'carrierName' => 'Yang Ming',
                            'trackingNumber' => 'YMMU6083695',
                            'trackingNumberType' => 'ContainerNumber',
                            'pol' => 'BDCGP',
                            'pod' => 'SGSIN',
                            'origin' => ['city' => 'Chattogram', 'countryName' => 'Bangladesh'],
                            'destination' => ['city' => 'Singapore', 'countryName' => 'Singapore'],
                            'createdAt' => '2026-11-14 17:43:01',
                        ]
                    ]
                ]
            ], 200),
            'https://api.shipmentbeat.com/api/v1/shipments/mock-ymlu-uuid-456' => Http::response([
                'success' => true,
                'code' => 0,
                'data' => [
                    'id' => 'mock-ymlu-uuid-456',
                    'lifecycleStatus' => 'active',
                    'trackingStatus' => 'loaded',
                    'carrierCode' => 'YMLU',
                    'carrierName' => 'Yang Ming',
                    'trackingNumber' => 'YMMU6083695',
                    'pol' => 'BDCGP',
                    'pod' => 'SGSIN',
                    'origin' => ['city' => 'Chattogram', 'countryName' => 'Bangladesh'],
                    'destination' => ['city' => 'Singapore', 'countryName' => 'Singapore'],
                    'transportLegs' => [
                        ['vesselName' => 'SOL MALAYSIA', 'voyageNumber' => '112E']
                    ],
                    'containers' => [
                        [
                            'containerNumber' => 'YMMU6083695',
                            'locations' => [
                                [
                                    'locationName' => 'Chattogram, Bangladesh',
                                    'portCode' => 'BDCGP',
                                    'events' => [
                                        [
                                            'status' => 'LOADED',
                                            'eventDescription' => 'Loaded on Vessel SOL MALAYSIA',
                                            'actTime' => '2026-11-14 14:15:00',
                                            'vesselName' => 'SOL MALAYSIA',
                                            'voyageNumber' => '112E',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/tracking/sync', [
            'tracking_number' => 'YMMU6083695',
            'carrier_code' => 'YMLU',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'tracking' => [
                'tracking_number' => 'YMMU6083695',
                'current_status' => 'loaded',
                'origin_locode' => 'BDCGP',
                'destination_locode' => 'SGSIN',
                'vessel_name' => 'SOL MALAYSIA',
            ]
        ]);
    }
}
