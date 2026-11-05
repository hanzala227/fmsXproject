{{-- Universal Shipment Creation & Update Success Popup (Clean, Simple, Professional) --}}
<div id="shipment-success-popup-container">
@if(session('success'))
    <div id="shipment-success-popup-overlay" 
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.15s ease-out;"
         onclick="if(event.target===this) window.closeShipmentSuccessPopup()">
        <div style="background: #ffffff; border-radius: 6px; width: 380px; max-width: 92vw; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <!-- Simple Clean Header Bar -->
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; color: #0f172a; font-size: 13px; font-weight: 600;">
                    <i class="fa fa-check-circle" style="font-size: 14px; color: #059669;"></i>
                    <span id="shipment-success-title">Shipment Saved</span>
                </div>
                <button type="button" 
                        onclick="window.closeShipmentSuccessPopup()" 
                        style="background: transparent; border: none; color: #64748b; font-size: 18px; cursor: pointer; padding: 0 4px; line-height: 1;"
                        onmouseover="this.style.color='#0f172a'" 
                        onmouseout="this.style.color='#64748b'">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div style="padding: 22px 20px 18px 20px; text-align: center;">
                <div style="width: 44px; height: 44px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                    <i class="fa fa-check" style="font-size: 18px; color: #059669;"></i>
                </div>
                <h3 style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0 0 6px 0;">
                    Operation Successful
                </h3>
                <p id="shipment-success-message" style="font-size: 12px; color: #475569; margin: 0 0 18px 0; line-height: 1.5;">
                    {{ session('success') }}
                </p>

                <!-- Actions -->
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button type="button" 
                            id="shipment-success-ok-btn"
                            onclick="window.closeShipmentSuccessPopup()" 
                            style="padding: 6px 30px; font-size: 12px; font-weight: 500; border-radius: 4px; cursor: pointer; background: #0f172a; border: 1px solid #0f172a; color: #ffffff; transition: background 0.15s ease;"
                            onmouseover="this.style.background='#1e293b'"
                            onmouseout="this.style.background='#0f172a'">
                        OK
                    </button>
                </div>
            </div>

            <!-- Auto-dismiss subtle neutral line -->
            <div id="shipment-success-progress" style="height: 2px; background: #94a3b8; width: 100%; transition: width 3.2s linear;"></div>
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
            el.style.transition = 'opacity 0.15s ease-out';
            setTimeout(() => el.remove(), 150);
        }
    };

    window.showShipmentSuccessPopup = function(message = 'Shipment saved successfully.', title = 'Shipment Saved') {
        window.closeShipmentSuccessPopup();

        const overlay = document.createElement('div');
        overlay.id = 'shipment-success-popup-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(2px); z-index: 9999999; display: flex; align-items: center; justify-content: center; animation: popupFadeIn 0.15s ease-out;';
        overlay.onclick = function(e) { if (e.target === overlay) window.closeShipmentSuccessPopup(); };

        overlay.innerHTML = `
            <div style="background: #ffffff; border-radius: 6px; width: 380px; max-width: 92vw; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0; overflow: hidden; animation: popupScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #0f172a; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-check-circle" style="font-size: 14px; color: #059669;"></i>
                        <span>${title}</span>
                    </div>
                    <button type="button" 
                            onclick="window.closeShipmentSuccessPopup()" 
                            style="background: transparent; border: none; color: #64748b; font-size: 18px; cursor: pointer; padding: 0 4px; line-height: 1;"
                            onmouseover="this.style.color='#0f172a'" 
                            onmouseout="this.style.color='#64748b'">
                        &times;
                    </button>
                </div>
                <div style="padding: 22px 20px 18px 20px; text-align: center;">
                    <div style="width: 44px; height: 44px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                        <i class="fa fa-check" style="font-size: 18px; color: #059669;"></i>
                    </div>
                    <h3 style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0 0 6px 0;">
                        Operation Successful
                    </h3>
                    <p style="font-size: 12px; color: #475569; margin: 0 0 18px 0; line-height: 1.5;">
                        ${message}
                    </p>
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button type="button" 
                                id="shipment-success-ok-btn"
                                onclick="window.closeShipmentSuccessPopup()" 
                                style="padding: 6px 30px; font-size: 12px; font-weight: 500; border-radius: 4px; cursor: pointer; background: #0f172a; border: 1px solid #0f172a; color: #ffffff;">
                            OK
                        </button>
                    </div>
                </div>
                <div id="shipment-success-progress" style="height: 2px; background: #94a3b8; width: 100%; transition: width 3.2s linear;"></div>
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
        }, 3200);

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
</script>
