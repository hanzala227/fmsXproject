<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\CarrierApiConfig;
use App\Models\CarrierApiLog;
use Illuminate\Http\Request;
use Throwable;

class ApiManagementController extends Controller
{
    /**
     * Show Super Admin Central API Keys & Webhooks Console
     */
    public function index(Request $request)
    {
        $shipmentBeat = CarrierApiConfig::where('carrier_code', 'shipmentbeat')->first();
        if (!$shipmentBeat) {
            $shipmentBeat = CarrierApiConfig::create([
                'carrier_code' => 'shipmentbeat',
                'name' => 'ShipmentBeat (Ocean Container Visibility)',
                'base_url' => 'https://api.shipmentbeat.com',
                'environment' => 'production',
                'is_active' => true,
                'enable_simulation' => false,
            ]);
        }

        $aiSetting = AiSetting::current();
        if (!$aiSetting) {
            $aiSetting = AiSetting::create([
                'api_type' => 'gemini',
                'model_name' => 'gemini-3.5-flash-lite',
                'api_key' => '',
            ]);
        }

        $logsQuery = CarrierApiLog::with(['user', 'company'])->latest();

        if ($carrier = $request->get('carrier')) {
            $logsQuery->where('carrier_code', $carrier);
        }

        if ($status = $request->get('status')) {
            if ($status === 'success') {
                $logsQuery->whereBetween('response_status', [200, 299]);
            } elseif ($status === 'error') {
                $logsQuery->where('response_status', '>=', 400);
            }
        }

        $logs = $logsQuery->paginate(15)->withQueryString();

        $kpis = [
            'total_calls' => CarrierApiLog::count(),
            'success_calls' => CarrierApiLog::whereBetween('response_status', [200, 299])->count(),
            'failed_calls' => CarrierApiLog::where('response_status', '>=', 400)->count(),
            'avg_latency' => round((float) (CarrierApiLog::avg('duration_ms') ?? 0), 0),
        ];

        $webhookUrl = url('/api/webhooks/shipmentbeat');

        return view('super-admin.integrations.api-keys', compact('shipmentBeat', 'aiSetting', 'logs', 'kpis', 'webhookUrl'));
    }

    /**
     * Test local webhook ping dispatch
     */
    public function testWebhookPing(Request $request)
    {
        $startTime = microtime(true);
        $payload = [
            'event' => 'ping',
            'type' => 'HEALTH_CHECK',
            'timestamp' => now()->toIso8601String(),
            'sender' => 'Freight SaaS 2.0 SuperAdmin Console',
            'data' => [
                'message' => 'SuperAdmin API Connectivity Probe',
            ],
        ];

        try {
            // Dispatch internal sub-request to ensure robust testing in CLI and HTTP
            $subRequest = Request::create(
                '/api/webhooks/shipmentbeat',
                'POST',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_X_SHIPMENTBEAT_TIMESTAMP' => (string) time(),
                    'HTTP_X_SHIPMENTBEAT_SIGNATURE' => 'sha256=' . hash_hmac('sha256', json_encode($payload), 'probe_secret'),
                ],
                json_encode($payload)
            );

            $webhookController = app(\App\Http\Controllers\Api\ShipmentBeatWebhookController::class);
            $response = $webhookController->handleWebhook($subRequest);

            $durationMs = round((microtime(true) - $startTime) * 1000);
            $responseData = json_decode($response->getContent(), true);

            // Log ping event in CarrierApiLog
            CarrierApiLog::create([
                'carrier_code' => 'shipmentbeat',
                'endpoint' => '/api/webhooks/shipmentbeat',
                'method' => 'POST',
                'tracking_number' => 'PROBE-PING',
                'request_payload' => $payload,
                'response_status' => $response->getStatusCode(),
                'response_payload' => $responseData,
                'duration_ms' => $durationMs,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => $response->getStatusCode() === 200,
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'message' => 'Webhook receiver active and healthy. HTTP ' . $response->getStatusCode(),
                'response' => $responseData,
            ]);

        } catch (Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => false,
                'status_code' => 500,
                'duration_ms' => $durationMs,
                'message' => 'Webhook ping failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
