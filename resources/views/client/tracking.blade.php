@extends('layouts.userapp')
@section('title', 'Track Shipments - Client Portal')

@section('content')
@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<style>
    .port-marker-pin {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }
    .port-pol-pin { background: #059669; color: #ffffff; }
    .port-pod-pin { background: #dc2626; color: #ffffff; }
    .maritime-pin-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2.5px solid #ffffff;
        box-shadow: 0 3px 10px rgba(37,99,235,0.45);
        cursor: pointer;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .vessel-arrow-container {
        position: relative;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .vessel-pulse-aura {
        position: absolute;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(37, 99, 235, 0.25);
        animation: vesselRadarPulse 2.2s infinite ease-in-out;
    }
    .vessel-arrow-icon {
        position: relative;
        z-index: 2;
        filter: drop-shadow(0 2px 5px rgba(37,99,235,0.6));
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.4s ease;
    }
    .stepper-journey-container {
        position: relative;
        padding-left: 28px;
    }
    .stepper-journey-line {
        position: absolute;
        left: 8px;
        top: 10px;
        bottom: 12px;
        width: 2px;
        background: #e2e8f0;
    }
    .stepper-node-ring {
        position: absolute;
        left: 2px;
        top: 3px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #ffffff;
        box-sizing: border-box;
    }
    .stepper-node-completed {
        border: 2.5px solid #ea580c;
    }
    .stepper-node-upcoming {
        border: 2px solid #cbd5e1;
    }
    .stepper-node-vessel {
        position: absolute;
        left: -3px;
        top: 0px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ea580c;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        box-shadow: 0 0 0 3px #ffedd5;
    }
    @keyframes vesselRadarPulse {
        0% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.75); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 16px rgba(37, 99, 235, 0); }
        100% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    .custom-leaflet-div { background: transparent !important; border: none !important; }
    .container-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        margin: 2px 4px 2px 0;
        transition: all 0.15s ease;
    }
    .container-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(37,99,235,0.25);
    }
    @keyframes iconSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin {
        animation: iconSpin 1s linear infinite !important;
        display: inline-block;
    }
</style>
@endonce

<div style="padding: 16px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;" data-turbo="false">

    <!-- Top Hero Banner & Instant Search -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 12px; padding: 26px 28px; color: #fff; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
        <div style="max-width: 760px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #38bdf8; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); padding: 2px 8px; border-radius: 9999px;">
                    <i class="bi bi-geo-alt-fill"></i> Live Dynamic Carrier Tracking
                </span>
                <span style="font-size: 11px; color: #94a3b8;">ShipmentBeat Maritime Feed</span>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin: 0 0 8px 0; color: #ffffff; letter-spacing: -0.01em;">
                Track Your Cargo Milestones &amp; Live Vessel
            </h2>
            <p style="font-size: 13px; color: #cbd5e1; margin: 0 0 20px 0; line-height: 1.5;">
                Select any container from your shipments list below, or enter any container / Bill of Lading number to view live carrier milestones, ocean routes, and delivery status.
            </p>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('client.tracking') }}" data-turbo="false" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 280px;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: #94a3b8; font-size: 14px;"></i>
                    <input type="text" 
                           id="client-tracking-input"
                           name="q" 
                           value="{{ $activeTracking ? $activeTracking->tracking_number : $searchNumber }}" 
                           placeholder="Enter Container No (e.g. CRSU9148090, MSCU1234567, or Master B/L)..." 
                           style="width: 100%; height: 42px; padding: 0 16px 0 38px; border-radius: 8px; border: 1.5px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.12); color: #fff; font-size: 13px; font-family: monospace; font-weight: 600; outline: none; transition: all 0.2s;"
                           onfocus="this.style.background='rgba(255,255,255,0.2)'; this.style.borderColor='#38bdf8';" 
                           onblur="this.style.background='rgba(255,255,255,0.12)'; this.style.borderColor='rgba(255,255,255,0.25)';">
                </div>
                <button type="submit" 
                        style="height: 42px; padding: 0 22px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                    <i class="bi bi-arrow-right-circle"></i> Track Container
                </button>
            </form>
        </div>
    </div>

    <!-- Error Notice if Carrier could not identify or sync -->
    @if(isset($errorMessage) && $errorMessage)
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-size: 13px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #ef4444; flex-shrink: 0;"></i>
            <div>
                <strong>Carrier Tracking Notice:</strong> {{ $errorMessage }}
            </div>
        </div>
    @endif

    <!-- Active Tracking Dashboard & Timeline (If active container is queried) -->
    @if($activeTracking)
        @php
            $badge = $activeTracking->status_badge;
            $origin = $activeTracking->display_origin;
            $dest = $activeTracking->display_destination;
            $mapData = $activeTracking->map_data;
            $carrierBadge = $activeTracking->carrier_badge;
            $routeDisplay = $activeTracking->route_display;
            $etaDisplay = $activeTracking->eta_display;
            $journeySteps = $activeTracking->journey_steps;
            $containerType = $activeTracking->container_type_label;
        @endphp

        <!-- Top Header Strip (Exact Split Design) -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 22px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <!-- Carrier Badge -->
                <div style="width: 38px; height: 38px; border-radius: 8px; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: {{ $carrierBadge['bg'] }}; color: {{ $carrierBadge['color'] }}; border: 1px solid {{ $carrierBadge['border'] }}; text-transform: lowercase; letter-spacing: -0.02em;">
                    {{ $carrierBadge['short'] ?? 'msc' }}
                </div>

                <!-- Container Number + CN -->
                <div style="display: flex; align-items: baseline; gap: 5px;">
                    <span style="font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.01em; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $activeTracking->tracking_number }}</span>
                    <span style="font-size: 13px; font-weight: 700; color: #64748b;">CN</span>
                </div>

                <!-- Container Type & Size Badge -->
                <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; font-weight: 500; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span>{{ $containerType }}</span>
                </div>

                <!-- Route Display with Flags -->
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #1e293b;">
                    <span>{{ $routeDisplay['label'] }}</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" 
                        id="btn-sync-live-updates"
                        onclick="refreshLiveTracking('{{ $activeTracking->tracking_number }}')" 
                        style="height: 36px; padding: 0 16px; background: #2563eb; color: #ffffff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(37,99,235,0.3); transition: background 0.15s;">
                    <i class="bi bi-arrow-clockwise" id="btn-sync-icon"></i>
                    <span id="btn-sync-text">Sync Live Updates</span>
                </button>
            </div>
        </div>

        <!-- Main Split Layout (Left Stepper + Right Map) -->
        <div style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: stretch; margin-bottom: 24px;">
            
            <!-- Left Panel: ETA & Stepper Journey -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <!-- ETA Header -->
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">{{ $etaDisplay['label'] }}</div>
                        <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 3px; letter-spacing: -0.02em;">{{ $etaDisplay['date'] }}</div>
                    </div>

                    <!-- Vertical Stepper List -->
                    <div class="stepper-journey-container">
                        <div class="stepper-journey-line"></div>

                        @foreach($journeySteps as $st)
                            <div style="position: relative; margin-bottom: 22px; min-height: 28px;">
                                @if($st['type'] === 'vessel')
                                    <div class="stepper-node-vessel" title="Vessel in transit">
                                        <i class="bi bi-water"></i>
                                    </div>
                                    <div style="font-size: 13px; font-weight: 800; color: #ea580c; line-height: 1.4; padding-top: 3px;">
                                        {{ $st['label'] }}
                                    </div>
                                @else
                                    <div class="stepper-node-ring {{ ($st['status'] ?? '') === 'completed' ? 'stepper-node-completed' : 'stepper-node-upcoming' }}"></div>
                                    <div>
                                        <div style="font-size: 11px; font-weight: 600; color: #64748b; line-height: 1.2;">{{ $st['label'] }}</div>
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 3px; display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 15px;">{{ $st['flag'] }}</span>
                                            <span>{{ $st['city'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Summary Info -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 12px; font-size: 11px; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
                    <span>Status: <strong style="color: #0f172a;">{{ $badge['label'] }}</strong></span>
                    <span><i class="bi bi-clock"></i> {{ $activeTracking->last_polled_at ? $activeTracking->last_polled_at->diffForHumans() : 'Just now' }}</span>
                </div>
            </div>

            <!-- Right Panel: Maritime Map -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative; min-height: 540px;">
                <div id="client-ocean-map" style="width: 100%; height: 100%; min-height: 540px;"></div>

                <!-- Map Legend -->
                <div style="position: absolute; bottom: 14px; left: 14px; z-index: 400; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 11px; color: #334155; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #2563eb; color: #fff; font-size: 9px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;">POL</span> <strong>Origin</strong>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 16px; height: 3px; background: #2563eb; display: inline-block; border-radius: 1px;"></span> <strong>Maritime Lane</strong>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #2563eb; color: #fff; font-size: 9px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;">POD</span> <strong>Destination</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Verified Milestones & Raw History Section -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-clock-history" style="color: #2563eb;"></i> Verified Milestone History &amp; Telemetry
                </h3>
                <span style="font-size: 11px; color: #64748b; font-weight: 600;">{{ count($activeTracking->events) }} verified milestones</span>
            </div>

            <div style="position: relative; padding-left: 20px;">
                <div style="position: absolute; left: 6px; top: 6px; bottom: 6px; width: 2px; background: #e2e8f0;"></div>

                @forelse($activeTracking->events as $event)
                    @php $cls = $event->classifier_badge; @endphp
                    <div style="position: relative; margin-bottom: 16px;">
                        <div style="position: absolute; left: -20px; top: 8px; width: 12px; height: 12px; border-radius: 50%; background: #ffffff; border: 2.5px solid {{ $event->event_classifier === 'ACT' ? '#10b981' : '#3b82f6' }}; box-shadow: 0 0 0 2px {{ $event->event_classifier === 'ACT' ? '#dcfce7' : '#dbeafe' }};"></div>
                        
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="font-size: 13px; color: #0f172a;">{{ $event->title }}</strong>
                                    <span style="font-size: 9px; padding: 1px 6px; border-radius: 3px; font-weight: 700; background: {{ $cls['bg'] }}; color: {{ $cls['color'] }};">
                                        {{ $cls['text'] }}
                                    </span>
                                </div>
                                <span style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    {{ $event->event_date_time ? $event->event_date_time->format('M d, Y H:i') : '' }}
                                </span>
                            </div>

                            @if($event->description)
                                <div style="font-size: 12px; color: #475569; margin-top: 4px;">
                                    {{ $event->description }}
                                </div>
                            @endif

                            <div style="display: flex; gap: 14px; font-size: 11px; color: #64748b; margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 6px; flex-wrap: wrap;">
                                @if($event->location_name)
                                    <span><i class="bi bi-geo-alt"></i> {{ $event->location_name }}</span>
                                @endif
                                @if($event->vessel_name)
                                    <span><i class="bi bi-ship"></i> {{ $event->vessel_name }} {{ $event->voyage_no ? ' / ' . $event->voyage_no : '' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">
                        Live carrier milestones are registered and active. No milestone logs recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
                <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #dbeafe; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 8px;">
                        <i class="bi bi-arrow-repeat" id="sync-icon-header"></i>
                    </div>
                    <span style="font-size: 13px; color: #1e40af; display: block; margin-bottom: 4px; font-weight: 700;">
                        Sync live updates from carrier network
                    </span>
                    <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 12px;">
                        Poll {{ strtoupper($activeTracking->carrier_code ?: 'Carrier') }} network for real-time milestones.
                    </span>
                    <button type="button" 
                            id="btn-sync-live-updates"
                            onclick="refreshLiveTracking('{{ $activeTracking->tracking_number }}')" 
                            style="padding: 10px 22px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 6px rgba(37,99,235,0.3); transition: all 0.2s; width: 100%; max-width: 200px; margin: 0 auto;">
                        <i class="bi bi-arrow-clockwise" id="btn-sync-icon" style="font-size: 15px;"></i>
                        <span id="btn-sync-text">Sync Live Updates</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- CUSTOMER'S ACTIVE SHIPMENTS & CLICKABLE CONTAINERS LIST  -->
    <!-- ======================================================== -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-boxes" style="color: #2563eb;"></i> Your Relevant Shipments &amp; Containers
                </h3>
                <span style="font-size: 12px; color: #64748b; margin-top: 2px; display: block;">
                    Click on any container number badge below to instantly track its live maritime journey.
                </span>
            </div>
            <span style="font-size: 11px; font-weight: 700; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 9999px;">
                {{ $recentShipments->count() }} Recent Shipments
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Type &amp; File #</th>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Master B/L</th>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; min-width: 240px;">Containers List (Click to Track)</th>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Route (POL &rarr; POD)</th>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Vessel</th>
                        <th style="padding: 12px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentShipments as $shp)
                        @php
                            $shipType = $shp->shipment_direction ?? 'Import';
                            $mbl = $shp->mbl_no ?? '';
                            $containers = $shp->containers ?? collect();
                            $firstContainerNo = $containers->first()->container_no ?? $mbl;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <!-- Type & File Number -->
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; {{ $shipType === 'Import' ? 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;' }}">
                                        {{ $shipType }}
                                    </span>
                                    <strong style="color: #0f172a; font-size: 13px;">#{{ $shp->file_no }}</strong>
                                </div>
                            </td>

                            <!-- Master B/L -->
                            <td style="padding: 14px 18px;">
                                @if($mbl)
                                    <a href="{{ route('client.tracking', ['q' => $mbl]) }}" 
                                       data-turbo="false"
                                       title="Track MBL {{ $mbl }}"
                                       style="font-family: monospace; font-weight: 700; color: #2563eb; text-decoration: none;">
                                        {{ $mbl }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>

                            <!-- Clickable Containers List -->
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                    @forelse($containers as $cnt)
                                        @php
                                            $currTracked = $activeTracking ? $activeTracking->tracking_number : $searchNumber;
                                            $isCurrentlyTracked = ($currTracked === $cnt->container_no);
                                        @endphp
                                        <a href="{{ route('client.tracking', ['q' => $cnt->container_no]) }}" 
                                           onclick="if(document.getElementById('client-tracking-input')) document.getElementById('client-tracking-input').value = '{{ $cnt->container_no }}';"
                                           data-turbo="false"
                                           class="container-pill"
                                           title="Click to track container {{ $cnt->container_no }}"
                                           style="{{ $isCurrentlyTracked ? 'background: #2563eb; color: #ffffff; box-shadow: 0 2px 6px rgba(37,99,235,0.3); border: 1px solid #2563eb;' : 'background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe;' }}">
                                            <i class="bi bi-box-seam"></i>
                                            <span>{{ $cnt->container_no }}</span>
                                            @if($isCurrentlyTracked)
                                                <i class="bi bi-check-circle-fill" style="font-size: 10px; margin-left: 2px;"></i>
                                            @endif
                                        </a>
                                    @empty
                                        @if($mbl)
                                            <a href="{{ route('client.tracking', ['q' => $mbl]) }}" 
                                               data-turbo="false"
                                               class="container-pill" 
                                               style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                                <i class="bi bi-file-text"></i> Track via MBL: {{ $mbl }}
                                            </a>
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">Awaiting Container Allocation</span>
                                        @endif
                                    @endforelse
                                </div>
                            </td>

                            <!-- Route POL -> POD -->
                            <td style="padding: 14px 18px; color: #334155;">
                                <div style="font-weight: 600; color: #1e293b;">
                                    {{ $shp->portOfLoading->name ?? ($shp->pol_name ?? 'Origin') }}
                                    <span style="color: #94a3b8; margin: 0 4px;">&rarr;</span>
                                    {{ $shp->portOfDischarge->name ?? ($shp->pod_name ?? 'Destination') }}
                                </div>
                            </td>

                            <!-- Vessel -->
                            <td style="padding: 14px 18px; color: #475569;">
                                {{ $shp->vessel->name ?? ($shp->vessel_name ?? '-') }}
                            </td>

                            <!-- Action -->
                            <td style="padding: 14px 18px; text-align: center;">
                                @if($firstContainerNo)
                                    <a href="{{ route('client.tracking', ['q' => $firstContainerNo]) }}" 
                                       data-turbo="false"
                                       style="padding: 5px 12px; background: #2563eb; color: #fff; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(37,99,235,0.25);">
                                        <i class="bi bi-geo-alt"></i> Track
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px;">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 36px 20px; text-align: center; color: #94a3b8;">
                                <i class="bi bi-box-seam" style="font-size: 28px; display: block; margin-bottom: 8px; color: #cbd5e1;"></i>
                                No recent shipments or containers found for your account.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let clientMapInstance = null;

function refreshLiveTracking(trackingNumber) {
    const btn = document.getElementById('btn-sync-live-updates');
    const icon = document.getElementById('btn-sync-icon');
    const text = document.getElementById('btn-sync-text');
    const headerIcon = document.getElementById('sync-icon-header');

    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.cursor = 'not-allowed';
    }
    if (icon) {
        icon.className = 'bi bi-arrow-repeat spin';
    }
    if (headerIcon) {
        headerIcon.className = 'bi bi-arrow-repeat spin';
    }
    if (text) {
        text.innerText = 'Syncing...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch('{{ route("api.tracking.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ tracking_number: trackingNumber })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (text) text.innerText = 'Updated!';
            setTimeout(() => {
                if (window.Turbo && window.Turbo.clearCache) {
                    window.Turbo.clearCache();
                }
                window.location.href = window.location.href.split('#')[0];
            }, 300);
        } else {
            alert(res.message || 'Tracking sync failed.');
            resetSyncButton();
        }
    })
    .catch(err => {
        alert('Network error while refreshing live tracking from carrier network.');
        resetSyncButton();
    });

    function resetSyncButton() {
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
        if (icon) {
            icon.className = 'bi bi-arrow-clockwise';
        }
        if (headerIcon) {
            headerIcon.className = 'bi bi-arrow-repeat';
        }
        if (text) {
            text.innerText = 'Sync Live Updates';
        }
    }
}

@if($activeTracking && !empty($mapData) && !empty($mapData['has_route']))
function initClientOceanMap() {
    const mapContainer = document.getElementById('client-ocean-map');
    if (!mapContainer) return;

    if (typeof L === 'undefined') {
        setTimeout(initClientOceanMap, 60);
        return;
    }

    // Clean up existing map instance or container leaflet ID
    if (clientMapInstance) {
        try {
            clientMapInstance.remove();
        } catch (e) {}
        clientMapInstance = null;
    }

    if (mapContainer._leaflet_id) {
        delete mapContainer._leaflet_id;
    }

    const origin = @json($mapData['origin']);
    const dest = @json($mapData['destination']);
    const waypoints = @json($mapData['waypoints'] ?? []);
    const vessel = @json($mapData['vessel'] ?? null);

    clientMapInstance = L.map('client-ocean-map', {
        attributionControl: false,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 16,
        attribution: '&copy; Esri &copy; OpenStreetMap'
    }).addTo(clientMapInstance);

    if (waypoints && waypoints.length > 1) {
        L.polyline(waypoints, {
            color: '#2563eb',
            weight: 3,
            opacity: 0.85,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(clientMapInstance);
    }

    const polIcon = L.divIcon({
        className: 'custom-leaflet-div',
        html: `<div class="maritime-pin-badge" title="Port of Loading: ${origin.label}">POL</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });
    L.marker([origin.lat, origin.lng], { icon: polIcon })
        .addTo(clientMapInstance)
        .bindPopup(`
            <div style="font-family: 'Inter', -apple-system, sans-serif; padding: 4px 6px;">
                <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">POL</div>
                <div style="font-size: 11px; color: #475569; font-weight: 600; line-height: 1.35;">${origin.terminal || origin.label}</div>
            </div>
        `);

    const podIcon = L.divIcon({
        className: 'custom-leaflet-div',
        html: `<div class="maritime-pin-badge" title="Port of Discharge: ${dest.label}">POD</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });
    const clientPodMarker = L.marker([dest.lat, dest.lng], { icon: podIcon })
        .addTo(clientMapInstance)
        .bindPopup(`
            <div style="font-family: 'Inter', -apple-system, sans-serif; padding: 4px 6px;">
                <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">POD</div>
                <div style="font-size: 11px; color: #475569; font-weight: 600; line-height: 1.35;">${dest.terminal || dest.label}</div>
            </div>
        `);

    setTimeout(() => {
        if (clientPodMarker) clientPodMarker.openPopup();
    }, 350);

    if (vessel && vessel.lat && vessel.lng) {
        const heading = vessel.heading || 0;
        const vesselIcon = L.divIcon({
            className: 'custom-leaflet-div',
            html: `
                <div class="vessel-arrow-container" title="${vessel.name}">
                    <div class="vessel-pulse-aura"></div>
                    <div class="vessel-arrow-icon" style="transform: rotate(${heading}deg);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#2563eb">
                            <polygon points="12 2, 22 22, 12 17, 2 22" />
                        </svg>
                    </div>
                </div>
            `,
            iconSize: [44, 44],
            iconAnchor: [22, 22],
        });
        L.marker([vessel.lat, vessel.lng], { icon: vesselIcon, zIndexOffset: 1000 })
            .addTo(clientMapInstance)
            .bindPopup(`
                <div style="font-family: 'Inter', -apple-system, sans-serif; padding: 4px 6px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0f172a;">${vessel.name}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Voyage: ${vessel.voyage || 'Active'} • Heading: ${Math.round(heading)}°</div>
                </div>
            `);
    }

    fitClientMap();

    setTimeout(() => {
        if (clientMapInstance) {
            clientMapInstance.invalidateSize();
            fitClientMap();
        }
    }, 200);
}

function fitClientMap() {
    if (!clientMapInstance) return;
    clientMapInstance.invalidateSize();
    const waypoints = @json($mapData['waypoints'] ?? []);
    const origin = @json($mapData['origin']);
    const dest = @json($mapData['destination']);

    let bounds = L.latLngBounds();
    if (Array.isArray(waypoints) && waypoints.length > 0) {
        if (Array.isArray(waypoints[0]) && Array.isArray(waypoints[0][0])) {
            waypoints.forEach(leg => leg.forEach(pt => bounds.extend(pt)));
        } else {
            waypoints.forEach(pt => bounds.extend(pt));
        }
    } else {
        bounds = L.latLngBounds([[origin.lat, origin.lng], [dest.lat, dest.lng]]);
    }
    bounds.extend([origin.lat, origin.lng]);
    bounds.extend([dest.lat, dest.lng]);
    clientMapInstance.fitBounds(bounds, { padding: [35, 35] });
}

// Trigger map initialization across full page loads & dynamic navigations
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientOceanMap);
} else {
    initClientOceanMap();
}
document.addEventListener('turbo:load', initClientOceanMap);
document.addEventListener('turbo:render', initClientOceanMap);
@endif
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection