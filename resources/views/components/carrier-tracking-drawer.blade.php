<!-- Live Dynamic Carrier Tracking Drawer / Modal -->
@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<style>
    @keyframes vesselRadarPulse {
        0% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.75); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 16px rgba(37, 99, 235, 0); }
        100% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    .vessel-marker-pin {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        border: 2.5px solid #ffffff;
        box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        animation: vesselRadarPulse 2s infinite ease-in-out;
        cursor: pointer;
    }
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
        cursor: pointer;
    }
    .port-pol-pin { background: #059669; color: #ffffff; }
    .port-pod-pin { background: #dc2626; color: #ffffff; }
    .custom-leaflet-div { background: transparent !important; border: none !important; }
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2) !important;
        padding: 2px !important;
    }
</style>
@endonce

@if(!request()->routeIs('admin.tracking'))
<div x-data="carrierTrackingDrawer()" 
     x-init="initDrawer()" 
     @open-carrier-tracking.window="openTracking($event.detail)"
     x-cloak>

    <!-- Overlay Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 99999;"
         @click="close()">
    </div>

    <!-- Sliding Drawer Panel -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         style="position: fixed; top: 0; right: 0; bottom: 0; width: 680px; max-width: 96vw; background: #ffffff; z-index: 100000; box-shadow: -10px 0 40px -10px rgba(15, 23, 42, 0.35); display: flex; flex-direction: column; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

        <!-- Drawer Header -->
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 17px; box-shadow: 0 1px 2px rgba(37,99,235,0.1);">
                    <i class="fa fa-ship"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                            Carrier Track & Trace
                        </h3>
                        <span style="font-size: 10px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;" 
                              :style="'background:' + (statusBadge.bg || '#eff6ff') + '; color:' + (statusBadge.color || '#1e40af') + '; border: 1px solid ' + (statusBadge.color ? statusBadge.color + '33' : '#bfdbfe') + ';'" 
                              x-text="statusBadge.label || 'TRACKING'">
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; display: flex; align-items: center; gap: 8px; margin-top: 3px;">
                        <span style="font-weight: 600; color: #1e293b;" x-text="trackingNumber || 'No Number'"></span>
                        <button type="button" 
                                @click="copyNumber()" 
                                title="Copy tracking number" 
                                style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; cursor: pointer; padding: 1px 6px; border-radius: 4px; font-size: 10px; display: inline-flex; align-items: center; gap: 3px;">
                            <i class="fa fa-copy"></i>
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" 
                    @click="close()" 
                    title="Close Drawer"
                    style="width: 32px; height: 32px; border-radius: 8px; background: #ffffff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 14px; cursor: pointer; transition: all 0.15s ease;" 
                    onmouseover="this.style.background='#f1f5f9'; this.style.color='#0f172a';" 
                    onmouseout="this.style.background='#ffffff'; this.style.color='#64748b';">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- Quick Selector / Sync Action Bar -->
        <div style="padding: 12px 22px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
            
            <div style="display: flex; gap: 8px; align-items: center; flex: 1; min-width: 280px; flex-wrap: wrap;">
                <!-- Carrier Selector Dropdown (Auto-Detect or Select Carrier) -->
                <select x-model="selectedCarrier" 
                        title="Carrier Shipping Line"
                        style="height: 34px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; font-weight: 700; background: #f8fafc; color: #1e293b; outline: none; cursor: pointer; min-width: 130px; max-width: 160px;">
                    <option value="">⚡ Auto-Detect Carrier</option>
                    <option value="MSCU">MSC</option>
                    <option value="MAEU">Maersk</option>
                    <option value="CMDU">CMA CGM</option>
                    <option value="COSU">COSCO</option>
                    <option value="HLCU">Hapag-Lloyd</option>
                    <option value="ONEY">ONE</option>
                    <option value="EGLV">Evergreen</option>
                    <option value="HDMU">HMM</option>
                    <option value="YMLU">Yang Ming</option>
                    <option value="ZIMU">ZIM</option>
                    <option value="WHLC">Wan Hai</option>
                    <option value="PCIU">PIL</option>
                    <option value="OOLU">OOCL</option>
                    <option value="12PD">SITC</option>
                    <option value="ESPU">Emirates</option>
                    <option value="MATS">Matson</option>
                    <option value="13DF">TS Lines</option>
                    <option value="CULU">CU Lines</option>
                </select>

                <!-- Input or Active Containers List -->
                <template x-if="availableContainers.length > 1">
                    <select x-model="trackingNumber" 
                            @change="syncLiveTracking()" 
                            style="height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; font-family: ui-monospace, monospace; font-weight: 600; color: #1e293b; outline: none; flex: 1; min-width: 140px;">
                        <template x-for="c in availableContainers" :key="c">
                            <option :value="c" x-text="c"></option>
                        </template>
                    </select>
                </template>

                <template x-if="availableContainers.length <= 1">
                    <input type="text" 
                           x-model="trackingNumber" 
                           placeholder="Container No / Master B/L (e.g. CRSU9148090)" 
                           @keyup.enter="syncLiveTracking()" 
                           style="height: 34px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: ui-monospace, monospace; font-weight: 600; flex: 1; min-width: 140px; outline: none;"
                           onfocus="this.style.borderColor='#3b82f6'" 
                           onblur="this.style.borderColor='#cbd5e1'">
                </template>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" 
                        @click="syncLiveTracking()" 
                        :disabled="isSyncing || !trackingNumber"
                        style="height: 34px; padding: 0 14px; background: #2563eb; border: none; color: #fff; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(37,99,235,0.25); transition: background 0.15s;"
                        onmouseover="if(!this.disabled) this.style.background='#1d4ed8'"
                        onmouseout="if(!this.disabled) this.style.background='#2563eb'">
                    <i class="fa" :class="isSyncing ? 'fa-spinner fa-spin' : 'fa-refresh'"></i>
                    <span x-text="isSyncing ? 'Syncing...' : 'Sync Live Tracking'"></span>
                </button>

                <button type="button" 
                        @click="showManualModal = true"
                        title="Add manual milestone or note"
                        style="height: 34px; padding: 0 12px; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa fa-plus"></i> Add Note
                </button>
            </div>
        </div>

        <!-- Syncing Linear Progress Bar -->
        <div x-show="isSyncing" style="height: 3px; background: #e0e7ff; overflow: hidden; position: relative;">
            <div style="height: 100%; background: #2563eb; width: 45%; position: absolute; border-radius: 2px; animation: linearBarAnim 1.3s infinite ease-in-out;"></div>
        </div>
        <style>
            @keyframes linearBarAnim {
                0% { left: -45%; width: 45%; }
                50% { left: 30%; width: 50%; }
                100% { left: 100%; width: 45%; }
            }
        </style>

        <!-- Scrollable Body -->
        <div style="flex: 1; overflow-y: auto; padding: 22px;" class="custom-scrollbar">

            <!-- Route & Vessel Card -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 14px; align-items: center; margin-bottom: 14px;">
                    
                    <!-- Origin -->
                    <div>
                        <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 4px;">
                            <i class="fa fa-map-marker" style="color: #64748b; font-size: 12px;"></i>
                            <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Origin (POL)</span>
                        </div>
                        <div style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                            <span x-text="getOriginLabel()"></span>
                            <template x-if="trackingData.origin_locode">
                                <span style="font-size: 10px; font-family: monospace; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #475569; margin-left: 4px;" x-text="trackingData.origin_locode"></span>
                            </template>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;" x-text="trackingData.etd ? 'ETD: ' + formatDate(trackingData.etd) : 'ETD TBA'"></div>
                    </div>

                    <!-- Route Carrier Connector -->
                    <div style="text-align: center; padding: 0 10px; min-width: 140px;">
                        <div style="font-size: 10px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 3px 10px; border-radius: 9999px; border: 1px solid #dbeafe; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;">
                            <i class="fa fa-ship" style="font-size: 10px;"></i>
                            <span x-text="formatCarrierName()"></span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; margin-top: 6px;">
                            <div style="height: 1px; flex: 1; background: #cbd5e1;"></div>
                            <i class="fa fa-arrow-right" style="color: #94a3b8; font-size: 12px;"></i>
                            <div style="height: 1px; flex: 1; background: #cbd5e1;"></div>
                        </div>
                    </div>

                    <!-- Destination -->
                    <div style="text-align: right;">
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 5px; margin-bottom: 4px;">
                            <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Destination (POD)</span>
                            <i class="fa fa-anchor" style="color: #64748b; font-size: 12px;"></i>
                        </div>
                        <div style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                            <template x-if="trackingData.destination_locode">
                                <span style="font-size: 10px; font-family: monospace; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #475569; margin-right: 4px;" x-text="trackingData.destination_locode"></span>
                            </template>
                            <span x-text="getDestinationLabel()"></span>
                        </div>
                        <div style="font-size: 11px; color: #059669; font-weight: 600; margin-top: 3px;" x-text="trackingData.eta ? 'ETA: ' + formatDate(trackingData.eta) : 'ETA TBA'"></div>
                    </div>
                </div>

                <!-- Vessel & Polled Footer -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #475569;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-ship" style="color: #64748b;"></i> 
                        <strong style="color: #1e293b;" x-text="getVesselLabel()"></strong>
                        <span x-text="trackingData.voyage_no ? ' / Voyage ' + trackingData.voyage_no : ''" style="color: #64748b;"></span>
                    </div>
                    <div>
                        <span style="color: #94a3b8;">Last Synced:</span>
                        <span style="font-weight: 600; color: #334155;" x-text="trackingData.last_polled_at ? formatTime(trackingData.last_polled_at) : 'Just now'"></span>
                    </div>
                </div>
            </div>

            <!-- 6-Stage Visual Stepper (Bulletproof CSS Grid Layout) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 14px 16px 14px; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 0 4px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Milestone Progress</label>
                    <span style="font-size: 11px; font-weight: 600; color: #2563eb;" x-text="stepSummaryText()"></span>
                </div>
                
                <!-- Stepper Container with Isolated Track Lines -->
                <div style="position: relative; width: 100%;">
                    
                    <!-- Base Inactive Track Line (spanning center of col 1 to center of col 6) -->
                    <div style="position: absolute; left: 8.333%; width: 83.333%; top: 16px; height: 3px; background: #e2e8f0; z-index: 1; border-radius: 9999px;"></div>
                    
                    <!-- Active Progress Fill Line -->
                    <div style="position: absolute; left: 8.333%; top: 16px; height: 3px; background: #2563eb; z-index: 2; border-radius: 9999px; transition: width 0.35s ease;" 
                         :style="'width: ' + ((currentStepIndex() / 5) * 83.333) + '%'">
                    </div>

                    <!-- 6 Grid Columns (Zero Horizontal Collision Guaranteed) -->
                    <div style="display: grid; grid-template-columns: repeat(6, 1fr); position: relative; z-index: 3;">
                        @php
                            $milestoneSteps = [
                                ['label' => 'Booking', 'icon' => 'fa-file-text-o'],
                                ['label' => 'Gate In', 'icon' => 'fa-truck'],
                                ['label' => 'Loaded', 'icon' => 'fa-ship'],
                                ['label' => 'In Transit', 'icon' => 'fa-compass'],
                                ['label' => 'Discharged', 'icon' => 'fa-anchor'],
                                ['label' => 'Delivered', 'icon' => 'fa-check-circle']
                            ];
                        @endphp
                        @foreach($milestoneSteps as $sIdx => $st)
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                                
                                <!-- Step Circle Node -->
                                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s ease;"
                                     :style="{{ $sIdx }} < currentStepIndex() ? 'background: #2563eb; color: #ffffff; box-shadow: 0 1px 3px rgba(37,99,235,0.3);' : ({{ $sIdx }} === currentStepIndex() ? 'background: #2563eb; color: #ffffff; box-shadow: 0 0 0 4px #dbeafe, 0 2px 4px rgba(37,99,235,0.25);' : 'background: #ffffff; border: 2px solid #cbd5e1; color: #94a3b8;')">
                                    <template x-if="{{ $sIdx }} < currentStepIndex()">
                                        <i class="fa fa-check" style="font-weight: 700;"></i>
                                    </template>
                                    <template x-if="{{ $sIdx }} === currentStepIndex()">
                                        <i class="fa {{ $st['icon'] }}"></i>
                                    </template>
                                    <template x-if="{{ $sIdx }} > currentStepIndex()">
                                        <i class="fa {{ $st['icon'] }}"></i>
                                    </template>
                                </div>

                                <!-- Step Label -->
                                <span style="font-size: 11px; margin-top: 8px; font-weight: 600; line-height: 1.25; text-align: center; max-width: 72px;" 
                                      :style="{{ $sIdx }} <= currentStepIndex() ? 'color: #0f172a; font-weight: 700;' : 'color: #94a3b8; font-weight: 500;'">
                                    {{ $st['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Live Maritime Route & Vessel Tracking Map Card -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa fa-globe" style="color: #2563eb;"></i> Ocean Shipping Route
                        </span>
                        <template x-if="trackingData.map_data && trackingData.map_data.has_route">
                            <span style="font-size: 10px; background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 9999px; font-weight: 700; border: 1px solid #bfdbfe; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa fa-map-signs"></i> Maritime Corridor
                            </span>
                        </template>
                    </div>

                    <div style="display: flex; align-items: center; gap: 6px;">
                        <button type="button" 
                                @click="fitMapRoute()" 
                                title="Center shipping route" 
                                style="background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa fa-crosshairs"></i> Center
                        </button>
                        <button type="button" 
                                @click="openCenterMapModal()" 
                                title="Open full-screen centered ocean map" 
                                style="background: #2563eb; border: 1px solid #1d4ed8; color: #ffffff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(37,99,235,0.25);">
                            <i class="fa fa-arrows-alt"></i> Expand Map
                        </button>
                    </div>
                </div>

                <!-- Active Map Canvas -->
                <div style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <div id="drawer-ocean-map" 
                         style="height: 300px; width: 100%;">
                    </div>

                    <!-- Telemetry Banner Overlay (Shown if carrier port calls are pending confirmation) -->
                    <template x-if="trackingData.map_data && trackingData.map_data.is_pending_ports">
                        <div style="position: absolute; top: 10px; left: 10px; right: 10px; z-index: 400; background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(6px); color: #fff; padding: 7px 12px; border-radius: 6px; font-size: 11px; display: flex; align-items: center; justify-content: space-between; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-compass fa-spin" style="color: #60a5fa; font-size: 13px;"></i>
                                <span><strong>Ocean Route:</strong> Scheduled maritime corridor plotted between ports.</span>
                            </div>
                            <span style="font-size: 9px; background: rgba(96,165,250,0.2); color: #93c5fd; padding: 2px 7px; border-radius: 4px; border: 1px solid rgba(96,165,250,0.3); font-weight: 700; letter-spacing: 0.05em;">ROUTING</span>
                        </div>
                    </template>

                    <!-- Quick Click-to-Expand Badge -->
                    <button type="button" 
                            @click="openCenterMapModal()"
                            style="position: absolute; top: 10px; right: 10px; z-index: 400; background: rgba(255, 255, 255, 0.94); border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 700; color: #1e293b; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);"
                            title="Expand map to full centered view">
                        <i class="fa fa-arrows-alt"></i> Full View
                    </button>

                    <!-- Map Legend Overlay -->
                    <div style="position: absolute; bottom: 10px; left: 10px; z-index: 400; background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(4px); padding: 5px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 10px; color: #334155; display: flex; align-items: center; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #059669; display: inline-block;"></span> Origin (POL)
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 14px; height: 3px; background: #2563eb; display: inline-block; border-radius: 1px;"></span> Shipping Route
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #dc2626; display: inline-block;"></span> Destination (POD)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Verified Event Timeline -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Verified Event Timeline</label>
                    <span style="font-size: 11px; color: #64748b; font-weight: 500;" x-text="events.length + (events.length === 1 ? ' milestone recorded' : ' milestones recorded')"></span>
                </div>

                <div style="position: relative; padding-left: 24px;">
                    <!-- Vertical Timeline Line -->
                    <div style="position: absolute; left: 7px; top: 8px; bottom: 8px; width: 2px; background: #e2e8f0;"></div>

                    <template x-for="(evt, idx) in events" :key="idx">
                        <div style="position: relative; margin-bottom: 16px;">
                            <!-- Node Dot (Aligned exactly over 7px line) -->
                            <div style="position: absolute; left: -24px; top: 12px; width: 14px; height: 14px; border-radius: 50%; background: #ffffff; border: 2.5px solid; z-index: 2;"
                                 :style="'border-color:' + (evt.event_classifier === 'ACT' ? '#10b981' : '#3b82f6') + '; box-shadow: 0 0 0 3px ' + (evt.event_classifier === 'ACT' ? '#dcfce7' : '#dbeafe') + ';'">
                            </div>

                            <!-- Event Card -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: border-color 0.15s;"
                                 onmouseover="this.style.borderColor='#cbd5e1'" 
                                 onmouseout="this.style.borderColor='#e2e8f0'">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <strong style="font-size: 13px; color: #0f172a;" x-text="evt.title"></strong>
                                        <span style="font-size: 9px; padding: 2px 7px; border-radius: 4px; font-weight: 700; letter-spacing: 0.04em;"
                                              :style="evt.event_classifier === 'ACT' ? 'background: #dcfce7; color: #166534;' : 'background: #fef9c3; color: #854d0e;'"
                                              x-text="evt.event_classifier === 'ACT' ? 'ACTUAL' : 'ESTIMATED'">
                                        </span>
                                        <template x-if="evt.is_manual">
                                            <span style="font-size: 9px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #e0e7ff; color: #4338ca; letter-spacing: 0.04em;">OPERATOR</span>
                                        </template>
                                    </div>
                                    <span style="font-size: 11px; color: #64748b; font-family: ui-monospace, monospace; white-space: nowrap;" x-text="formatDateTime(evt.event_date_time)"></span>
                                </div>

                                <div style="font-size: 12px; color: #475569; line-height: 1.4; margin-bottom: 8px;" x-text="evt.description || 'Status update logged.'"></div>

                                <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 11px; color: #64748b; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <template x-if="evt.location_name">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-map-marker" style="color: #ef4444;"></i> 
                                            <span x-text="evt.location_name + (evt.location_locode ? ' (' + evt.location_locode + ')' : '')"></span>
                                        </span>
                                    </template>
                                    <template x-if="evt.facility_name">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-building-o" style="color: #64748b;"></i> 
                                            <span x-text="evt.facility_name"></span>
                                        </span>
                                    </template>
                                    <template x-if="evt.vessel_name">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-ship" style="color: #2563eb;"></i> 
                                            <span x-text="evt.vessel_name + (evt.voyage_no ? ' / ' + evt.voyage_no : '')"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="events.length === 0">
                        <div style="padding: 28px 20px; text-align: center; color: #94a3b8; font-size: 12px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            <i class="fa fa-clock-o" style="font-size: 20px; color: #cbd5e1; margin-bottom: 6px; display: block;"></i>
                            No milestone events found yet. Click <strong>Sync Live Tracking</strong> to fetch real-time carrier updates.
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Manual Event Modal -->
        <div x-show="showManualModal" 
             x-cloak 
             style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 100001; display: flex; align-items: center; justify-content: center;" 
             @click.self="showManualModal = false">
            <div style="background: #ffffff; width: 460px; max-width: 92vw; border-radius: 10px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); font-family: 'Inter', sans-serif;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #0f172a;">Add Manual Tracking Event</h4>
                    <button type="button" @click="showManualModal = false" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer; padding: 4px;">&times;</button>
                </div>
                <div style="padding: 18px; font-size: 12px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Milestone Title</label>
                        <input type="text" x-model="manualForm.title" placeholder="e.g. Customs Clearance Approved" style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Description / Operator Note</label>
                        <textarea x-model="manualForm.description" rows="2" placeholder="Enter additional details..." style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none;"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Date & Time</label>
                            <input type="datetime-local" x-model="manualForm.event_date_time" style="width: 100%; height: 34px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Status Type</label>
                            <select x-model="manualForm.event_classifier" style="width: 100%; height: 34px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px;">
                                <option value="ACT">Actual (Completed)</option>
                                <option value="EST">Estimated (Planned)</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Location / Terminal</label>
                        <input type="text" x-model="manualForm.location_name" placeholder="e.g. Los Angeles Pier 400" style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none;">
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" @click="showManualModal = false" style="padding: 7px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; cursor: pointer; color: #475569;">Cancel</button>
                        <button type="button" @click="submitManualEvent()" :disabled="!manualForm.title" style="padding: 7px 16px; background: #2563eb; border: none; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Save Milestone</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Centered Fullscreen Ocean Map Modal -->
    <div x-show="showCenterMapModal" 
         x-cloak 
         @resize.window.debounce.150ms="if(showCenterMapModal && centerModalMap) { centerModalMap.invalidateSize(); fitCenterModalRoute(); }"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(6px); z-index: 100020; display: flex; align-items: center; justify-content: center; margin: 0; padding: 24px; box-sizing: border-box;"
         @click.self="closeCenterMapModal()"
         @keydown.escape.window="closeCenterMapModal()">

        <div x-show="showCenterMapModal"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="background: #ffffff; width: 88vw; max-width: 1200px; height: 82vh; max-height: 820px; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); position: relative; margin: auto; font-family: 'Inter', -apple-system, sans-serif;">
            
            <!-- Modal Header -->
            <div style="padding: 14px 24px; background: #0f172a; color: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; flex-shrink: 0; z-index: 20;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 17px; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                        <i class="fa fa-ship"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">
                                Ocean Shipping Route Map
                            </h3>
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; background: #1e293b; color: #93c5fd; border: 1px solid #3b82f6; font-family: monospace;" x-text="trackingNumber"></span>
                            <span style="font-size: 10px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; text-transform: uppercase;"
                                  :style="'background:' + (statusBadge.bg || '#eff6ff') + '; color:' + (statusBadge.color || '#1e40af') + '; border: 1px solid ' + (statusBadge.color ? statusBadge.color + '44' : '#bfdbfe') + ';'"
                                  x-text="statusBadge.label || 'TRACKING'"></span>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 3px; display: flex; align-items: center; gap: 12px;">
                            <span>Carrier: <strong style="color: #f1f5f9;" x-text="formatCarrierName()"></strong></span>
                            <span>•</span>
                            <span>POL: <strong style="color: #34d399;" x-text="getOriginLabel()"></strong> &rarr; POD: <strong style="color: #f87171;" x-text="getDestinationLabel()"></strong></span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" 
                            @click="fitCenterModalRoute()" 
                            style="padding: 6px 14px; background: #1e293b; border: 1px solid #334155; color: #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s;"
                            onmouseover="this.style.background='#334155'; this.style.color='#fff';"
                            onmouseout="this.style.background='#1e293b'; this.style.color='#cbd5e1';"
                            title="Center map on entire route">
                        <i class="fa fa-crosshairs"></i> Fit Route
                    </button>
                    <button type="button" 
                            @click="closeCenterMapModal()" 
                            style="width: 34px; height: 34px; border-radius: 6px; background: #1e293b; border: 1px solid #334155; color: #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; transition: all 0.15s;"
                            onmouseover="this.style.background='#ef4444'; this.style.color='#fff'; this.style.borderColor='#ef4444';"
                            onmouseout="this.style.background='#1e293b'; this.style.color='#cbd5e1'; this.style.borderColor='#334155';"
                            title="Close Modal (Esc)">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Map Body -->
            <div style="flex: 1 1 0%; min-height: 420px; position: relative; background: #e2e8f0; overflow: hidden; width: 100%;">
                <div id="center-modal-ocean-map" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; z-index: 1;"></div>

                <!-- Floating Route Info Card (Top-Left) -->
                <div style="position: absolute; top: 16px; left: 16px; z-index: 500; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); max-width: 360px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fa fa-map-signs"></i> Shipping Route Corridor
                        </span>
                        <span style="font-size: 10px; background: #eff6ff; color: #1d4ed8; padding: 2px 7px; border-radius: 4px; font-weight: 700; text-transform: uppercase;" 
                              x-text="statusBadge.label || 'SCHEDULED ROUTE'">
                        </span>
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px;" x-text="getVesselLabel()"></div>
                    <div style="font-size: 11px; color: #64748b; margin-bottom: 10px;" x-text="trackingData.voyage_no ? 'Voyage: ' + trackingData.voyage_no : 'Active Voyage'"></div>
                    
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px;">
                        <div>
                            <span style="color: #059669; font-weight: 700; display: block; font-size: 10px;">ORIGIN (POL)</span>
                            <strong style="color: #1e293b;" x-text="getOriginLabel()"></strong>
                            <div style="color: #64748b; font-size: 10px; margin-top: 2px;" x-text="trackingData.etd ? 'ETD: ' + formatDate(trackingData.etd) : 'ETD TBA'"></div>
                        </div>
                        <div>
                            <span style="color: #dc2626; font-weight: 700; display: block; font-size: 10px;">DESTINATION (POD)</span>
                            <strong style="color: #1e293b;" x-text="getDestinationLabel()"></strong>
                            <div style="color: #059669; font-size: 10px; margin-top: 2px;" x-text="trackingData.eta ? 'ETA: ' + formatDate(trackingData.eta) : 'ETA TBA'"></div>
                        </div>
                    </div>
                </div>

                <!-- Floating Legend Overlay (Bottom-Left) -->
                <div style="position: absolute; bottom: 16px; left: 16px; z-index: 500; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 11px; color: #334155; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #059669; display: inline-block;"></span> <strong>Origin Port (POL)</strong>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 18px; height: 4px; background: #2563eb; display: inline-block; border-radius: 2px;"></span> <strong>Shipping Route</strong>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #dc2626; display: inline-block;"></span> <strong>Destination Port (POD)</strong>
                    </span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 12px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; flex-shrink: 0; z-index: 20;">
                <div style="display: flex; align-items: center; gap: 8px; color: #64748b;">
                    <i class="fa fa-clock-o"></i>
                    <span>Last Synchronized: <strong style="color: #334155;" x-text="trackingData.last_polled_at ? formatDateTime(trackingData.last_polled_at) : 'Just now'"></strong></span>
                </div>
                <button type="button" 
                        @click="closeCenterMapModal()" 
                        style="padding: 7px 20px; background: #2563eb; border: none; border-radius: 6px; font-weight: 600; color: #ffffff; cursor: pointer; box-shadow: 0 1px 3px rgba(37,99,235,0.3); transition: background 0.15s;"
                        onmouseover="this.style.background='#1d4ed8';"
                        onmouseout="this.style.background='#2563eb';">
                    Close Map
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function carrierTrackingDrawer() {
    return {
        isOpen: false,
        isSyncing: false,
        copied: false,
        trackingNumber: '',
        selectedCarrier: '',
        shipmentId: null,
        shipmentType: null,
        availableContainers: [],
        trackingData: {},
        statusBadge: {},
        events: [],
        showManualModal: false,
        showCenterMapModal: false,
        leafletMap: null,
        centerModalMap: null,
        manualForm: {
            title: '',
            description: '',
            event_date_time: new Date().toISOString().slice(0, 16),
            event_classifier: 'ACT',
            location_name: ''
        },

        steps: [
            { label: 'Booking', icon: 'fa-file-text-o' },
            { label: 'Gate In', icon: 'fa-truck' },
            { label: 'Loaded', icon: 'fa-ship' },
            { label: 'In Transit', icon: 'fa-compass' },
            { label: 'Discharged', icon: 'fa-anchor' },
            { label: 'Delivered', icon: 'fa-check-circle' }
        ],

        initDrawer() {
            window.carrierTrackingDrawerInstance = this;
            window.openLiveTracking = (opts) => this.openTracking(opts);
            this.$watch('isOpen', (open) => {
                if (open) {
                    this.renderMap();
                }
            });
        },

        openTracking(opts = {}) {
            this.trackingNumber = (opts.number || opts.trackingNumber || '').trim();
            this.selectedCarrier = opts.carrier && opts.carrier !== 'shipmentbeat' ? opts.carrier : '';
            this.shipmentId = opts.shipmentId || null;
            this.shipmentType = opts.shipmentType || null;
            this.availableContainers = opts.containers || (this.trackingNumber ? [this.trackingNumber] : []);
            this.copied = false;
            this.isOpen = true;

            if (this.trackingNumber || this.shipmentId) {
                this.loadDetails();
            }
        },

        close() {
            this.isOpen = false;
        },

        currentStepIndex() {
            const status = (this.trackingData.current_status || '').toLowerCase();
            if (status === 'delivered') return 5;
            if (status === 'gate_out' || status === 'discharged') return 4;
            if (status === 'in_transit') return 3;
            if (status === 'loaded') return 2;
            if (status === 'gate_in') return 1;
            return 0; // booked or default
        },

        stepSummaryText() {
            const idx = this.currentStepIndex();
            return (idx + 1) + ' of 6 stages reached';
        },

        formatCarrierName() {
            const raw = this.trackingData.raw_payload || {};
            const carrierName = raw.carrierName || raw.carrierCode || this.trackingData.carrier_code || 'Carrier';
            if (carrierName.toUpperCase() === 'SHIPMENTBEAT') {
                return 'ShipmentBeat Live Feed';
            }
            return carrierName + ' via ShipmentBeat';
        },

        getOriginLabel() {
            return (this.trackingData.map_data && this.trackingData.map_data.origin && this.trackingData.map_data.origin.label)
                || this.trackingData.origin_name 
                || 'Origin Terminal';
        },

        getDestinationLabel() {
            return (this.trackingData.map_data && this.trackingData.map_data.destination && this.trackingData.map_data.destination.label)
                || this.trackingData.destination_name 
                || 'Port of Discharge';
        },

        getVesselLabel() {
            return (this.trackingData.map_data && this.trackingData.map_data.vessel && this.trackingData.map_data.vessel.name)
                || this.trackingData.vessel_name 
                || (this.formatCarrierName() + ' Vessel');
        },

        validateNumber(number) {
            const raw = (number || '').trim().toUpperCase();
            if (!raw) {
                return { valid: false, message: 'Please enter a container number or Master B/L.' };
            }

            // Pure letters with no digits (e.g. "jhdhsj")
            if (/^[A-Z]+$/.test(raw)) {
                return {
                    valid: false,
                    message: `Invalid input "${raw}". Container numbers require numeric digits after the carrier prefix (e.g., MEDU4387708).`
                };
            }

            // Minimum length check
            if (raw.length < 8) {
                return { 
                    valid: false, 
                    message: `Invalid format "${raw}". Container numbers must be 4 letters followed by 6 or 7 digits (e.g. MEDU4387708).` 
                };
            }

            // Standard container format (4 letters + 6 or 7 digits) or alphanumeric B/L
            const containerPattern = /^[A-Z]{4}[0-9]{6,7}$/;
            const blPattern = /^[A-Z0-9]{8,20}$/;

            if (!containerPattern.test(raw) && !blPattern.test(raw)) {
                return {
                    valid: false,
                    message: `Invalid tracking number "${raw}". Please enter a valid 11-character container number (e.g., MEDU4387708) or standard B/L.`
                };
            }

            // ISO 6346 Container Check-Digit Validation
            if (/^[A-Z]{4}[0-9]{7}$/.test(raw) && window.iso6346Validator) {
                const v = window.iso6346Validator.validate(raw);
                if (v && (v.status === 'mismatch' || v.status === 'invalid')) {
                    return {
                        valid: false,
                        message: `Check-digit mismatch for "${raw}". Expected last digit is ${v.expectedDigit}, but "${v.enteredDigit}" was entered (Suggested: ${v.suggestedValue || ''}).`
                    };
                }
            }

            return { valid: true, clean: raw };
        },

        loadDetails() {
            const raw = (this.trackingNumber || '').trim();
            if (raw) {
                const check = this.validateNumber(raw);
                if (!check.valid) {
                    if (typeof showToast === 'function') {
                        showToast('error', check.message);
                    }
                    return;
                }
                this.trackingNumber = check.clean;
            }

            this.isSyncing = true;
            fetch('{{ route("api.tracking.details") }}?tracking_number=' + encodeURIComponent(this.trackingNumber) + '&shipment_id=' + (this.shipmentId || ''))
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.tracking) {
                        this.trackingData = res.tracking;
                        this.statusBadge = res.status_badge || {};
                        this.events = res.events || [];
                        if (res.tracking.carrier_code && res.tracking.carrier_code !== 'shipmentbeat' && !this.selectedCarrier) {
                            this.selectedCarrier = res.tracking.carrier_code;
                        }
                        this.renderMap();
                    } else {
                        // If not found in DB, auto-trigger live carrier sync
                        this.syncLiveTracking();
                    }
                })
                .catch(() => {
                    this.syncLiveTracking();
                })
                .finally(() => {
                    this.isSyncing = false;
                });
        },

        syncLiveTracking() {
            const raw = (this.trackingNumber || '').trim();
            if (!raw) return;

            const check = this.validateNumber(raw);
            if (!check.valid) {
                if (typeof showToast === 'function') {
                    showToast('error', check.message);
                } else {
                    alert(check.message);
                }
                return; // STOP: Prevents unnecessary API calls
            }

            this.trackingNumber = check.clean;
            this.isSyncing = true;

            fetch('{{ route("api.tracking.sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tracking_number: this.trackingNumber,
                    carrier_code: this.selectedCarrier || '',
                    shipment_id: this.shipmentId,
                    shipment_type: this.shipmentType
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.tracking) {
                    this.trackingData = res.tracking;
                    this.statusBadge = res.status_badge || {};
                    this.events = res.events || [];
                    if (res.tracking.carrier_code && res.tracking.carrier_code !== 'shipmentbeat') {
                        this.selectedCarrier = res.tracking.carrier_code;
                    }
                    this.renderMap();
                    if (typeof showToast === 'function') {
                        showToast('success', res.message || 'Tracking updated successfully.');
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('error', res.message || 'Tracking sync failed.');
                    }
                }
            })
            .catch(err => {
                if (typeof showToast === 'function') {
                    showToast('error', 'Error syncing tracking with carrier.');
                }
            })
            .finally(() => {
                this.isSyncing = false;
            });
        },

        renderMap() {
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initOrUpdateMap();
                    if (this.showCenterMapModal) {
                        this.initCenterModalMap();
                    }
                }, 150);
                setTimeout(() => {
                    if (this.leafletMap) this.leafletMap.invalidateSize();
                    if (this.centerModalMap) this.centerModalMap.invalidateSize();
                }, 400);
                setTimeout(() => {
                    if (this.leafletMap) this.leafletMap.invalidateSize();
                    if (this.centerModalMap) this.centerModalMap.invalidateSize();
                }, 800);
            });
        },

        initOrUpdateMap() {
            const mapContainer = document.getElementById('drawer-ocean-map');
            if (!mapContainer) return;

            if (typeof L === 'undefined') {
                setTimeout(() => this.initOrUpdateMap(), 200);
                return;
            }

            if (!this.leafletMap) {
                if (mapContainer._leaflet_id) {
                    delete mapContainer._leaflet_id;
                }
                this.leafletMap = L.map('drawer-ocean-map', {
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: false,
                });

                // OpenStreetMap TileLayer - Free, reliable, NO API KEY required
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.leafletMap);

                L.control.zoom({ position: 'topright' }).addTo(this.leafletMap);
            } else {
                this.leafletMap.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) return;
                    this.leafletMap.removeLayer(layer);
                });
            }

            this.leafletMap.invalidateSize();

            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.leafletMap.setView([20, 0], 2);
                return;
            }

            const origin = mapData.origin;
            const dest = mapData.destination;
            const vessel = mapData.vessel;
            const waypoints = mapData.waypoints || [];

            // 1. Draw Nautical Shipping Route Corridor
            if (waypoints.length > 1) {
                // Wide semi-transparent glow corridor
                L.polyline(waypoints, {
                    color: '#0284c7',
                    weight: 6,
                    opacity: 0.25,
                    lineCap: 'round',
                }).addTo(this.leafletMap);

                // Crisp dashed shipping lane
                L.polyline(waypoints, {
                    color: '#2563eb',
                    weight: 2.5,
                    dashArray: '5, 8',
                    opacity: 0.85,
                    lineCap: 'round',
                }).addTo(this.leafletMap)
                  .bindPopup(`<strong>Shipping Route:</strong> ${origin.label} &rarr; ${dest.label}`);
            }

            // 2. Origin Port Marker (POL)
            const polIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="port-marker-pin port-pol-pin" title="Origin Port: ${origin.label}"><i class="fa fa-map-marker"></i></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
            });
            L.marker([origin.lat, origin.lng], { icon: polIcon })
                .addTo(this.leafletMap)
                .bindPopup(`
                    <div style="font-family: 'Inter', sans-serif; font-size: 12px; min-width: 170px;">
                        <span style="color: #059669; font-size: 10px; font-weight: 800; text-transform: uppercase;">Port of Loading (POL)</span>
                        <div style="font-weight: 700; color: #1e293b; margin-top: 3px;">${origin.label}</div>
                        ${origin.code ? `<div style="font-size: 10px; font-family: monospace; color: #64748b;">UN/LOCODE: ${origin.code}</div>` : ''}
                        ${origin.date ? `<div style="font-size: 11px; color: #475569; margin-top: 5px;"><i class="fa fa-calendar"></i> ETD: ${origin.date}</div>` : ''}
                    </div>
                `);

            // 3. Destination Port Marker (POD)
            const podIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="port-marker-pin port-pod-pin" title="Destination Port: ${dest.label}"><i class="fa fa-anchor"></i></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
            });
            L.marker([dest.lat, dest.lng], { icon: podIcon })
                .addTo(this.leafletMap)
                .bindPopup(`
                    <div style="font-family: 'Inter', sans-serif; font-size: 12px; min-width: 170px;">
                        <span style="color: #dc2626; font-size: 10px; font-weight: 800; text-transform: uppercase;">Port of Discharge (POD)</span>
                        <div style="font-weight: 700; color: #1e293b; margin-top: 3px;">${dest.label}</div>
                        ${dest.code ? `<div style="font-size: 10px; font-family: monospace; color: #64748b;">UN/LOCODE: ${dest.code}</div>` : ''}
                        ${dest.date ? `<div style="font-size: 11px; color: #059669; font-weight: 600; margin-top: 5px;"><i class="fa fa-calendar"></i> ETA: ${dest.date}</div>` : ''}
                    </div>
                `);

            // Auto-fit route bounds with padding
            let bounds = L.latLngBounds();
            if (Array.isArray(waypoints) && waypoints.length > 0) {
                if (Array.isArray(waypoints[0]) && Array.isArray(waypoints[0][0])) {
                    waypoints.forEach(leg => leg.forEach(pt => bounds.extend(pt)));
                } else {
                    waypoints.forEach(pt => bounds.extend(pt));
                }
            } else {
                bounds = L.latLngBounds([
                    [origin.lat, origin.lng],
                    [dest.lat, dest.lng]
                ]);
            }
            bounds.extend([origin.lat, origin.lng]);
            bounds.extend([dest.lat, dest.lng]);
            this.leafletMap.fitBounds(bounds, { padding: [35, 35] });
        },

        fitMapRoute() {
            if (!this.leafletMap || !this.trackingData || !this.trackingData.map_data) return;
            this.leafletMap.invalidateSize();
            const mapData = this.trackingData.map_data;
            const waypoints = mapData.waypoints || [];
            let bounds = L.latLngBounds();
            if (Array.isArray(waypoints) && waypoints.length > 0) {
                if (Array.isArray(waypoints[0]) && Array.isArray(waypoints[0][0])) {
                    waypoints.forEach(leg => leg.forEach(pt => bounds.extend(pt)));
                } else {
                    waypoints.forEach(pt => bounds.extend(pt));
                }
            } else {
                bounds = L.latLngBounds([
                    [mapData.origin.lat, mapData.origin.lng],
                    [mapData.destination.lat, mapData.destination.lng]
                ]);
            }
            bounds.extend([mapData.origin.lat, mapData.origin.lng]);
            bounds.extend([mapData.destination.lat, mapData.destination.lng]);
            this.leafletMap.fitBounds(bounds, { padding: [35, 35] });
            this.leafletMap.fitBounds(bounds, { padding: [40, 40] });
        },

        openCenterMapModal() {
            this.showCenterMapModal = true;
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initCenterModalMap();
                }, 50);
                setTimeout(() => {
                    if (this.centerModalMap) {
                        this.centerModalMap.invalidateSize();
                        this.fitCenterModalRoute();
                    }
                }, 200);
                setTimeout(() => {
                    if (this.centerModalMap) {
                        this.centerModalMap.invalidateSize();
                        this.fitCenterModalRoute();
                    }
                }, 400);
                setTimeout(() => {
                    if (this.centerModalMap) {
                        this.centerModalMap.invalidateSize();
                        this.fitCenterModalRoute();
                    }
                }, 700);
            });
        },

        closeCenterMapModal() {
            this.showCenterMapModal = false;
        },

        fitCenterModalRoute() {
            if (!this.centerModalMap) return;
            this.centerModalMap.invalidateSize();
            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.centerModalMap.setView([20, 0], 2);
                return;
            }
            const origin = mapData.origin;
            const dest = mapData.destination;
            const waypoints = mapData.waypoints || [];
            let bounds = L.latLngBounds();
            if (Array.isArray(waypoints) && waypoints.length > 0) {
                if (Array.isArray(waypoints[0]) && Array.isArray(waypoints[0][0])) {
                    waypoints.forEach(leg => leg.forEach(pt => bounds.extend(pt)));
                } else {
                    waypoints.forEach(pt => bounds.extend(pt));
                }
            } else {
                bounds = L.latLngBounds([
                    [origin.lat, origin.lng],
                    [dest.lat, dest.lng]
                ]);
            }
            bounds.extend([origin.lat, origin.lng]);
            bounds.extend([dest.lat, dest.lng]);
            this.centerModalMap.fitBounds(bounds, { padding: [60, 60] });
        },

        initCenterModalMap() {
            const container = document.getElementById('center-modal-ocean-map');
            if (!container) return;

            if (typeof L === 'undefined') {
                setTimeout(() => this.initCenterModalMap(), 150);
                return;
            }

            if (!this.centerModalMap) {
                if (container._leaflet_id) {
                    delete container._leaflet_id;
                }
                this.centerModalMap = L.map('center-modal-ocean-map', {
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: true,
                });

                // OpenStreetMap TileLayer - Free, reliable, NO API KEY required
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.centerModalMap);

                L.control.zoom({ position: 'topright' }).addTo(this.centerModalMap);
            } else {
                this.centerModalMap.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) return;
                    this.centerModalMap.removeLayer(layer);
                });
            }

            this.centerModalMap.invalidateSize();

            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.centerModalMap.setView([20, 0], 2);
                return;
            }

            const origin = mapData.origin;
            const dest = mapData.destination;
            const vessel = mapData.vessel;
            const waypoints = mapData.waypoints || [];

            // 1. Polyline Route Corridor
            if (waypoints.length > 1) {
                L.polyline(waypoints, {
                    color: '#0284c7',
                    weight: 7,
                    opacity: 0.25,
                    lineCap: 'round',
                }).addTo(this.centerModalMap);

                L.polyline(waypoints, {
                    color: '#2563eb',
                    weight: 3,
                    dashArray: '6, 10',
                    opacity: 0.9,
                    lineCap: 'round',
                }).addTo(this.centerModalMap);
            }

            // 2. Origin marker
            const polIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="port-marker-pin port-pol-pin" style="width: 28px; height: 28px; font-size: 13px;" title="Origin Port: ${origin.label}"><i class="fa fa-map-marker"></i></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
            L.marker([origin.lat, origin.lng], { icon: polIcon })
                .addTo(this.centerModalMap)
                .bindPopup(`
                    <div style="font-family: 'Inter', sans-serif; font-size: 13px; min-width: 190px;">
                        <span style="color: #059669; font-size: 10px; font-weight: 800; text-transform: uppercase;">Port of Loading (POL)</span>
                        <div style="font-weight: 700; color: #1e293b; margin-top: 3px;">${origin.label}</div>
                        ${origin.code ? `<div style="font-size: 11px; font-family: monospace; color: #64748b;">UN/LOCODE: ${origin.code}</div>` : ''}
                        ${origin.date ? `<div style="font-size: 11px; color: #475569; margin-top: 5px;"><i class="fa fa-calendar"></i> ETD: ${origin.date}</div>` : ''}
                    </div>
                `);

            // 3. Destination marker
            const podIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="port-marker-pin port-pod-pin" style="width: 28px; height: 28px; font-size: 13px;" title="Destination Port: ${dest.label}"><i class="fa fa-anchor"></i></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
            L.marker([dest.lat, dest.lng], { icon: podIcon })
                .addTo(this.centerModalMap)
                .bindPopup(`
                    <div style="font-family: 'Inter', sans-serif; font-size: 13px; min-width: 190px;">
                        <span style="color: #dc2626; font-size: 10px; font-weight: 800; text-transform: uppercase;">Port of Discharge (POD)</span>
                        <div style="font-weight: 700; color: #1e293b; margin-top: 3px;">${dest.label}</div>
                        ${dest.code ? `<div style="font-size: 11px; font-family: monospace; color: #64748b;">UN/LOCODE: ${dest.code}</div>` : ''}
                        ${dest.date ? `<div style="font-size: 11px; color: #059669; font-weight: 600; margin-top: 5px;"><i class="fa fa-calendar"></i> ETA: ${dest.date}</div>` : ''}
                    </div>
                `);

            let bounds = L.latLngBounds();
            if (Array.isArray(waypoints) && waypoints.length > 0) {
                if (Array.isArray(waypoints[0]) && Array.isArray(waypoints[0][0])) {
                    waypoints.forEach(leg => leg.forEach(pt => bounds.extend(pt)));
                } else {
                    waypoints.forEach(pt => bounds.extend(pt));
                }
            } else {
                bounds = L.latLngBounds([
                    [origin.lat, origin.lng],
                    [dest.lat, dest.lng]
                ]);
            }
            bounds.extend([origin.lat, origin.lng]);
            bounds.extend([dest.lat, dest.lng]);
            this.centerModalMap.fitBounds(bounds, { padding: [60, 60] });
        },

        submitManualEvent() {
            if (!this.trackingData.id) {
                alert('Please sync tracking first.');
                return;
            }

            fetch('{{ route("api.tracking.manual-event") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    shipment_tracking_id: this.trackingData.id,
                    ...this.manualForm
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.events = res.events || [];
                    this.showManualModal = false;
                    this.manualForm.title = '';
                    this.manualForm.description = '';
                    if (typeof showToast === 'function') {
                        showToast('success', 'Milestone note logged.');
                    }
                }
            });
        },

        copyNumber() {
            if (this.trackingNumber) {
                navigator.clipboard.writeText(this.trackingNumber);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            }
        },

        formatDate(val) {
            if (!val) return '';
            try { return new Date(val).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); } catch(e) { return val; }
        },

        formatDateTime(val) {
            if (!val) return '';
            try { 
                const d = new Date(val);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch(e) { return val; }
        },

        formatTime(val) {
            if (!val) return '';
            try { return new Date(val).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); } catch(e) { return val; }
        }
    };
}
</script>
@endif
