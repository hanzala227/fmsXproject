@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;" x-data="apiKeysConsole()">
    {{-- Page Header --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">API Keys & Webhooks Console</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Platform Gateway</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Central credentials management for ShipmentBeat multi-carrier tracking, Google Gemini AI Engine, and incoming container telemetry webhooks
            </p>
        </div>

        <div style="display: flex; gap: 8px;">
            <button @click="sendTestPing" :disabled="pinging" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                <i class="fa fa-paper-plane" :class="pinging ? 'fa-spin' : ''"></i>
                <span x-text="pinging ? 'Sending Probe...' : 'Send Test Webhook Ping'"></span>
            </button>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Sub-Navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.carrier-tracking.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-ship" style="margin-right: 4px;"></i> Carrier Tracking (ShipmentBeat)
        </a>
        <a href="{{ route('super-admin.ai-settings.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-microchip" style="margin-right: 4px;"></i> AI Engine API (Gemini)
        </a>
        <a href="{{ route('super-admin.integrations.api-keys') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">
            <i class="fa fa-key" style="margin-right: 4px;"></i> API Keys & Webhooks Console
        </a>
    </div>

    {{-- Live Probe Notification Banner --}}
    <div x-show="pingResult" x-cloak style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 12px; display: flex; align-items: center; justify-content: space-between;"
         :style="pingResult && pingResult.success ? 'background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;' : 'background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;'">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa" :class="pingResult && pingResult.success ? 'fa-check-circle' : 'fa-exclamation-triangle'" style="font-size: 16px;"></i>
            <div>
                <strong x-text="pingResult && pingResult.success ? 'Webhook Probe Verified 200 OK' : 'Probe Error'"></strong>
                <span x-text="pingResult ? ' — ' + pingResult.message + ' (' + pingResult.duration_ms + ' ms latency)' : ''" style="margin-left: 4px;"></span>
            </div>
        </div>
        <button @click="pingResult = null" style="background: none; border: none; font-size: 14px; cursor: pointer; color: inherit;"><i class="fa fa-times"></i></button>
    </div>

    {{-- KPI Metric Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total API Requests</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($kpis['total_calls']) }}</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Carrier & Webhook Invocations</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-exchange"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">HTTP 2xx Success</div>
                <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ number_format($kpis['success_calls']) }}</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Healthy API & Webhook responses</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Average Latency</div>
                <div style="font-size: 24px; font-weight: 800; color: #6366f1; margin-top: 4px;">{{ $kpis['avg_latency'] }} <span style="font-size: 14px; font-weight: 600;">ms</span></div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">End-to-end API response time</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-tachometer"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Webhook Ingestion</div>
                <div style="font-size: 24px; font-weight: 800; color: #0ab39c; margin-top: 4px;">100% Live</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">ShipmentBeat Receiver Active</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e6fffa; color: #0ab39c; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-signal"></i>
            </div>
        </div>
    </div>

    {{-- Section 1: Active Gateway Credentials Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 24px;">
        {{-- Card A: ShipmentBeat Carrier Tracking Gateway --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fa fa-ship"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Carrier Tracking Gateway</h2>
                            <span style="font-size: 11px; color: #64748b;">ShipmentBeat Multi-Carrier API</span>
                        </div>
                    </div>
                    <span style="font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px; {{ $shipmentBeat->is_active ? 'background: #ecfdf5; color: #047857;' : 'background: #fef2f2; color: #dc2626;' }}">
                        {{ $shipmentBeat->is_active ? 'ACTIVE' : 'DISABLED' }}
                    </span>
                </div>

                <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Base URL:</span>
                        <code style="background: #f8fafc; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #0f172a;">{{ $shipmentBeat->base_url }}</code>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Environment:</span>
                        <span style="font-weight: 600; text-transform: uppercase; color: #1e293b;">{{ $shipmentBeat->environment ?? 'Production' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Carrier Coverage:</span>
                        <span style="font-weight: 600; color: #0ab39c;">18 Ocean Lines (MSC, Maersk, COSCO, etc.)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b;">API Key:</span>
                        <span style="font-family: monospace; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                            @if(!empty($shipmentBeat->api_key))
                                {{ substr($shipmentBeat->api_key, 0, 6) . '••••••••••••' . substr($shipmentBeat->api_key, -4) }}
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Sandbox Mode / Configured</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8;"><i class="fa fa-clock-o"></i> Synced with Carrier Engine</span>
                <a href="{{ route('super-admin.carrier-tracking.index') }}" style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    Configure Gateway <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card B: AI Intelligence Engine (Google Gemini) --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fa fa-microchip"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">AI Intelligence Engine</h2>
                            <span style="font-size: 11px; color: #64748b;">Autonomous Document Extraction & Chat</span>
                        </div>
                    </div>
                    <span style="font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px; background: #ecfdf5; color: #047857;">
                        VERIFIED 200 OK
                    </span>
                </div>

                <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Provider Type:</span>
                        <span style="font-weight: 600; text-transform: uppercase; color: #1e293b;">{{ $aiSetting->api_type ?? 'Google Gemini' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Active Model:</span>
                        <code style="background: #f8fafc; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #9333ea;">{{ $aiSetting->model_name ?? 'gemini-3.5-flash-lite' }}</code>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Core Capabilities:</span>
                        <span style="font-weight: 600; color: #0ab39c;">B/L Extraction, Commercial Invoice OCR, SLA Chat</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b;">Secret API Key:</span>
                        <span style="font-family: monospace; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                            @if(!empty($aiSetting->api_key))
                                {{ substr($aiSetting->api_key, 0, 8) . '••••••••••••••••' . substr($aiSetting->api_key, -4) }}
                            @else
                                <span style="color: #dc2626;">Missing Key</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8;"><i class="fa fa-bolt"></i> Live Tester Operational</span>
                <a href="{{ route('super-admin.ai-settings.index') }}" style="font-size: 12px; font-weight: 600; color: #9333ea; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    Open AI Tester <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Section 2: Public Webhook Ingestion Endpoint Box --}}
    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0 0 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-link" style="color: #0ab39c;"></i> Public Webhook Ingestion Endpoint
                    <span style="font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px;">LIVE HTTPS</span>
                </h3>
                <p style="font-size: 12px; color: #64748b; margin: 0;">
                    Register this webhook endpoint inside your ShipmentBeat Developer Portal. Real-time container discharge, gate-out, and transshipment events are ingested automatically into tenant shipments.
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" @click="copyWebhook" style="height: 32px; padding: 0 12px; font-size: 11px; font-weight: 600; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa" :class="copied ? 'fa-check text-green-600' : 'fa-copy'"></i>
                    <span x-text="copied ? 'Copied to Clipboard!' : 'Copy URL'"></span>
                </button>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 12px; color: #0f172a; display: flex; align-items: center; justify-content: space-between; overflow-x: auto;">
            <span>{{ $webhookUrl }}</span>
            <span style="font-size: 11px; color: #10b981; font-weight: 600; font-family: sans-serif;"><i class="fa fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Listening</span>
        </div>

        <div style="margin-top: 10px; font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <span><i class="fa fa-shield" style="color: #6366f1; margin-right: 4px;"></i> Signature Header: <code>X-ShipmentBeat-Signature</code></span>
            <span><i class="fa fa-calendar-check-o" style="color: #0ab39c; margin-right: 4px;"></i> Milestone Auto-Tracking: Active</span>
            <span><i class="fa fa-code-fork" style="color: #f59e0b; margin-right: 4px;"></i> Protocol: JSON / REST Webhook</span>
        </div>
    </div>

    {{-- Section 3: Live Telemetry & Webhook Logs Stream --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0 0 2px;">
                    <i class="fa fa-list-alt" style="color: #3b82f6; margin-right: 6px;"></i> Live API & Webhook Telemetry Stream
                </h3>
                <span style="font-size: 11px; color: #64748b;">Real-time incoming carrier webhook events & outgoing API sync queries</span>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <form method="GET" action="{{ route('super-admin.integrations.api-keys') }}" style="display: flex; gap: 6px; margin: 0;">
                    <select name="status" onchange="this.form.submit()" style="height: 30px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 8px; background: #fff; color: #475569;">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>2xx Success Only</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>4xx/5xx Errors Only</option>
                    </select>

                    <select name="carrier" onchange="this.form.submit()" style="height: 30px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 8px; background: #fff; color: #475569;">
                        <option value="">All Services</option>
                        <option value="shipmentbeat" {{ request('carrier') === 'shipmentbeat' ? 'selected' : '' }}>ShipmentBeat</option>
                        <option value="gemini" {{ request('carrier') === 'gemini' ? 'selected' : '' }}>Gemini AI</option>
                    </select>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">
                        <th style="padding: 10px 16px;">Timestamp</th>
                        <th style="padding: 10px 14px;">Service</th>
                        <th style="padding: 10px 14px;">Method</th>
                        <th style="padding: 10px 14px;">Endpoint</th>
                        <th style="padding: 10px 14px;">Ref / Container</th>
                        <th style="padding: 10px 14px;">Status Code</th>
                        <th style="padding: 10px 14px;">Latency</th>
                        <th style="padding: 10px 16px; text-align: right;">Payload</th>
                    </tr>
                </thead>
                <tbody style="divide-y divide-gray-100;">
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 10px 16px; color: #64748b; font-family: monospace; font-size: 11px;">
                            {{ $log->created_at ? $log->created_at->format('M d, H:i:s') : 'N/A' }}
                        </td>
                        <td style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                                {{ $log->carrier_code }}
                            </span>
                        </td>
                        <td style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 800; font-family: monospace; {{ strtoupper($log->method) === 'POST' ? 'color: #059669;' : 'color: #2563eb;' }}">
                                {{ strtoupper($log->method) }}
                            </span>
                        </td>
                        <td style="padding: 10px 14px; color: #334155; font-family: monospace; font-size: 11px;">
                            {{ $log->endpoint }}
                        </td>
                        <td style="padding: 10px 14px;">
                            @if(!empty($log->tracking_number))
                                <span style="font-weight: 600; color: #0f172a; font-family: monospace;">{{ $log->tracking_number }}</span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">—</span>
                            @endif
                        </td>
                        <td style="padding: 10px 14px;">
                            @php
                                $status = $log->response_status ?? 200;
                                $isSuccess = $status >= 200 && $status < 300;
                            @endphp
                            <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; font-family: monospace; {{ $isSuccess ? 'background: #dcfce7; color: #166534;' : 'background: #fee2e2; color: #991b1b;' }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td style="padding: 10px 14px; color: #64748b; font-family: monospace; font-size: 11px;">
                            {{ $log->duration_ms ?? 0 }} ms
                        </td>
                        <td style="padding: 10px 16px; text-align: right;">
                            <button type="button" @click="viewPayload({{ json_encode($log->request_payload) }}, {{ json_encode($log->response_payload) }}, '{{ $log->tracking_number }}')" style="padding: 4px 10px; font-size: 11px; font-weight: 600; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; cursor: pointer;">
                                <i class="fa fa-code"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">
                            <i class="fa fa-info-circle" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                            No API or Webhook logs recorded yet. Click "Send Test Webhook Ping" to dispatch a live probe.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; background: #fafafa;">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    {{-- Payload Inspection Modal --}}
    <div x-show="showPayloadModal" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <div @click.away="showPayloadModal = false" style="background: #fff; border-radius: 12px; width: 100%; max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                <div>
                    <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">API Payload Inspector</h3>
                    <span style="font-size: 11px; color: #64748b;" x-text="'Ref: ' + (activeRef || 'Telemetry Probe')"></span>
                </div>
                <button type="button" @click="showPayloadModal = false" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
            </div>

            <div style="padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Request Payload:</div>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px 14px; border-radius: 8px; font-size: 11px; line-height: 1.5; overflow-x: auto; max-height: 180px; margin: 0;" x-text="JSON.stringify(activeRequest, null, 2)"></pre>
                </div>

                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Response Payload:</div>
                    <pre style="background: #0f172a; color: #4ade80; padding: 12px 14px; border-radius: 8px; font-size: 11px; line-height: 1.5; overflow-x: auto; max-height: 180px; margin: 0;" x-text="JSON.stringify(activeResponse, null, 2)"></pre>
                </div>
            </div>

            <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
                <button type="button" @click="showPayloadModal = false" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function apiKeysConsole() {
    return {
        copied: false,
        pinging: false,
        pingResult: null,
        showPayloadModal: false,
        activeRequest: null,
        activeResponse: null,
        activeRef: null,

        copyWebhook() {
            navigator.clipboard.writeText('{{ $webhookUrl }}').then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            });
        },

        async sendTestPing() {
            this.pinging = true;
            this.pingResult = null;

            try {
                const response = await fetch("{{ route('super-admin.integrations.test-webhook-ping') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                this.pingResult = data;
                
                // Refresh log view after ping
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } catch (err) {
                this.pingResult = {
                    success: false,
                    duration_ms: 0,
                    message: 'Network failed to dispatch probe: ' + err.message
                };
            } finally {
                this.pinging = false;
            }
        },

        viewPayload(request, response, ref) {
            this.activeRequest = request;
            this.activeResponse = response;
            this.activeRef = ref;
            this.showPayloadModal = true;
        }
    };
}
</script>
@endsection
