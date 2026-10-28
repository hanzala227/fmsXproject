{{-- Universal Shipment Success Popup (Works with standard HTTP, AJAX session storage & Turbo visits) --}}
<div id="shipment-success-popup-container">
@if(session('success'))
    <div id="shipment-success-popup-overlay" 
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.2s ease-out;"
         onclick="if(event.target===this) window.closeShipmentSuccessPopup()">
        <div style="background: #ffffff; border-radius: 8px; width: 380px; max-width: 90vw; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <!-- Header bar with theme color -->
            <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 0.3px;">
                    <i class="fa fa-check-circle" style="font-size: 15px; color: #86efac;"></i>
                    <span id="shipment-success-title">Shipment Saved</span>
                </div>
                <button type="button" 
                        onclick="window.closeShipmentSuccessPopup()" 
                        style="background: transparent; border: none; color: #ffffff; opacity: 0.85; font-size: 18px; cursor: pointer; padding: 0 4px; line-height: 1;"
                        onmouseover="this.style.opacity='1'" 
                        onmouseout="this.style.opacity='0.85'">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div style="padding: 24px 20px 20px 20px; text-align: center;">
                <div style="width: 52px; height: 52px; background: #ecfdf5; border: 2px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto;">
                    <i class="fa fa-check" style="font-size: 24px; color: #059669;"></i>
                </div>
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">
                    Success!
                </h3>
                <p id="shipment-success-message" style="font-size: 12px; color: #475569; margin: 0 0 20px 0; line-height: 1.5;">
                    {{ session('success') }}
                </p>

                <!-- Actions -->
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button type="button" 
                            id="shipment-success-ok-btn"
                            onclick="window.closeShipmentSuccessPopup()" 
                            class="btn-freightx"
                            style="padding: 6px 32px; font-size: 12px; font-weight: 600; border-radius: 4px; cursor: pointer; background: #3b82f6; border: none; color: #fff; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);">
                        OK
                    </button>
                </div>
            </div>

            <!-- Auto-dismiss subtle progress line -->
            <div id="shipment-success-progress" style="height: 3px; background: #3b82f6; width: 100%; transition: width 3.5s linear;"></div>
        </div>
    </div>
@endif
</div>

<style>
    @keyframes popupFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes popupScaleIn {
        from { opacity: 0; transform: scale(0.92); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<script>
    window.closeShipmentSuccessPopup = function() {
        const el = document.getElementById('shipment-success-popup-overlay');
        if (el) {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.2s ease-out';
            setTimeout(() => el.remove(), 200);
        }
    };

    window.showShipmentSuccessPopup = function(message = 'Shipment saved successfully.', title = 'Shipment Saved') {
        window.closeShipmentSuccessPopup();

        const overlay = document.createElement('div');
        overlay.id = 'shipment-success-popup-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.2s ease-out;';
        overlay.onclick = function(e) { if (e.target === overlay) window.closeShipmentSuccessPopup(); };

        overlay.innerHTML = `
            <div style="background: #ffffff; border-radius: 8px; width: 380px; max-width: 90vw; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 0.3px;">
                        <i class="fa fa-check-circle" style="font-size: 15px; color: #86efac;"></i>
                        <span>${title}</span>
                    </div>
                    <button type="button" 
                            onclick="window.closeShipmentSuccessPopup()" 
                            style="background: transparent; border: none; color: #ffffff; opacity: 0.85; font-size: 18px; cursor: pointer; padding: 0 4px; line-height: 1;"
                            onmouseover="this.style.opacity='1'" 
                            onmouseout="this.style.opacity='0.85'">
                        &times;
                    </button>
                </div>
                <div style="padding: 24px 20px 20px 20px; text-align: center;">
                    <div style="width: 52px; height: 52px; background: #ecfdf5; border: 2px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto;">
                        <i class="fa fa-check" style="font-size: 24px; color: #059669;"></i>
                    </div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">
                        Success!
                    </h3>
                    <p style="font-size: 12px; color: #475569; margin: 0 0 20px 0; line-height: 1.5;">
                        ${message}
                    </p>
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button type="button" 
                                id="shipment-success-ok-btn"
                                onclick="window.closeShipmentSuccessPopup()" 
                                class="btn-freightx"
                                style="padding: 6px 32px; font-size: 12px; font-weight: 600; border-radius: 4px; cursor: pointer; background: #3b82f6; border: none; color: #fff; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);">
                            OK
                        </button>
                    </div>
                </div>
                <div id="shipment-success-progress" style="height: 3px; background: #3b82f6; width: 100%; transition: width 3.5s linear;"></div>
            </div>
        `;
        document.body.appendChild(overlay);
        window.initShipmentSuccessPopupEvents();
    };

    window.initShipmentSuccessPopupEvents = function() {
        const overlay = document.getElementById('shipment-success-popup-overlay');
        if (!overlay) return;

        const bar = document.getElementById('shipment-success-progress');
        if (bar) {
            setTimeout(() => { bar.style.width = '0%'; }, 50);
        }

        const autoTimer = setTimeout(() => {
            window.closeShipmentSuccessPopup();
        }, 3500);

        const keyHandler = function(e) {
            if (e.key === 'Escape' || e.key === 'Enter') {
                clearTimeout(autoTimer);
                window.closeShipmentSuccessPopup();
                document.removeEventListener('keydown', keyHandler);
            }
        };
        document.addEventListener('keydown', keyHandler);

        setTimeout(() => {
            const btn = document.getElementById('shipment-success-ok-btn');
            if (btn) btn.focus();
        }, 100);
    };

    window.checkAndShowSuccessPopup = function() {
        // 1. Check if stored in sessionStorage across client-side redirect
        const pendingMsg = sessionStorage.getItem('shipment_success_pending');
        if (pendingMsg) {
            sessionStorage.removeItem('shipment_success_pending');
            window.showShipmentSuccessPopup(pendingMsg);
            return;
        }

        // 2. Check if rendered directly by Blade session('success')
        const overlay = document.getElementById('shipment-success-popup-overlay');
        if (overlay) {
            window.initShipmentSuccessPopupEvents();
        }
    };

    // Auto-run immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.checkAndShowSuccessPopup);
    } else {
        window.checkAndShowSuccessPopup();
    }

    // Hook to Turbo load event for soft navigations
    document.addEventListener('turbo:load', window.checkAndShowSuccessPopup);
</script>
