<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CarrierApiConfig;
use App\Models\CarrierApiLog;
use App\Services\Tracking\TrackingManager;
use Illuminate\Http\Request;
use Throwable;

class TrackingSettingsController extends Controller
{
    /**
     * Show Super Admin Carrier Tracking settings dashboard.
     */
    public function index()
    {
        $carriers = CarrierApiConfig::all()->keyBy('carrier_code');

        // Ensure default records exist (ShipmentBeat Unified Multi-Carrier Gateway only)
        $defaults = ['shipmentbeat'];
        foreach ($defaults as $code) {
            if (!$carriers->has($code)) {
                $created = CarrierApiConfig::create([
                    'carrier_code' => 'shipmentbeat',
                    'name' => 'ShipmentBeat (Ocean Container Visibility)',
                    'base_url' => 'https://api.shipmentbeat.com',
                    'environment' => 'production',
                    'is_active' => true,
                    'enable_simulation' => false,
                ]);
                $carriers->put($code, $created);
            }
        }

        $logs = CarrierApiLog::with(['user', 'company'])
            ->latest()
            ->paginate(15);

        return view('super-admin.integrations.carrier-tracking', compact('carriers', 'logs'));
    }

    /**
     * Update carrier credentials and configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'carrier_code' => 'required|string|in:shipmentbeat',
            'base_url' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'api_key' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
            'is_active' => 'nullable|boolean',
            'enable_simulation' => 'nullable|boolean',
        ]);

        $carrier = CarrierApiConfig::where('carrier_code', $request->carrier_code)->firstOrFail();

        $apiKey = $request->filled('api_key') 
            ? trim($request->api_key) 
            : ($carrier->api_key ?: env('SHIPMENTBEAT_API_KEY'));

        $carrier->update([
            'base_url' => $request->base_url ?: 'https://api.shipmentbeat.com',
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'api_key' => $apiKey,
            'environment' => $request->environment,
            'is_active' => $request->boolean('is_active', true),
            'enable_simulation' => $request->boolean('enable_simulation', false),
        ]);

        return back()->with('success', "{$carrier->name} configuration saved successfully.");
    }

    /**
     * Test connection to a carrier API endpoint.
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'carrier_code' => 'required|string|in:shipmentbeat',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|string',
        ]);

        try {
            if ($request->filled('api_key')) {
                $customConfig = clone CarrierApiConfig::where('carrier_code', $request->carrier_code)->firstOrFail();
                $customConfig->api_key = trim($request->api_key);
                if ($request->filled('base_url')) {
                    $customConfig->base_url = trim($request->base_url);
                }
                $driver = new \App\Services\Tracking\Drivers\ShipmentBeatDriver($customConfig);
            } else {
                $driver = TrackingManager::driver($request->carrier_code);
            }
            $result = $driver->testConnection();

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => 0,
                'details' => ['error' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Clear carrier API logs.
     */
    public function clearLogs()
    {
        CarrierApiLog::truncate();
        return back()->with('success', 'Carrier API logs cleared successfully.');
    }
}

