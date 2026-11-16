<x-layout>
@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<style>
    @keyframes vesselRadarPulse {
        0% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.75); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 16px rgba(37, 99, 235, 0); }
        100% { transform: scale(0.92); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    @keyframes liveBeaconPulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    @keyframes linearBarAnim {
        0% { left: -45%; width: 45%; }
        50% { left: 30%; width: 50%; }
        100% { left: 100%; width: 45%; }
    }
    .live-beacon {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        animation: liveBeaconPulse 2s infinite ease-in-out;
    }
    .tracking-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
    }
    .tracking-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }
    .maritime-pin-badge {
        width: 32px;
        height: 32px;
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
        background: rgba(37, 99, 235, 0.22);
        border: 1.5px solid rgba(37, 99, 235, 0.35);
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
    .track-live-btn {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 0 18px;
        height: 38px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(37,99,235,0.3);
        transition: all 0.15s ease;
    }
    .track-live-btn:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        box-shadow: 0 4px 10px rgba(37,99,235,0.4);
        transform: translateY(-1px);
    }
    .custom-leaflet-div { background: transparent !important; border: none !important; }

    /* FindTEU Matching High-End Leaflet Popup */
    .leaflet-popup-content-wrapper {
        background: #ffffff !important;
        border-radius: 8px !important;
        padding: 4px 6px !important;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.14) !important;
        border: 1px solid #e2e8f0 !important;
    }
    .leaflet-popup-content {
        margin: 8px 12px !important;
        line-height: 1.4 !important;
    }
    .leaflet-popup-tip {
        background: #ffffff !important;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08) !important;
    }
    .leaflet-container a.leaflet-popup-close-button {
        top: 6px !important;
        right: 8px !important;
        color: #94a3b8 !important;
        font: 16px/14px Tahoma, Verdana, sans-serif !important;
    }
    .leaflet-container a.leaflet-popup-close-button:hover {
        color: #0f172a !important;
    }
</style>
@endonce

<div x-data="trackingCenterPage()" 
     x-init="initPage()"
     style="padding: 24px 28px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; min-height: 100vh;">
    
    <!-- Top Hero Header & Quick Search Bar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 24px; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 800; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 5px;">
                    <span class="live-beacon"></span> LIVE MARITIME RADAR
                </span>
                <span style="font-size: 11px; color: #64748b; font-weight: 500;">
                    Carrier Telemetry Active
                </span>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.02em; display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-ship" style="color: #2563eb; font-size: 20px;"></i> Carrier Track &amp; Trace Center
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.4;">
                Unified multi-carrier ocean container visibility, milestone tracking &amp; nautical route corridors powered by ShipmentBeat.
            </p>
        </div>

        <!-- Quick Instant Track Form Component -->
        <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 4px 4px 4px 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
            <i class="fa fa-search" style="color: #94a3b8; font-size: 13px;"></i>
            <input type="text" 
                   id="quick-track-input" 
                   value="{{ request('search') }}"
                   placeholder="ENTER CONTAINER NO / MASTER B/L..." 
                   onkeyup="if(event.key === 'Enter') handleQuickTrack()"
                   style="height: 38px; border: none; background: transparent; font-size: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 700; letter-spacing: 0.04em; min-width: 270px; outline: none; color: #0f172a;">
            <span style="font-size: 10px; font-weight: 700; color: #64748b; background: #e2e8f0; border-radius: 4px; padding: 3px 6px; font-family: monospace;">↵ ENTER</span>
            <button type="button" 
                    onclick="handleQuickTrack()"
                    class="track-live-btn">
                <i class="fa fa-crosshairs"></i> Trace Container
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- DEDICATED IN-PAGE LIVE TRACKING & OCEAN ROUTE SECTION    -->
    <!-- (Front & Center: Renders directly under Quick Search!)    -->
    <!-- ======================================================== -->
    <div id="inpage-tracking-section"
         x-show="activeTrackingNumber" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08); overflow: hidden; margin-bottom: 24px; scroll-margin-top: 100px;">

        <!-- Section Top Action & Control Bar (Matches FindTEU Branding Header) -->
        <div style="padding: 16px 24px; background: #ffffff; color: #0f172a; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; border-bottom: 1.5px solid #e2e8f0;">
            <!-- Left Info Cluster: Carrier Badge + Container No + Type + Route with Flags -->
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <!-- Carrier Badge (Yellow MSC square or carrier branding) -->
                <div style="width: 42px; height: 42px; border-radius: 8px; font-weight: 900; font-size: 15px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.12); text-transform: lowercase; letter-spacing: -0.02em;"
                     :style="'background:' + (getCarrierBadge().bg || '#ffcc00') + '; color:' + (getCarrierBadge().color || '#000000') + '; border: 1.5px solid ' + (getCarrierBadge().border || '#eab308') + ';'"
                     x-text="getCarrierBadge().short || 'msc'">
                </div>

                <!-- Container Number + CN -->
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span style="font-size: 19px; font-weight: 800; color: #0f172a; letter-spacing: -0.01em; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" x-text="activeTrackingNumber"></span>
                    <span style="font-size: 13px; font-weight: 800; color: #64748b;">CN</span>
                </div>

                <!-- Container Type & Size Badge (e.g. 40' General Purpose) -->
                <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; font-weight: 600; background: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 12px; border-radius: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span x-text="getContainerType()"></span>
                </div>

                <!-- Origin Flag & City ➔ Destination Flag & City -->
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #0f172a;">
                    <span x-text="getRouteDisplay().label"></span>
                </div>
            </div>

            <!-- Right Controls: Auto-Detect Carrier, Sync, Notes, Fullscreen, Close -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <select x-model="selectedCarrier" 
                        @change="syncLiveTracking()"
                        title="Carrier Shipping Line"
                        style="height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ffffff; color: #334155; outline: none; cursor: pointer;">
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

                <button type="button" 
                        @click="syncLiveTracking()" 
                        :disabled="isSyncing"
                        style="height: 34px; padding: 0 14px; background: #2563eb; border: none; color: #fff; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(37,99,235,0.3); transition: background 0.15s;"
                        onmouseover="if(!this.disabled) this.style.background='#1d4ed8'"
                        onmouseout="if(!this.disabled) this.style.background='#2563eb'">
                    <i class="fa" :class="isSyncing ? 'fa-spinner fa-spin' : 'fa-refresh'"></i>
                    <span x-text="isSyncing ? 'Syncing...' : 'Sync Live Tracking'"></span>
                </button>

                <button type="button" 
                        @click="showManualModal = true"
                        style="height: 34px; padding: 0 10px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa fa-plus"></i> Add Note
                </button>

                <button type="button" 
                        @click="openCenterMapModal()"
                        title="Open fullscreen map"
                        style="height: 34px; padding: 0 10px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa fa-arrows-alt"></i> Fullscreen
                </button>

                <button type="button" 
                        @click="closeInPageSection()"
                        title="Close in-page section"
                        style="width: 34px; height: 34px; border-radius: 6px; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 15px; transition: all 0.15s;"
                        onmouseover="this.style.background='#ef4444'; this.style.color='#fff'; this.style.borderColor='#ef4444';"
                        onmouseout="this.style.background='#ffffff'; this.style.color='#64748b'; this.style.borderColor='#cbd5e1';">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Syncing Linear Progress Bar -->
        <div x-show="isSyncing" style="height: 3px; background: #e0e7ff; overflow: hidden; position: relative;">
            <div style="height: 100%; background: #2563eb; width: 45%; position: absolute; border-radius: 2px; animation: linearBarAnim 1.3s infinite ease-in-out;"></div>
        </div>

        <!-- Inside Body of In-Page Section: Split View (Journey & ETA on Left, Maritime Map on Right) -->
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: stretch;">
                
                <!-- Left Panel: ETA & Vertical Stepper (Exact FindTEU Layout with Zero Overlap!) -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <!-- ETA Header -->
                        <div style="margin-bottom: 24px;">
                            <div style="font-size: 12px; font-weight: 700; color: #64748b; letter-spacing: 0.04em;" x-text="getEtaDisplay().label"></div>
                            <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 3px; letter-spacing: -0.02em;" x-text="getEtaDisplay().date"></div>
                        </div>

                        <!-- Vertical Stepper List (Flexbox Layout: Zero Text Overlap!) -->
                        <div style="position: relative; padding: 6px 0;">
                            <!-- Vertical continuous connecting track line -->
                            <div style="position: absolute; left: 14px; top: 14px; bottom: 22px; width: 2px; background: #e2e8f0; z-index: 1;"></div>

                            <template x-for="(st, sIdx) in getJourneySteps()" :key="sIdx">
                                <div style="display: flex; align-items: flex-start; gap: 16px; position: relative; z-index: 2; margin-bottom: 24px;">
                                    
                                    <!-- Marker Column: 30px width centered on line at 14px -->
                                    <div style="width: 30px; display: flex; justify-content: center; align-items: center; flex-shrink: 0; padding-top: 2px;">
                                        <template x-if="st.type === 'vessel'">
                                            <div style="width: 30px; height: 30px; border-radius: 50%; background: #ea580c; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; box-shadow: 0 0 0 5px #ffedd5;">
                                                <i class="fa fa-ship"></i>
                                            </div>
                                        </template>
                                        <template x-if="st.type !== 'vessel' && st.status === 'completed'">
                                            <div style="width: 16px; height: 16px; border-radius: 50%; border: 2.5px solid #f97316; background: #ffffff; box-shadow: 0 0 0 3px #ffffff;"></div>
                                        </template>
                                        <template x-if="st.type !== 'vessel' && st.status !== 'completed'">
                                            <div style="width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd5e1; background: #ffffff; box-shadow: 0 0 0 3px #ffffff;"></div>
                                        </template>
                                    </div>

                                    <!-- Text Column: Separated cleanly with no overlap -->
                                    <div style="flex: 1; min-width: 0;">
                                        <template x-if="st.type === 'vessel'">
                                            <div style="font-size: 14px; font-weight: 800; color: #ea580c; line-height: 1.4; padding-top: 5px;" x-text="st.label"></div>
                                        </template>

                                        <template x-if="st.type !== 'vessel'">
                                            <div>
                                                <div style="font-size: 11px; font-weight: 600; color: #64748b; line-height: 1.2; text-transform: capitalize;" x-text="st.label"></div>
                                                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                                                    <span x-text="st.flag" style="font-size: 16px;"></span>
                                                    <span x-text="st.city"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Info (Status & Polled timestamp) -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 12px; font-size: 11px; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
                        <span>Status: <strong style="color: #0f172a; text-transform: uppercase;" x-text="(statusBadge.label || 'BOOKED')"></strong></span>
                        <span><i class="fa fa-clock-o"></i> <span x-text="getLastPolledText()"></span></span>
                    </div>
                </div>

                <!-- Right Panel: Maritime Route Map (Esri World Light Gray Base, POL/POD Badges, Auto-opened POD Popup, Vessel Navigation Arrow) -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative; min-height: 560px;">
                    <div id="inpage-ocean-map" style="width: 100%; height: 100%; min-height: 560px;"></div>

                    <!-- Map Legend Overlay -->
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

            <!-- Below Split: Collapsible Carrier Milestones & Verified Audit Trail -->
            <div x-data="{ showAuditSection: false }" style="margin-top: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" 
                            @click="showAuditSection = !showAuditSection"
                            style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; font-size: 11px; font-weight: 700; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa" :class="showAuditSection ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        <span x-text="showAuditSection ? 'Hide Carrier Milestone Audit & Timeline' : 'View Detailed Carrier Milestones & Timeline (' + events.length + ' milestones)'"></span>
                    </button>
                    <span style="font-size: 11px; color: #64748b;" x-text="stepSummaryText()"></span>
                </div>

                <!-- Collapsible Content -->
                <div x-show="showAuditSection" x-cloak style="margin-top: 14px;">
                    <!-- 6-Stage Stepper -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 16px 14px 16px; margin-bottom: 16px;">
                        <div style="position: relative; width: 100%;">
                            <div style="position: absolute; left: 8.333%; width: 83.333%; top: 16px; height: 3px; background: #e2e8f0; z-index: 1; border-radius: 9999px;"></div>
                            <div style="position: absolute; left: 8.333%; top: 16px; height: 3px; background: #2563eb; z-index: 2; border-radius: 9999px; transition: width 0.35s ease;" 
                                 :style="'width: ' + ((currentStepIndex() / 5) * 83.333) + '%'"></div>

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
                                        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s ease;"
                                             :style="{{ $sIdx }} < currentStepIndex() ? 'background: #2563eb; color: #ffffff;' : ({{ $sIdx }} === currentStepIndex() ? 'background: #2563eb; color: #ffffff; box-shadow: 0 0 0 4px #dbeafe;' : 'background: #ffffff; border: 2px solid #cbd5e1; color: #94a3b8;')">
                                            <template x-if="{{ $sIdx }} < currentStepIndex()">
                                                <i class="fa fa-check" style="font-weight: 700;"></i>
                                            </template>
                                            <template x-if="{{ $sIdx }} >= currentStepIndex()">
                                                <i class="fa {{ $st['icon'] }}"></i>
                                            </template>
                                        </div>
                                        <span style="font-size: 11px; margin-top: 8px; font-weight: 600; line-height: 1.25; text-align: center;" 
                                              :style="{{ $sIdx }} <= currentStepIndex() ? 'color: #0f172a; font-weight: 700;' : 'color: #94a3b8; font-weight: 500;'">
                                            {{ $st['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Verified Event Timeline -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; max-height: 380px; overflow-y: auto;">
                        <div style="position: relative; padding-left: 20px;">
                            <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: #e2e8f0;"></div>
                            <template x-for="(evt, idx) in events" :key="idx">
                                <div style="position: relative; margin-bottom: 14px;">
                                    <div style="position: absolute; left: -20px; top: 10px; width: 12px; height: 12px; border-radius: 50%; background: #ffffff; border: 2.5px solid; z-index: 2;"
                                         :style="'border-color:' + (evt.event_classifier === 'ACT' ? '#10b981' : '#3b82f6') + '; box-shadow: 0 0 0 2px ' + (evt.event_classifier === 'ACT' ? '#dcfce7' : '#dbeafe') + ';'">
                                    </div>
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; gap: 6px;">
                                            <strong style="font-size: 12px; color: #0f172a;" x-text="evt.title"></strong>
                                            <span style="font-size: 9px; padding: 1px 6px; border-radius: 4px; font-weight: 800;"
                                                  :style="evt.event_classifier === 'ACT' ? 'background: #dcfce7; color: #166534;' : 'background: #fef9c3; color: #854d0e;'"
                                                  x-text="evt.event_classifier === 'ACT' ? 'ACTUAL' : 'ESTIMATED'"></span>
                                        </div>
                                        <div style="font-size: 11px; color: #475569; line-height: 1.35; margin-bottom: 4px;" x-text="evt.description || 'Status update logged.'"></div>
                                        <div style="font-size: 10px; color: #64748b; font-family: monospace;" x-text="formatDateTime(evt.event_date_time)"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metric Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px;">
        <!-- Total Tracked -->
        <div class="tracking-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Total Tracked</span>
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa fa-cubes"></i>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #0f172a; margin: 8px 0 4px 0; letter-spacing: -0.02em;">
                {{ $trackings->total() }}
            </div>
            <div style="font-size: 11px; color: #2563eb; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fa fa-rss"></i> Active Container Stream
            </div>
        </div>

        <!-- In Transit -->
        <div class="tracking-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">In Transit / At Sea</span>
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #f0fdf4; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa fa-ship"></i>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #0284c7; margin: 8px 0 4px 0; letter-spacing: -0.02em;">
                {{ $trackings->where('current_status', 'in_transit')->count() }}
            </div>
            <div style="font-size: 11px; color: #0284c7; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fa fa-compass"></i> Ocean Voyage Active
            </div>
        </div>

        <!-- Discharged -->
        <div class="tracking-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Port Arrived / Discharged</span>
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #faf5ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa fa-anchor"></i>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #7c3aed; margin: 8px 0 4px 0; letter-spacing: -0.02em;">
                {{ $trackings->where('current_status', 'discharged')->count() }}
            </div>
            <div style="font-size: 11px; color: #7c3aed; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fa fa-check"></i> Ready for Terminal Gate Out
            </div>
        </div>

        <!-- Delivered -->
        <div class="tracking-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Delivered / Completed</span>
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #059669; margin: 8px 0 4px 0; letter-spacing: -0.02em;">
                {{ $trackings->where('current_status', 'delivered')->count() }}
            </div>
            <div style="font-size: 11px; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fa fa-flag-checkered"></i> Consignee Receipt Confirmed
            </div>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.tracking') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <div style="position: relative; display: flex; align-items: center;">
                <i class="fa fa-search" style="position: absolute; left: 10px; color: #94a3b8; font-size: 11px;"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search container, vessel, port..." 
                       style="height: 36px; padding: 0 12px 0 28px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; width: 220px; outline: none; background: #f8fafc; color: #1e293b;">
            </div>

            <select name="status" style="height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #334155; background: #f8fafc; outline: none; cursor: pointer; width: 140px;">
                <option value="">All Statuses</option>
                <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked</option>
                <option value="gate_in" {{ request('status') === 'gate_in' ? 'selected' : '' }}>Gate In</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="discharged" {{ request('status') === 'discharged' ? 'selected' : '' }}>Discharged</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>

            @if(isset($carriers) && count($carriers) > 0)
                <select name="carrier" style="height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #334155; background: #f8fafc; outline: none; cursor: pointer; width: 130px;">
                    <option value="">All Carriers</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c }}" {{ request('carrier') === $c ? 'selected' : '' }}>{{ strtoupper($c) }}</option>
                    @endforeach
                </select>
            @endif

            @if(isset($customers) && count($customers) > 0)
                <select name="customer_id" style="height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #334155; background: #f8fafc; outline: none; cursor: pointer; max-width: 180px;">
                    <option value="">All Customers</option>
                    @foreach($customers as $cst)
                        <option value="{{ $cst->id }}" {{ (string) request('customer_id') === (string) $cst->id ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Str::limit($cst->name, 22) }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" style="height: 36px; padding: 0 16px; background: #0f172a; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s;">
                <i class="fa fa-filter"></i> Apply
            </button>

            @if(request()->hasAny(['search', 'carrier', 'status', 'customer_id']))
                <a href="{{ route('admin.tracking') }}" style="font-size: 12px; font-weight: 600; color: #ef4444; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 0 6px;">
                    <i class="fa fa-times-circle"></i> Reset
                </a>
            @endif
        </form>

        <!-- Right Side: Records Count Badge ONLY -->
        <span style="font-size: 12px; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 6px; font-weight: 600;">
            Showing <strong>{{ $trackings->count() }}</strong> of <strong>{{ $trackings->total() }}</strong> records
        </span>
    </div>

    <!-- Active Shipments Data Table Grid -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-barcode" style="color: #64748b; margin-right: 4px;"></i> Container / Reference
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-ship" style="color: #64748b; margin-right: 4px;"></i> Carrier
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-exchange" style="color: #64748b; margin-right: 4px;"></i> Voyage Routing (POL &rarr; POD)
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-anchor" style="color: #64748b; margin-right: 4px;"></i> Vessel &amp; Voyage
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-tasks" style="color: #64748b; margin-right: 4px;"></i> Status &amp; Milestone
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                            <i class="fa fa-clock-o" style="color: #64748b; margin-right: 4px;"></i> Last Synced
                        </th>
                        <th style="padding: 14px 18px; font-weight: 800; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; text-align: center;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings as $tr)
                        @php
                            $badge = $tr->status_badge;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s; cursor: pointer;" 
                            @click="selectTracking('{{ $tr->tracking_number }}', '{{ $tr->carrier_code }}')"
                            onmouseover="this.style.background='#f8fafc'" 
                            onmouseout="this.style.background='transparent'">
                            
                            <!-- Container / Tracking Number -->
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; font-weight: 800; color: #0f172a; letter-spacing: 0.02em;">
                                        {{ $tr->tracking_number }}
                                    </span>
                                    <span style="font-size: 9px; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase;">
                                        {{ $tr->tracking_type ?: 'container' }}
                                    </span>
                                </div>
                                @if($tr->trackable)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                        Ref: {{ class_basename($tr->trackable_type) }} #{{ $tr->trackable_id }}
                                    </div>
                                @endif
                            </td>

                            <!-- Carrier Branding -->
                            <td style="padding: 14px 18px;">
                                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe;">
                                    <i class="fa fa-ship" style="font-size: 10px;"></i>
                                    <span>{{ strtoupper($tr->carrier_code) }}</span>
                                </div>
                            </td>

                            <!-- Origin & Destination -->
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span>{{ $tr->origin_name ?: ($tr->origin_locode ?: 'Awaiting Port Scans') }}</span>
                                    <i class="fa fa-arrow-right" style="color: #94a3b8; font-size: 10px;"></i>
                                    <span>{{ $tr->destination_name ?: ($tr->destination_locode ?: 'Awaiting Port Scans') }}</span>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px; display: flex; gap: 12px;">
                                    @if($tr->etd)
                                        <span>ETD: <strong style="color: #334155;">{{ $tr->etd->format('M d, Y') }}</strong></span>
                                    @endif
                                    @if($tr->eta)
                                        <span>ETA: <strong style="color: #059669;">{{ $tr->eta->format('M d, Y') }}</strong></span>
                                    @endif
                                </div>
                            </td>

                            <!-- Vessel & Voyage -->
                            <td style="padding: 14px 18px; color: #334155;">
                                <div style="font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-anchor" style="color: #64748b; font-size: 11px;"></i>
                                    <span>{{ $tr->vessel_name ?: '-' }}</span>
                                </div>
                                @if($tr->voyage_no)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                        Voyage: <span style="font-family: monospace; font-weight: 600;">{{ $tr->voyage_no }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Status Badge & Milestone Meter -->
                            <td style="padding: 14px 18px; min-width: 170px;">
                                <div style="margin-bottom: 5px;">
                                    <span style="display: inline-block; padding: 3px 9px; border-radius: 9999px; font-size: 10px; font-weight: 800; letter-spacing: 0.04em; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                <div style="background: #e2e8f0; height: 6px; border-radius: 9999px; overflow: hidden; max-width: 140px;">
                                    <div style="background: #2563eb; height: 100%; border-radius: 9999px; width: {{ $tr->progress_percentage }}%;"></div>
                                </div>
                            </td>

                            <!-- Last Synced -->
                            <td style="padding: 14px 18px; color: #64748b; font-size: 11px;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-clock-o"></i>
                                    <span>{{ $tr->last_polled_at ? $tr->last_polled_at->diffForHumans() : 'Never' }}</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 14px 18px; text-align: center;" @click.stop>
                                <button type="button" 
                                        @click="selectTracking('{{ $tr->tracking_number }}', '{{ $tr->carrier_code }}')"
                                        style="padding: 6px 14px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;"
                                        onmouseover="this.style.background='#2563eb'; this.style.color='#ffffff';"
                                        onmouseout="this.style.background='#eff6ff'; this.style.color='#2563eb';">
                                    <i class="fa fa-crosshairs"></i> View Radar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 48px 24px; text-align: center; color: #94a3b8;">
                                <div style="width: 52px; height: 52px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; font-size: 20px; color: #cbd5e1;">
                                    <i class="fa fa-search"></i>
                                </div>
                                <h4 style="margin: 0 0 4px 0; color: #475569; font-size: 14px; font-weight: 700;">No Tracked Shipments Found</h4>
                                <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                    Enter any ocean container number or Master B/L above to trace live with ShipmentBeat telemetry.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trackings->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                {{ $trackings->links() }}
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- CENTER SCREEN FULLSCREEN MARITIME ROUTE MODAL            -->
    <!-- ======================================================== -->
    <div x-show="showCenterMapModal" 
         x-cloak
         style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center;"
         @keydown.escape.window="closeCenterMapModal()">

        <!-- Dark backdrop overlay -->
        <div x-show="showCenterMapModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeCenterMapModal()"
             style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px);">
        </div>

        <!-- Center modal window container -->
        <div x-show="showCenterMapModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="position: relative; width: 92vw; max-width: 1400px; height: 86vh; max-height: 880px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.4); display: flex; flex-direction: column; overflow: hidden; z-index: 100000;">

            <!-- Modal Header -->
            <div style="padding: 16px 24px; background: #ffffff; border-bottom: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; text-transform: lowercase;"
                         :style="'background:' + (getCarrierBadge().bg || '#ffcc00') + '; color:' + (getCarrierBadge().color || '#000000') + '; border: 1.5px solid ' + (getCarrierBadge().border || '#eab308') + ';'"
                         x-text="getCarrierBadge().short || 'msc'">
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" x-text="activeTrackingNumber"></h3>
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; text-transform: uppercase;"
                                  :style="'background:' + (statusBadge.bg || '#eff6ff') + '; color:' + (statusBadge.color || '#2563eb') + ';'"
                                  x-text="statusBadge.label || 'IN TRANSIT'"></span>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <span x-text="getRouteDisplay().label"></span>
                            <span style="margin: 0 6px;">•</span>
                            <span x-text="getVesselLabel()"></span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" 
                            @click="fitCenterModalRoute()" 
                            style="padding: 6px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa fa-crosshairs"></i> Reset View
                    </button>

                    <button type="button" 
                            @click="closeCenterMapModal()"
                            style="width: 34px; height: 34px; border-radius: 6px; background: #f1f5f9; border: none; color: #475569; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Map Body -->
            <div style="flex: 1; position: relative; background: #f8fafc;">
                <div id="center-modal-ocean-map" style="width: 100%; height: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Add Manual Event Note Modal -->
    <div x-show="showManualModal" 
         x-cloak
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 100000; display: flex; align-items: center; justify-content: center;"
         @keydown.escape.window="showManualModal = false">
        
        <div @click.away="showManualModal = false"
             style="background: #ffffff; border-radius: 12px; width: 440px; max-width: 94vw; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
            
            <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">
                    <i class="fa fa-pencil" style="color: #2563eb; margin-right: 6px;"></i> Add Tracking Note / Event
                </h4>
                <button type="button" @click="showManualModal = false" style="background: transparent; border: none; font-size: 16px; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>

            <div style="padding: 20px;">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px;">Milestone Title</label>
                    <input type="text" x-model="manualForm.title" placeholder="e.g. Customs Cleared / Terminal Gate Hold" style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px;">Location</label>
                    <input type="text" x-model="manualForm.location_name" placeholder="e.g. Savannah Garden City Terminal" style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px;">Description / Remarks</label>
                    <textarea x-model="manualForm.description" rows="3" placeholder="Enter notes or updates regarding this container..." style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                    <button type="button" @click="showManualModal = false" style="padding: 8px 16px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; cursor: pointer;">Cancel</button>
                    <button type="button" @click="submitManualEvent()" style="padding: 8px 18px; background: #2563eb; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; color: #ffffff; cursor: pointer;">Save Event</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function trackingCenterPage() {
    return {
        activeTrackingNumber: @json(request('search') ?: ''),
        selectedCarrier: @json(request('carrier') ?: ''),
        trackingData: null,
        events: [],
        statusBadge: { bg: '#eff6ff', color: '#1e40af', label: 'BOOKED', step: 0 },
        isSyncing: false,
        isLoadingDetails: false,
        inpageMap: null,
        showCenterMapModal: false,
        centerModalMap: null,
        showManualModal: false,
        manualForm: {
            title: '',
            location_name: '',
            description: '',
            event_type: 'CUSTOMS',
            event_classifier: 'ACT'
        },

        initPage() {
            window.trackingCenterInstance = this;
            if (this.activeTrackingNumber) {
                this.fetchTrackingDetails(this.activeTrackingNumber);
            }
        },

        selectTracking(trackingOrNumber, carrier = '') {
            let number = '';
            let carrierCode = '';
            if (typeof trackingOrNumber === 'object' && trackingOrNumber !== null) {
                number = trackingOrNumber.tracking_number || '';
                carrierCode = trackingOrNumber.carrier_code || carrier || '';
            } else {
                number = String(trackingOrNumber || '').trim().toUpperCase();
                carrierCode = carrier || '';
            }
            if (!number) return;
            this.activeTrackingNumber = number;
            this.selectedCarrier = carrierCode;
            const input = document.getElementById('quick-track-input');
            if (input) input.value = number;
            this.fetchTrackingDetails(this.activeTrackingNumber);
            this.$nextTick(() => {
                const el = document.getElementById('inpage-tracking-section');
                if (el) {
                    const yOffset = -90;
                    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
                }
            });
        },

        closeInPageSection() {
            this.activeTrackingNumber = '';
            this.trackingData = null;
            this.events = [];
            if (this.inpageMap) {
                this.inpageMap.remove();
                this.inpageMap = null;
            }
        },

        async fetchTrackingDetails(number) {
            if (!number) return;
            this.isLoadingDetails = true;
            try {
                const res = await fetch(`/api/tracking/details?tracking_number=${encodeURIComponent(number)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.tracking) {
                    this.trackingData = data.tracking;
                    this.events = data.events || [];
                    this.statusBadge = data.tracking.status_badge || { bg: '#eff6ff', color: '#1e40af', label: 'BOOKED', step: 0 };
                    if (data.tracking.carrier_code && !this.selectedCarrier) {
                        this.selectedCarrier = data.tracking.carrier_code;
                    }
                    this.$nextTick(() => {
                        setTimeout(() => { this.initInPageMap(); }, 100);
                        setTimeout(() => { this.initInPageMap(); }, 350);
                    });
                }
            } catch(e) {
                console.error('Failed to load tracking details:', e);
            } finally {
                this.isLoadingDetails = false;
            }
        },

        async syncLiveTracking() {
            if (!this.activeTrackingNumber) return;
            this.isSyncing = true;
            try {
                const res = await fetch('/api/tracking/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        tracking_number: this.activeTrackingNumber,
                        carrier_code: this.selectedCarrier || undefined,
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('success', data.message || 'Telemetry updated successfully.');
                    }
                    if (data.tracking) {
                        this.trackingData = data.tracking;
                        this.events = data.events || data.tracking.events || [];
                        this.statusBadge = data.tracking.status_badge || { bg: '#eff6ff', color: '#1e40af', label: 'BOOKED', step: 0 };
                        if (data.tracking.carrier_code && !this.selectedCarrier) {
                            this.selectedCarrier = data.tracking.carrier_code;
                        }
                        this.$nextTick(() => {
                            setTimeout(() => { this.initInPageMap(); }, 100);
                            setTimeout(() => { this.initInPageMap(); }, 350);
                        });
                    } else {
                        await this.fetchTrackingDetails(this.activeTrackingNumber);
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('error', data.message || 'Tracking sync failed.');
                    } else {
                        alert(data.message || 'Tracking sync failed.');
                    }
                }
            } catch(e) {
                console.error('Error syncing tracking:', e);
                if (typeof showToast === 'function') showToast('error', 'Network error during tracking sync.');
            } finally {
                this.isSyncing = false;
            }
        },

        async submitManualEvent() {
            if (!this.manualForm.title.trim()) {
                alert('Please enter a milestone title.');
                return;
            }
            try {
                const res = await fetch('/api/tracking/manual-event', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        tracking_number: this.activeTrackingNumber,
                        ...this.manualForm
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.showManualModal = false;
                    this.manualForm = { title: '', location_name: '', description: '', event_type: 'CUSTOMS', event_classifier: 'ACT' };
                    await this.fetchTrackingDetails(this.activeTrackingNumber);
                }
            } catch(e) {
                console.error('Error saving manual event:', e);
            }
        },

        getCarrierBadge() {
            if (this.trackingData && this.trackingData.carrier_badge) {
                return this.trackingData.carrier_badge;
            }
            const c = (this.selectedCarrier || (this.trackingData ? this.trackingData.carrier_code : '') || 'MSCU').toUpperCase();
            const map = {
                'MSCU': { name: 'MSC', short: 'msc', bg: '#ffcc00', color: '#000000', border: '#eab308' },
                'MAEU': { name: 'Maersk', short: 'maersk', bg: '#002444', color: '#ffffff', border: '#002444' },
                'CMDU': { name: 'CMA CGM', short: 'cma', bg: '#00256c', color: '#ffffff', border: '#00256c' },
                'COSU': { name: 'COSCO', short: 'cosco', bg: '#0284c7', color: '#ffffff', border: '#0284c7' },
                'HLCU': { name: 'Hapag-Lloyd', short: 'hl', bg: '#ff6600', color: '#ffffff', border: '#ea580c' },
                'ONEY': { name: 'ONE', short: 'one', bg: '#ff1493', color: '#ffffff', border: '#db2777' },
                'EGLV': { name: 'Evergreen', short: 'evergreen', bg: '#008040', color: '#ffffff', border: '#008040' },
                'HDMU': { name: 'HMM', short: 'hmm', bg: '#003399', color: '#ffffff', border: '#003399' },
                'YMLU': { name: 'Yang Ming', short: 'yml', bg: '#dc2626', color: '#ffffff', border: '#b91c1c' },
                'ZIMU': { name: 'ZIM', short: 'zim', bg: '#1e3a8a', color: '#ffffff', border: '#1e3a8a' },
                'WHLC': { name: 'Wan Hai', short: 'whl', bg: '#0284c7', color: '#ffffff', border: '#0284c7' },
            };
            return map[c] || { name: c, short: c.toLowerCase().slice(0, 4), bg: '#2563eb', color: '#ffffff', border: '#1d4ed8' };
        },

        getContainerType() {
            if (this.trackingData && this.trackingData.container_type_label) {
                return this.trackingData.container_type_label;
            }
            return "40' General Purpose";
        },

        getRouteDisplay() {
            if (this.trackingData && this.trackingData.route_display) {
                return this.trackingData.route_display;
            }
            return {
                origin_city: this.getOriginLabel(),
                dest_city: this.getDestinationLabel(),
                label: this.getOriginLabel() + ' ➔ ' + this.getDestinationLabel()
            };
        },

        getEtaDisplay() {
            if (this.trackingData && this.trackingData.eta_display) {
                return this.trackingData.eta_display;
            }
            return {
                label: 'ETA • ' + this.getDestinationLabel(),
                date: (this.trackingData && this.trackingData.eta) ? this.formatDate(this.trackingData.eta) + ' LT' : 'ETA TBA'
            };
        },

        getJourneySteps() {
            if (this.trackingData && Array.isArray(this.trackingData.journey_steps) && this.trackingData.journey_steps.length > 0) {
                return this.trackingData.journey_steps;
            }
            return [
                { type: 'fpol', label: 'POL', city: this.getOriginLabel(), flag: '🌐', status: 'completed' },
                ...(this.trackingData && this.trackingData.vessel_name ? [{ type: 'vessel', label: 'At sea on ' + this.trackingData.vessel_name, status: 'active' }] : []),
                { type: 'lpod', label: 'POD', city: this.getDestinationLabel(), flag: '🌐', status: 'upcoming' },
                { type: 'del', label: 'Place of Delivery', city: this.getDestinationLabel(), flag: '🌐', status: 'upcoming' }
            ];
        },

        initInPageMap() {
            const container = document.getElementById('inpage-ocean-map');
            if (!container || typeof L === 'undefined') return;

            if (!this.inpageMap) {
                if (container._leaflet_id) {
                    delete container._leaflet_id;
                }
                this.inpageMap = L.map('inpage-ocean-map', {
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: true,
                });

                // Clean light-gray maritime basemap (Esri World Light Gray Base: No watermarks, no API key required)
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 16,
                    attribution: '&copy; Esri &copy; OpenStreetMap'
                }).addTo(this.inpageMap);

                L.control.zoom({ position: 'topright' }).addTo(this.inpageMap);
            } else {
                this.inpageMap.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) return;
                    this.inpageMap.removeLayer(layer);
                });
            }

            this.inpageMap.invalidateSize();

            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.inpageMap.setView([20, 0], 2);
                return;
            }

            const origin = mapData.origin;
            const dest = mapData.destination;
            const waypoints = mapData.waypoints || [];

            // Draw Nautical Shipping Route Polyline (Vibrant Royal Blue)
            if (waypoints.length > 1) {
                L.polyline(waypoints, {
                    color: '#2563eb',
                    weight: 3.5,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(this.inpageMap);
            }

            // Origin Port Marker (POL circular badge)
            const polIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="maritime-pin-badge" title="Port of Loading: ${origin.label}">POL</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            });
            const polMarker = L.marker([origin.lat, origin.lng], { icon: polIcon }).addTo(this.inpageMap);
            polMarker.bindPopup(`
                <div style="font-family: 'Inter', -apple-system, sans-serif; padding: 4px 6px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">POL</div>
                    <div style="font-size: 11px; color: #475569; font-weight: 600; line-height: 1.35;">${origin.terminal || origin.label}</div>
                </div>
            `);

            // Destination Port Marker (POD circular badge)
            const podIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="maritime-pin-badge" title="Port of Discharge: ${dest.label}">POD</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            });
            const podMarker = L.marker([dest.lat, dest.lng], { icon: podIcon }).addTo(this.inpageMap);
            podMarker.bindPopup(`
                <div style="font-family: 'Inter', -apple-system, sans-serif; min-width: 180px;">
                    <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 3px;">POD</div>
                    <div style="font-size: 11px; color: #475569; font-weight: 600; line-height: 1.4;">${dest.terminal || dest.label}</div>
                </div>
            `, { autoClose: false, closeOnClick: false });

            // Automatically open the POD popup matching FindTEU reference design
            setTimeout(() => {
                if (podMarker) podMarker.openPopup();
            }, 300);
            setTimeout(() => {
                if (podMarker) podMarker.openPopup();
            }, 700);

            // Active Vessel Marker with Rotating Navigation Arrow and Pulsing Radar Aura
            if (mapData.vessel && mapData.vessel.lat && mapData.vessel.lng) {
                const vessel = mapData.vessel;
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
                    .addTo(this.inpageMap)
                    .bindPopup(`
                        <div style="font-family: 'Inter', -apple-system, sans-serif; padding: 4px 6px;">
                            <div style="font-size: 12px; font-weight: 800; color: #0f172a;">${vessel.name}</div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Voyage: ${vessel.voyage || 'Active'} • Heading: ${Math.round(heading)}°</div>
                        </div>
                    `);
            }

            this.fitInPageMap();
        },

        fitInPageMap() {
            if (!this.inpageMap) return;
            this.inpageMap.invalidateSize();
            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.inpageMap.setView([20, 0], 2);
                return;
            }
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
            this.inpageMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 5 });
        },

        openCenterMapModal() {
            this.showCenterMapModal = true;
            this.$nextTick(() => {
                setTimeout(() => { this.initCenterModalMap(); }, 50);
                setTimeout(() => { if (this.centerModalMap) { this.centerModalMap.invalidateSize(); this.fitCenterModalRoute(); } }, 200);
                setTimeout(() => { if (this.centerModalMap) { this.centerModalMap.invalidateSize(); this.fitCenterModalRoute(); } }, 450);
            });
        },

        closeCenterMapModal() {
            this.showCenterMapModal = false;
        },

        initCenterModalMap() {
            const container = document.getElementById('center-modal-ocean-map');
            if (!container || typeof L === 'undefined') return;

            if (!this.centerModalMap) {
                if (container._leaflet_id) delete container._leaflet_id;
                this.centerModalMap = L.map('center-modal-ocean-map', {
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: true,
                });
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 16,
                    attribution: '&copy; Esri &copy; OpenStreetMap'
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
            const waypoints = mapData.waypoints || [];

            if (waypoints.length > 1) {
                L.polyline(waypoints, {
                    color: '#2563eb',
                    weight: 3.5,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(this.centerModalMap);
            }

            const polIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="maritime-pin-badge" style="width:34px;height:34px;font-size:12px;" title="${origin.label}">POL</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
            });
            L.marker([origin.lat, origin.lng], { icon: polIcon })
                .addTo(this.centerModalMap)
                .bindPopup(`<strong>POL:</strong><br>${origin.terminal || origin.label}`);

            const podIcon = L.divIcon({
                className: 'custom-leaflet-div',
                html: `<div class="maritime-pin-badge" style="width:34px;height:34px;font-size:12px;" title="${dest.label}">POD</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
            });
            const podModalMarker = L.marker([dest.lat, dest.lng], { icon: podIcon })
                .addTo(this.centerModalMap)
                .bindPopup(`
                    <div style="font-family: 'Inter', -apple-system, sans-serif; min-width: 180px;">
                        <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 3px;">POD</div>
                        <div style="font-size: 11px; color: #475569; font-weight: 600; line-height: 1.4;">${dest.terminal || dest.label}</div>
                    </div>
                `, { autoClose: false, closeOnClick: false });

            setTimeout(() => {
                if (podModalMarker) podModalMarker.openPopup();
            }, 300);

            if (mapData.vessel && mapData.vessel.lat && mapData.vessel.lng) {
                const vessel = mapData.vessel;
                const heading = vessel.heading || 0;
                const vesselIcon = L.divIcon({
                    className: 'custom-leaflet-div',
                    html: `
                        <div class="vessel-arrow-container" style="width:50px;height:50px;" title="${vessel.name}">
                            <div class="vessel-pulse-aura" style="width:50px;height:50px;"></div>
                            <div class="vessel-arrow-icon" style="transform: rotate(${heading}deg);">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="#2563eb">
                                    <polygon points="12 2, 22 22, 12 17, 2 22" />
                                </svg>
                            </div>
                        </div>
                    `,
                    iconSize: [50, 50],
                    iconAnchor: [25, 25],
                });
                L.marker([vessel.lat, vessel.lng], { icon: vesselIcon, zIndexOffset: 1000 })
                    .addTo(this.centerModalMap)
                    .bindPopup(`<strong>${vessel.name}</strong><br>Voyage: ${vessel.voyage || 'Active'} • Heading: ${Math.round(heading)}°`);
            }

            this.fitCenterModalRoute();
        },

        fitCenterModalRoute() {
            if (!this.centerModalMap) return;
            this.centerModalMap.invalidateSize();
            const mapData = this.trackingData ? this.trackingData.map_data : null;
            if (!mapData || !mapData.has_route) {
                this.centerModalMap.setView([20, 0], 2);
                return;
            }
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
            this.centerModalMap.fitBounds(bounds, { padding: [60, 60], maxZoom: 5 });
        },

        currentStepIndex() {
            if (!this.trackingData) return 0;
            const s = (this.trackingData.current_status || '').toLowerCase();
            if (s === 'delivered') return 5;
            if (s === 'discharged' || s === 'arrived' || s === 'gate_out') return 4;
            if (s === 'in_transit') return 3;
            if (s === 'loaded') return 2;
            if (s === 'gate_in') return 1;
            return 0; // Booked
        },

        stepSummaryText() {
            const idx = this.currentStepIndex();
            return (idx + 1) + ' of 6 stages reached';
        },

        getOriginLabel() {
            if (this.trackingData && this.trackingData.origin_name) {
                return this.trackingData.origin_name + (this.trackingData.origin_locode ? ' (' + this.trackingData.origin_locode + ')' : '');
            }
            if (this.trackingData && this.trackingData.map_data && this.trackingData.map_data.origin) {
                return this.trackingData.map_data.origin.label;
            }
            return (this.trackingData && this.trackingData.origin_locode) || 'Origin Terminal';
        },

        getDestinationLabel() {
            if (this.trackingData && this.trackingData.destination_name) {
                return this.trackingData.destination_name + (this.trackingData.destination_locode ? ' (' + this.trackingData.destination_locode + ')' : '');
            }
            if (this.trackingData && this.trackingData.map_data && this.trackingData.map_data.destination) {
                return this.trackingData.map_data.destination.label;
            }
            return (this.trackingData && this.trackingData.destination_locode) || 'Port of Discharge';
        },

        getVesselLabel() {
            if (this.trackingData && this.trackingData.vessel_name) return this.trackingData.vessel_name;
            if (this.trackingData && this.trackingData.map_data && this.trackingData.map_data.vessel && this.trackingData.map_data.vessel.name) {
                return this.trackingData.map_data.vessel.name;
            }
            return this.formatCarrierName() + ' Vessel';
        },

        formatCarrierName() {
            const c = (this.selectedCarrier || (this.trackingData ? this.trackingData.carrier_code : '') || '').toUpperCase();
            if (!c || c === 'SHIPMENTBEAT') return 'Carrier';
            const names = {
                'MSCU': 'MSC',
                'MAEU': 'Maersk',
                'CMDU': 'CMA CGM',
                'COSU': 'COSCO',
                'HLCU': 'Hapag-Lloyd',
                'ONEY': 'ONE',
                'EGLV': 'Evergreen',
                'HDMU': 'HMM',
                'YMLU': 'Yang Ming',
                'ZIMU': 'ZIM',
                'WHLC': 'Wan Hai',
                'PCIU': 'PIL',
                'OOLU': 'OOCL'
            };
            return (names[c] || c) + ' via ShipmentBeat';
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

        getLastPolledText() {
            if (this.trackingData && this.trackingData.last_polled_at) {
                return this.formatDateTime(this.trackingData.last_polled_at);
            }
            return 'Just now';
        }
    };
}

function handleQuickTrack() {
    const input = document.getElementById('quick-track-input');
    const val = input ? input.value.trim().toUpperCase() : '';
    if (!val) {
        if (typeof showToast === 'function') showToast('warning', 'Please enter a container or B/L number.');
        else alert('Please enter a container or B/L number.');
        return;
    }

    if (/^[A-Z]+$/.test(val) || val.length < 8) {
        const msg = `Invalid format "${val}". Container numbers must consist of 4 letters followed by 6 or 7 digits (e.g., MEDU4387708).`;
        if (typeof showToast === 'function') showToast('error', msg);
        else alert(msg);
        return;
    }

    const containerPattern = /^[A-Z]{4}[0-9]{6,7}$/;
    const blPattern = /^[A-Z0-9]{8,20}$/;
    if (!containerPattern.test(val) && !blPattern.test(val)) {
        const msg = `Invalid tracking number "${val}". Please enter a valid container number (e.g., MEDU4387708) or Master B/L.`;
        if (typeof showToast === 'function') showToast('error', msg);
        else alert(msg);
        return;
    }

    if (window.trackingCenterInstance) {
        window.trackingCenterInstance.activeTrackingNumber = val;
        window.trackingCenterInstance.fetchTrackingDetails(val);
        window.trackingCenterInstance.syncLiveTracking();
        const el = document.getElementById('inpage-tracking-section');
        if (el) {
            const yOffset = -90;
            const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
        }
    }
}
</script>
</x-layout>
