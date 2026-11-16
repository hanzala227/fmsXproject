{{-- Universal Shipment Creation & Update Success Popup (1.5s Auto-Dismiss, No OK button) --}}
<div id="shipment-success-popup-container">
@if(session('success'))
    <div id="shipment-success-popup-overlay" 
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.15s ease-out;"
         onclick="if(event.target===this) window.closeShipmentSuccessPopup()">
        <div style="background: #ffffff; border-radius: 8px; width: 360px; max-width: 90vw; box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <!-- Simple Clean Header Bar -->
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; color: #0f172a; font-size: 13px; font-weight: 600;">
                    <i class="fa fa-check-circle" style="font-size: 14px; color: #059669;"></i>
                    <span id="shipment-success-title">Shipment Saved</span>
                </div>
                <button type="button" 
                        onclick="window.closeShipmentSuccessPopup()" 
                        style="background: transparent; border: none; color: #94a3b8; font-size: 16px; cursor: pointer; padding: 0 4px; line-height: 1;"
                        onmouseover="this.style.color='#0f172a'" 
                        onmouseout="this.style.color='#94a3b8'">
                    &times;
                </button>
            </div>

            <!-- Body (Compact, Focused, No OK Button) -->
            <div style="padding: 24px 20px 20px 20px; text-align: center;">
                <div style="width: 48px; height: 48px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                    <i class="fa fa-check" style="font-size: 20px; color: #059669;"></i>
                </div>
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">
                    Operation Successful
                </h3>
                <p id="shipment-success-message" style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">
                    {{ session('success') }}
                </p>
            </div>

            <!-- Auto-dismiss 1.5s Progress Bar -->
            <div id="shipment-success-progress" style="height: 3px; background: #059669; width: 100%; transition: width 1.5s linear;"></div>
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
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<script>
    window.closeShipmentSuccessPopup = function() {
        const el = document.getElementById('shipment-success-popup-overlay');
        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'scale(0.96)';
            el.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
            setTimeout(() => el.remove(), 200);
        }
    };

    window.showShipmentSuccessPopup = function(message = 'Shipment saved successfully.', title = 'Shipment Saved') {
        window.closeShipmentSuccessPopup();

        const overlay = document.createElement('div');
        overlay.id = 'shipment-success-popup-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.15s ease-out;';
        overlay.onclick = function(e) { if (e.target === overlay) window.closeShipmentSuccessPopup(); };

        overlay.innerHTML = `
            <div style="background: #ffffff; border-radius: 8px; width: 360px; max-width: 90vw; box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #0f172a; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-check-circle" style="font-size: 14px; color: #059669;"></i>
                        <span>${title}</span>
                    </div>
                    <button type="button" 
                            onclick="window.closeShipmentSuccessPopup()" 
                            style="background: transparent; border: none; color: #94a3b8; font-size: 16px; cursor: pointer; padding: 0 4px; line-height: 1;"
                            onmouseover="this.style.color='#0f172a'" 
                            onmouseout="this.style.color='#94a3b8'">
                        &times;
                    </button>
                </div>
                <div style="padding: 24px 20px 20px 20px; text-align: center;">
                    <div style="width: 48px; height: 48px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                        <i class="fa fa-check" style="font-size: 20px; color: #059669;"></i>
                    </div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">
                        Operation Successful
                    </h3>
                    <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.5;">
                        ${message}
                    </p>
                </div>
                <div id="shipment-success-progress" style="height: 3px; background: #059669; width: 100%; transition: width 1.5s linear;"></div>
            </div>
        `;
        document.body.appendChild(overlay);
        window.initShipmentSuccessPopupEvents();
    };

    window.initShipmentSuccessPopupEvents = function() {
        const overlay = document.getElementById('shipment-success-popup-overlay');
        if (!overlay) return;

        // Suppress any duplicate header alerts or toasts immediately
        window.suppressDuplicateShipmentAlerts();

        const bar = document.getElementById('shipment-success-progress');
        if (bar) {
            setTimeout(() => { bar.style.width = '0%'; }, 20);
        }

        // Auto-dismiss exactly after 1.5 seconds (1500 ms)
        const autoTimer = setTimeout(() => {
            window.closeShipmentSuccessPopup();
        }, 1500);

        const keyHandler = function(e) {
            if (e.key === 'Escape' || e.key === 'Enter') {
                clearTimeout(autoTimer);
                window.closeShipmentSuccessPopup();
                document.removeEventListener('keydown', keyHandler);
            }
        };
        document.addEventListener('keydown', keyHandler);
    };

    // Suppress redundant header alerts and floating toasts across all shipment modules
    window.suppressDuplicateShipmentAlerts = function() {
        document.querySelectorAll('.alert-success, .alert.alert-success, .toast.success').forEach(el => {
            const txt = (el.textContent || '').toLowerCase();
            if (txt.includes('shipment') || txt.includes('created') || txt.includes('saved') || txt.includes('updated')) {
                el.style.display = 'none';
                el.remove();
            }
        });
    };

    window.checkAndShowSuccessPopup = function() {
        window.suppressDuplicateShipmentAlerts();

        const pendingMsg = sessionStorage.getItem('shipment_success_pending');
        if (pendingMsg) {
            sessionStorage.removeItem('shipment_success_pending');
            window.showShipmentSuccessPopup(pendingMsg);
            return;
        }

        const overlay = document.getElementById('shipment-success-popup-overlay');
        if (overlay) {
            window.initShipmentSuccessPopupEvents();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.checkAndShowSuccessPopup);
    } else {
        window.checkAndShowSuccessPopup();
    }

    document.addEventListener('turbo:load', window.checkAndShowSuccessPopup);
    setTimeout(window.suppressDuplicateShipmentAlerts, 100);
    setTimeout(window.suppressDuplicateShipmentAlerts, 300);
</script>
