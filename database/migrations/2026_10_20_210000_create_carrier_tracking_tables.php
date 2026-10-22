<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Carrier API Configuration table
        if (!Schema::hasTable('carrier_api_configs')) {
            Schema::create('carrier_api_configs', function (Blueprint $table) {
                $table->id();
                $table->string('carrier_code')->unique(); // hapag_lloyd, maersk, dcsa
                $table->string('name');
                $table->string('base_url')->nullable();
                $table->text('client_id')->nullable();
                $table->text('client_secret')->nullable();
                $table->text('api_key')->nullable(); // consumer key or subscription key
                $table->string('environment')->default('sandbox'); // sandbox, production
                $table->boolean('is_active')->default(true);
                $table->boolean('enable_simulation')->default(true); // fallback simulation mode
                $table->json('settings')->nullable();
                $table->timestamps();
            });

            // Seed default carrier configurations
            DB::table('carrier_api_configs')->insert([
                [
                    'carrier_code' => 'hapag_lloyd',
                    'name' => 'Hapag-Lloyd (DCSA v2/v3)',
                    'base_url' => 'https://api.hlag.com',
                    'client_id' => null,
                    'client_secret' => null,
                    'api_key' => null,
                    'environment' => 'sandbox',
                    'is_active' => true,
                    'enable_simulation' => true,
                    'settings' => json_encode([
                        'token_url' => 'https://api.hlag.com/oauth2/token',
                        'scope' => 'read:events',
                        'api_version' => 'v2'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'carrier_code' => 'maersk',
                    'name' => 'Maersk (DCSA Track & Trace)',
                    'base_url' => 'https://api.maersk.com',
                    'client_id' => null,
                    'client_secret' => null,
                    'api_key' => null, // consumer key
                    'environment' => 'sandbox',
                    'is_active' => true,
                    'enable_simulation' => true,
                    'settings' => json_encode([
                        'token_url' => 'https://api.maersk.com/oauth2/access_token',
                        'api_version' => 'v2'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'carrier_code' => 'dcsa',
                    'name' => 'Generic DCSA Gateway',
                    'base_url' => 'https://api.dcsa.org',
                    'client_id' => null,
                    'client_secret' => null,
                    'api_key' => null,
                    'environment' => 'sandbox',
                    'is_active' => true,
                    'enable_simulation' => true,
                    'settings' => json_encode([
                        'api_version' => 'v2'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Shipment Trackings Master table
        if (!Schema::hasTable('shipment_trackings')) {
            Schema::create('shipment_trackings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->nullableMorphs('trackable'); // OceanImport, OceanExport, TruckShipment, etc.
                $table->string('carrier_code')->default('dcsa')->index();
                $table->string('tracking_type')->default('container'); // container, mbl, booking
                $table->string('tracking_number')->index();
                $table->string('current_status')->default('booked')->index(); // booked, gate_in, loaded, in_transit, discharged, gate_out, delivered
                $table->string('status_description')->nullable();
                $table->integer('progress_percentage')->default(10);
                
                // Route info
                $table->string('origin_locode')->nullable();
                $table->string('origin_name')->nullable();
                $table->string('destination_locode')->nullable();
                $table->string('destination_name')->nullable();
                $table->string('vessel_name')->nullable();
                $table->string('voyage_no')->nullable();
                
                // Milestones dates
                $table->timestamp('etd')->nullable();
                $table->timestamp('atd')->nullable();
                $table->timestamp('eta')->nullable();
                $table->timestamp('ata')->nullable();
                $table->timestamp('last_polled_at')->nullable();
                
                $table->json('raw_payload')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tracking Events table (DCSA-compliant)
        if (!Schema::hasTable('tracking_events')) {
            Schema::create('tracking_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('shipment_tracking_id')->index();
                $table->string('event_id')->nullable()->index();
                $table->string('event_type')->default('EQUIPMENT'); // SHIPMENT, TRANSPORT, EQUIPMENT
                $table->string('event_classifier')->default('ACT'); // ACT, EST, PLN, REQ
                $table->string('event_code')->nullable(); // LOAD, DISC, GTIN, GTOUT, DEPA, ARRI
                $table->timestamp('event_date_time')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location_name')->nullable();
                $table->string('location_locode')->nullable();
                $table->string('facility_name')->nullable();
                $table->string('vessel_name')->nullable();
                $table->string('voyage_no')->nullable();
                $table->string('equipment_reference')->nullable()->index();
                $table->boolean('is_current')->default(false);
                $table->boolean('is_manual')->default(false);
                $table->timestamps();

                $table->foreign('shipment_tracking_id')
                    ->references('id')
                    ->on('shipment_trackings')
                    ->onDelete('cascade');
            });
        }

        // 4. Carrier API Logs table (Audit & monitoring)
        if (!Schema::hasTable('carrier_api_logs')) {
            Schema::create('carrier_api_logs', function (Blueprint $table) {
                $table->id();
                $table->string('carrier_code')->index();
                $table->string('endpoint');
                $table->string('method', 10)->default('GET');
                $table->string('tracking_number')->nullable()->index();
                $table->json('request_payload')->nullable();
                $table->integer('response_status')->default(200);
                $table->json('response_payload')->nullable();
                $table->integer('duration_ms')->default(0);
                $table->text('error_message')->nullable();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('shipment_trackings');
        Schema::dropIfExists('carrier_api_configs');
        Schema::dropIfExists('carrier_api_logs');
    }
};

