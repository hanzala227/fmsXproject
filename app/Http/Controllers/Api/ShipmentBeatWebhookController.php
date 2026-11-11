<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentTracking;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipmentBeatWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from ShipmentBeat.
     */
    public function handleWebhook(Request $request)
    {
        // Friendly status check when opened in a browser
        if ($request->isMethod('GET')) {
            return response()->json([
                'status' => 'healthy',
                'service' => 'ShipmentBeat Webhook Receiver',
                'message' => 'Endpoint is live and ready to receive POST webhook events.',
                'timestamp' => now()->toIso8601String(),
            ], 200);
        }

        $timestamp = $request->header('X-ShipmentBeat-Timestamp');
        $signature = $request->header('X-ShipmentBeat-Signature');
        $payload = $request->all();

        Log::info('ShipmentBeat Webhook Received', [
            'timestamp' => $timestamp,
            'has_signature' => !empty($signature),
            'payload' => $payload,
        ]);

        // Health / verification ping from dashboard
        if (empty($payload) || ($payload['type'] ?? '') === 'ping' || ($payload['event'] ?? '') === 'ping') {
            return response()->json([
                'success' => true,
                'message' => 'ShipmentBeat webhook active and healthy.',
            ], 200);
        }

        $eventType = $payload['type'] ?? ($payload['event'] ?? null);
        $data = $payload['data'] ?? $payload;

        $trackingNumber = $data['trackingNumber']
            ?? ($data['container_number'] 
            ?? ($data['tracking_number'] 
            ?? ($data['shipment_id'] 
            ?? ($data['bill_of_lading'] 
            ?? null))));

        if ($trackingNumber) {
            $tracking = ShipmentTracking::where('tracking_number', strtoupper(trim($trackingNumber)))->first();

            if ($tracking) {
                $status = $data['trackingStatus'] ?? ($data['status'] ?? $tracking->current_status);
                $eta = isset($data['eta']) ? date('Y-m-d H:i:s', strtotime($data['eta'])) : $tracking->eta;
                $etd = isset($data['etd']) ? date('Y-m-d H:i:s', strtotime($data['etd'])) : $tracking->etd;

                $tracking->update([
                    'current_status' => $status,
                    'status_description' => $data['reason'] ?? ($data['status_description'] ?? $tracking->status_description),
                    'eta' => $eta,
                    'etd' => $etd,
                    'vessel_name' => $data['vessel_name'] ?? ($data['vessel'] ?? $tracking->vessel_name),
                    'voyage_no' => $data['voyage_no'] ?? ($data['voyage'] ?? $tracking->voyage_no),
                    'last_polled_at' => now(),
                    'raw_payload' => $payload,
                ]);

                // Record milestone event if available
                TrackingEvent::create([
                    'shipment_tracking_id' => $tracking->id,
                    'event_id' => $payload['id'] ?? ('sb_evt_' . uniqid()),
                    'event_type' => $eventType ?? 'STATUS_UPDATE',
                    'event_classifier' => 'ACT',
                    'event_date_time' => isset($payload['createdAt']) ? date('Y-m-d H:i:s', strtotime($payload['createdAt'])) : now(),
                    'title' => ucfirst(str_replace(['shipment.', '_'], ['', ' '], $eventType ?? 'Update')),
                    'description' => $data['reason'] ?? "Received status: {$status}",
                    'location_name' => $data['location'] ?? ($data['route']['destination'] ?? null),
                    'is_current' => true,
                    'is_manual' => false,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed.',
        ], 200);
    }
}
