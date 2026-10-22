@extends('super-admin.layout')

@section('content')
<div style="padding: 20px; max-width: 1200px; margin: 0 auto;" x-data="carrierTrackingAdmin()">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-ship" style="color: #405189;"></i> Carrier Tracking API Integrations
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">
                Configure Hapag-Lloyd, Maersk, and generic DCSA container tracking endpoints with live connection diagnostics.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.tracking') }}" target="_blank" style="padding: 8px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #334155; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-external-link"></i> Open Admin Tracking Center
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 13px; display: flex; align-items: center;">
            <i class="fa fa-check-circle" style="margin-right: 8px; font-size: 16px;"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Carrier Selection Tabs -->
    <div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
        <button type="button" 
                @click="activeCarrier = 'hapag_lloyd'"
                :style="activeCarrier === 'hapag_lloyd' ? 'background:#405189; color:#fff; font-weight:600;' : 'background:#fff; color:#475569; border:1px solid #cbd5e1;'"
                style="padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; border: none; transition: all 0.2s;">
            <i class="fa fa-anchor"></i> Hapag-Lloyd (DCSA v2/v3)
            <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.25);">FREE</span>
        </button>

        <button type="button" 
                @click="activeCarrier = 'maersk'"
                :style="activeCarrier === 'maersk' ? 'background:#405189; color:#fff; font-weight:600;' : 'background:#fff; color:#475569; border:1px solid #cbd5e1;'"
                style="padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; border: none; transition: all 0.2s;">
            <i class="fa fa-star"></i> Maersk Line (Track & Trace)
            <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.25);">Consumer Key</span>
        </button>

        <button type="button" 
                @click="activeCarrier = 'dcsa'"
                :style="activeCarrier === 'dcsa' ? 'background:#405189; color:#fff; font-weight:600;' : 'background:#fff; color:#475569; border:1px solid #cbd5e1;'"
                style="padding: 10px 18px; border-radius: 6px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; border: none; transition: all 0.2s;">
            <i class="fa fa-cubes"></i> Generic DCSA Gateway
            <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.25);">Standard</span>
        </button>
    </div>

    <!-- Carrier Config Form Cards -->
    @foreach(['hapag_lloyd', 'maersk', 'dcsa'] as $code)
        @php
            $c = $carriers[$code] ?? null;
        @endphp
        <div x-show="activeCarrier === '{{ $code }}'" x-cloak style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; overflow: hidden;">
            
            <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: #1e293b;">
                        {{ $c->name ?? ucfirst($code) }} Configuration
                    </h3>
                    <span style="font-size: 12px; color: #64748b;">
                        @if($code === 'hapag_lloyd')
                            Official developer portal: <a href="https://www.hapag-lloyd.com/en/services-information/data-solutions/api-portal.html" target="_blank" style="color: #3b82f6;">developer.hlag.com</a> (Free standard Track & Trace)
                        @elseif($code === 'maersk')
                            Official developer portal: <a href="https://developer.maersk.com" target="_blank" style="color: #3b82f6;">developer.maersk.com</a> (Consumer Key auth)
                        @else
                            Digital Container Shipping Association: <a href="https://reference.dcsa.org" target="_blank" style="color: #3b82f6;">reference.dcsa.org</a> (Standard v2.2/v3.0)
                        @endif
                    </span>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" 
                            @click="runTestConnection('{{ $code }}')"
                            :disabled="testingCarrier === '{{ $code }}'"
                            style="padding: 8px 16px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa" :class="testingCarrier === '{{ $code }}' ? 'fa-spinner fa-spin' : 'fa-plug'"></i>
                        <span x-text="testingCarrier === '{{ $code }}' ? 'Testing Connection...' : 'Test Connection'"></span>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('super-admin.carrier-tracking.update') }}" style="padding: 24px;">
                @csrf
                @method('PUT')
                <input type="hidden" name="carrier_code" value="{{ $code }}">

                <!-- Environment & Active Status -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 8px;">Environment Mode</label>
                        <div style="display: flex; gap: 16px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #1e293b; cursor: pointer;">
                                <input type="radio" name="environment" value="sandbox" {{ ($c->environment ?? 'sandbox') === 'sandbox' ? 'checked' : '' }}>
                                <span>Sandbox / Test Environment</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #1e293b; cursor: pointer;">
                                <input type="radio" name="environment" value="production" {{ ($c->environment ?? '') === 'production' ? 'checked' : '' }}>
                                <span style="font-weight: 600; color: #059669;">Production (Live APIs)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 8px;">Direct Carrier API Integration</label>
                        <div style="font-size: 12px; color: #334155; display: flex; align-items: center; gap: 6px; padding: 6px 0;">
                            <i class="fa fa-shield" style="color: #059669;"></i>
                            <span>100% Real-Time API Fetching directly from shipping line gateways (No static or mock data).</span>
                        </div>
                    </div>
                </div>

                <!-- Base URL -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Base API Gateway URL</label>
                    <input type="text" name="base_url" value="{{ $c->base_url ?? '' }}" placeholder="https://api.hlag.com" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                </div>

                <!-- Dynamic Credentials Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    @if($code === 'hapag_lloyd')
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Client ID (OAuth2)</label>
                            <input type="text" name="client_id" value="{{ $c->client_id ?? '' }}" placeholder="Enter Hapag-Lloyd Client ID" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Client Secret (OAuth2)</label>
                            <input type="password" name="client_secret" value="{{ $c->client_secret ?? '' }}" placeholder="Enter Hapag-Lloyd Client Secret" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Subscription Key / API Key (Optional)</label>
                            <input type="text" name="api_key" value="{{ $c->api_key ?? '' }}" placeholder="X-IBM-Client-Id / API Key" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                    @elseif($code === 'maersk')
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Consumer Key (Primary API Key)</label>
                            <input type="text" name="api_key" value="{{ $c->api_key ?? '' }}" placeholder="Maersk App Consumer Key" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Consumer Secret (Optional)</label>
                            <input type="password" name="client_secret" value="{{ $c->client_secret ?? '' }}" placeholder="Maersk App Secret" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Customer Code / Party Code (Optional)</label>
                            <input type="text" name="client_id" value="{{ $c->client_id ?? '' }}" placeholder="Customer Code" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                    @else
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Bearer Token / API Key</label>
                            <input type="text" name="api_key" value="{{ $c->api_key ?? '' }}" placeholder="Bearer token for DCSA carrier gateway" style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; outline: none;">
                        </div>
                    @endif
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ ($c->is_active ?? true) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                        <span>Active for automatic and manual tracking lookups</span>
                    </label>

                    <button type="submit" style="padding: 10px 22px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa fa-save"></i> Save {{ $c->name ?? ucfirst($code) }} Settings
                    </button>
                </div>
            </form>
        </div>
    @endforeach

    <!-- Test Diagnostic Result Modal -->
    <div x-show="showDiagnosticModal" x-cloak style="position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center;" @click.self="showDiagnosticModal = false">
        <div style="background: #fff; border-radius: 8px; max-width: 580px; width: 92%; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-stethoscope" style="color: #3b82f6;"></i> Connection Diagnostic Result
                </h4>
                <button type="button" @click="showDiagnosticModal = false" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 20px;">
                <div :style="diagnosticResult.success ? 'background:#dcfce7; border-color:#86efac; color:#166534;' : 'background:#fee2e2; border-color:#fca5a5; color:#991b1b;'" style="padding: 14px; border-radius: 6px; border: 1px solid; margin-bottom: 16px; font-size: 13px;">
                    <div style="font-weight: 700; margin-bottom: 4px;" x-text="diagnosticResult.success ? 'Connection Successful!' : 'Connection Warning / Failed'"></div>
                    <div x-text="diagnosticResult.message"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; font-size: 12px;">
                    <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="color: #64748b; display: block;">HTTP Status:</span>
                        <strong style="font-size: 14px; color: #1e293b;" x-text="diagnosticResult.status_code || 200"></strong>
                    </div>
                    <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="color: #64748b; display: block;">Latency:</span>
                        <strong style="font-size: 14px; color: #1e293b;" x-text="(diagnosticResult.latency_ms || 0) + ' ms'"></strong>
                    </div>
                </div>

                <div>
                    <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 6px;">Response Details:</span>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 6px; font-size: 11px; max-height: 200px; overflow-y: auto; margin: 0;" x-text="JSON.stringify(diagnosticResult.details || {}, null, 2)"></pre>
                </div>
            </div>
            <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: right;">
                <button type="button" @click="showDiagnosticModal = false" style="padding: 6px 16px; background: #405189; color: #fff; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>

    <!-- Carrier API Logs Table -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0 0 2px 0; font-size: 14px; font-weight: 700; color: #1e293b;">
                    <i class="fa fa-history" style="color: #64748b;"></i> Carrier API Request & Audit Logs
                </h3>
                <span style="font-size: 12px; color: #64748b;">Live record of all DCSA queries made to Hapag-Lloyd, Maersk, and DCSA gateways.</span>
            </div>
            @if($logs->isNotEmpty())
                <form method="POST" action="{{ route('super-admin.carrier-tracking.clear-logs') }}" onsubmit="return confirm('Clear all carrier API logs?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 6px 12px; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                        <i class="fa fa-trash"></i> Clear Logs
                    </button>
                </form>
            @endif
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Time</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Carrier</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Method & Endpoint</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Tracking No.</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Status</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Latency</th>
                        <th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">User / Tenant</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 14px; color: #64748b; font-family: monospace;">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td style="padding: 10px 14px; font-weight: 600; color: #1e293b;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; background: #e0e7ff; color: #3730a3;">
                                    {{ strtoupper($log->carrier_code) }}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; color: #334155; font-family: monospace; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <strong style="color: #0284c7;">{{ $log->method }}</strong> {{ $log->endpoint }}
                            </td>
                            <td style="padding: 10px 14px; font-weight: 600; font-family: monospace; color: #1e293b;">
                                {{ $log->tracking_number ?: '-' }}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; {{ $log->response_status >= 200 && $log->response_status < 300 ? 'background:#dcfce7; color:#166534;' : 'background:#fee2e2; color:#991b1b;' }}">
                                    HTTP {{ $log->response_status }}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; color: #64748b;">
                                {{ $log->duration_ms }} ms
                            </td>
                            <td style="padding: 10px 14px; color: #64748b;">
                                {{ $log->user->name ?? 'System' }} {{ $log->company ? '(' . $log->company->name . ')' : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 24px; text-align: center; color: #94a3b8;">
                                No carrier API transactions recorded yet. Click "Test Connection" or perform a tracking sync to generate logs.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function carrierTrackingAdmin() {
    return {
        activeCarrier: 'hapag_lloyd',
        testingCarrier: null,
        showDiagnosticModal: false,
        diagnosticResult: {},

        runTestConnection(carrierCode) {
            this.testingCarrier = carrierCode;
            fetch('{{ route("super-admin.carrier-tracking.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ carrier_code: carrierCode })
            })
            .then(res => res.json())
            .then(data => {
                this.diagnosticResult = data;
                this.showDiagnosticModal = true;
            })
            .catch(err => {
                this.diagnosticResult = {
                    success: false,
                    message: err.message || 'Request failed',
                    status_code: 500,
                    latency_ms: 0,
                    details: { error: String(err) }
                };
                this.showDiagnosticModal = true;
            })
            .finally(() => {
                this.testingCarrier = null;
            });
        }
    };
}
</script>
@endsection

