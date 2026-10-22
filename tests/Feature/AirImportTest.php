<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AirImport;
use App\Models\AirImportHbl;
use App\Models\Office;
use App\Models\Port;
use App\Models\TradePartner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AirImportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_air_import_all_fields_persist_on_create_and_update()
    {
        $user = User::first() ?? User::factory()->create();
        $office = Office::first() ?? Office::create(['code' => 'TEST', 'name' => 'Test Office', 'is_active' => true]);
        $port = Port::first() ?? Port::create(['code' => 'NYC', 'name' => 'New York', 'country_id' => 1]);
        $agent = TradePartner::first() ?? TradePartner::create(['name' => 'Test Agent', 'code' => 'TA01']);

        $ts = time();
        $fileNo = 'AI-TEST-' . $ts;
        $mawbNo = 'MAWB-TEST-' . $ts;
        $hawbNo = 'HAWB-TEST-' . $ts;

        $storePayload = [
            'file_no' => $fileNo,
            'mawb_no' => $mawbNo,
            'office_id' => $office->id,
            'awb_type' => 'NORMAL',
            'post_date' => '2026-10-21',
            'oversea_agent_id' => $agent->id,
            'freight_term' => 'PREPAID',
            'cargo_type' => 'GENERAL CARGO',
            'available_date' => '2026-10-25',
            'last_free_day' => '2026-10-28',
            'display_unit' => 'BOTH',
            'gross_weight_kg' => 1200.50,
            'gross_weight_lb' => 2646.65,
            'chargeable_weight_kg' => 1250.00,
            'chargeable_weight_lb' => 2755.78,
            'volume_weight_kg' => 1000.00,
            'volume_cbm' => 6.00,
            'is_direct_master' => 1,
            'dm_customer_id' => $agent->id,
            'dm_sales_type' => 'NOMINATED',
            'ams_no' => 'AMS-' . $ts,
            'isf_no' => 'ISF-' . $ts,
            'cy_cfs_loc' => 'CFS-LOC-1',
            'entry_no' => 'ENTRY-1234',
            'ror' => 1,
            'door_delivered_date' => '2026-10-27',
            'routes' => [
                [
                    'airport_id' => $port->id,
                    'flight_no' => 'AI888',
                    'eta' => '2026-10-22 10:00',
                    'etd' => '2026-10-21 14:00',
                    'carrier_id' => $agent->id,
                ]
            ],
            'hbls' => [
                [
                    'hawb_no' => $hawbNo,
                    'customer_id' => $agent->id,
                    'display_unit' => 'KG_CBM',
                    'available_date' => '2026-10-25',
                    'last_free_day' => '2026-10-28',
                    'pkg_qty' => 50,
                    'gross_weight_kg' => 500.00,
                    'volume_cbm' => 2.50,
                    'is_frt_released' => 1,
                    'frt_released_date' => '2026-10-23',
                    'entry_no' => 'ENT-555',
                    'mark_text' => 'MARK TEXT HAWB 1',
                    'description_text' => 'ELECTRONICS GOODS',
                    'hbl_remark' => 'FRAGILE SHIPMENT',
                    'sub_hawbs' => json_encode([
                        ['no' => 'SH-01', 'description' => 'BOX A', 'pcs' => 25, 'unit' => 'CARTON(S)', 'amount' => 1000]
                    ]),
                    'commodities' => json_encode([
                        ['description' => 'LAPTOPS', 'po_no' => 'PO-01']
                    ])
                ]
            ]
        ];

        // 1. POST /air-import
        $response = $this->actingAs($user)->post(route('air-import.store'), $storePayload);
        $response->assertStatus(302);

        $shipment = AirImport::where('file_no', $fileNo)->with('hbls')->first();
        $this->assertNotNull($shipment, 'Shipment was not found in DB after store.');
        $this->assertEquals($mawbNo, $shipment->mawb_no);
        $this->assertEquals($agent->id, $shipment->oversea_agent_id);
        $this->assertEquals('PREPAID', $shipment->freight_term);
        $this->assertEquals('GENERAL CARGO', $shipment->cargo_type);
        $this->assertEquals('2026-10-25', $shipment->available_date->format('Y-m-d'));
        $this->assertEquals('2026-10-28', $shipment->last_free_day->format('Y-m-d'));
        $this->assertEquals('BOTH', $shipment->display_unit);
        $this->assertEquals(1200.50, (float)$shipment->gross_weight_kg);
        $this->assertEquals(6.00, (float)$shipment->volume_cbm);

        // HBL assertion
        $this->assertCount(1, $shipment->hbls);
        $hbl = $shipment->hbls->first();
        $this->assertEquals($hawbNo, $hbl->hawb_no);
        $this->assertEquals('KG_CBM', $hbl->display_unit);
        $this->assertEquals('2026-10-25', $hbl->available_date->format('Y-m-d'));
        $this->assertEquals('2026-10-28', $hbl->last_free_day->format('Y-m-d'));
        $this->assertEquals('ELECTRONICS GOODS', $hbl->description_text);
        $this->assertEquals('MARK TEXT HAWB 1', $hbl->mark_text);
        $this->assertEquals('FRAGILE SHIPMENT', $hbl->hbl_remark);
        $this->assertCount(1, $hbl->sub_hawbs);
        $this->assertCount(1, $hbl->commodities);

        // 2. GET /air-import/{id}/edit
        $editResponse = $this->actingAs($user)->get(route('air-import.edit', $shipment->id));
        $editResponse->assertStatus(200);

        // 3. PUT /air-import/{id}
        $updatePayload = $storePayload;
        $updatePayload['mawb_no'] = $mawbNo . '-UPD';
        $updatePayload['freight_term'] = 'COLLECT';
        $updatePayload['cargo_type'] = 'PERISHABLE';
        $updatePayload['available_date'] = '2026-10-29';
        $updatePayload['last_free_day'] = '2026-11-02';
        $updatePayload['display_unit'] = 'LB_CFT';
        $updatePayload['gross_weight_kg'] = 2200.00;
        $updatePayload['hbls'][0]['id'] = $hbl->id;
        $updatePayload['hbls'][0]['display_unit'] = 'LB_CFT';
        $updatePayload['hbls'][0]['available_date'] = '2026-10-29';
        $updatePayload['hbls'][0]['last_free_day'] = '2026-11-02';
        $updatePayload['hbls'][0]['description_text'] = 'UPDATED ELECTRONICS GOODS';

        $updateResponse = $this->actingAs($user)->put(route('air-import.update', $shipment->id), $updatePayload);
        $updateResponse->assertStatus(302);

        $shipment->refresh();
        $hbl->refresh();

        $this->assertEquals($mawbNo . '-UPD', $shipment->mawb_no);
        $this->assertEquals('COLLECT', $shipment->freight_term);
        $this->assertEquals('PERISHABLE', $shipment->cargo_type);
        $this->assertEquals('2026-10-29', $shipment->available_date->format('Y-m-d'));
        $this->assertEquals('2026-11-02', $shipment->last_free_day->format('Y-m-d'));
        $this->assertEquals('LB_CFT', $shipment->display_unit);
        $this->assertEquals(2200.00, (float)$shipment->gross_weight_kg);

        $this->assertEquals('LB_CFT', $hbl->display_unit);
        $this->assertEquals('2026-10-29', $hbl->available_date->format('Y-m-d'));
        $this->assertEquals('2026-11-02', $hbl->last_free_day->format('Y-m-d'));
        $this->assertEquals('UPDATED ELECTRONICS GOODS', $hbl->description_text);
    }
}

