import './bootstrap';
import * as Turbo from "@hotwired/turbo";
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Disable Turbo progress bar completely
if (Turbo && Turbo.setProgressBarDelay) {
    Turbo.setProgressBarDelay(999999999);
}

// Global registry to store listeners registered by inline/body scripts for cleanup
const pageListeners = [];
const originalAddEventListener = EventTarget.prototype.addEventListener;

EventTarget.prototype.addEventListener = function(type, listener, ...args) {
    // We only track listeners registered on window or document while tracking is enabled.
    // This avoids tracking listeners on standard DOM elements (which are automatically cleaned up when destroyed)
    // and listeners registered by global libraries during initial boot.
    if (window.__turbo_tracking_listeners && (this === window || this === document)) {
        pageListeners.push({ target: this, type, listener, args });
    }
    
    // Intercept DOMContentLoaded event registration
    if (type === 'DOMContentLoaded' && (this === document || this === window)) {
        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            // If the document is already loaded/interactive (during Turbo navigation), run the listener immediately.
            // Using setTimeout to defer execution until the call stack clears, matching native async-like behavior.
            setTimeout(() => {
                try {
                    if (typeof listener === 'function') {
                        listener.call(this, new Event('DOMContentLoaded'));
                    } else if (listener && typeof listener.handleEvent === 'function') {
                        listener.handleEvent(new Event('DOMContentLoaded'));
                    }
                } catch (e) {
                    console.error("Error executing deferred DOMContentLoaded listener:", e);
                }
            }, 0);
            return;
        }
    }

    return originalAddEventListener.call(this, type, listener, ...args);
};

// Cleanup routine called before rendering a new Turbo page
window.cleanupPageListeners = function() {
    while (pageListeners.length > 0) {
        const { target, type, listener, args } = pageListeners.pop();
        try {
            target.removeEventListener(type, listener, ...args);
        } catch (e) {
            console.error("Error removing listener during Turbo page transition:", e);
        }
    }
};

// Automatically run cleanup when Turbo replaces the page body
document.addEventListener('turbo:before-render', () => {
    if (typeof window.cleanupPageListeners === 'function') {
        window.cleanupPageListeners();
    }
    if (window.Alpine && document.body) {
        try {
            window.Alpine.destroyTree(document.body);
        } catch (e) {
            console.warn("Error destroying Alpine tree before Turbo render:", e);
        }
    }
});

// Reinitialize Alpine components after Turbo renders new content (soft navigation)
// Without this, x-data components in pages loaded via Turbo never boot
document.addEventListener('turbo:render', () => {
    if (window.Alpine) {
        window.Alpine.initTree(document.body);
    }
});

Alpine.plugin(collapse);

// Global store for quote modal state - accessible from any expression regardless of scope
Alpine.store('quoteModal', {
    step: 1,
    selectedQuote: null
});

window.Alpine = Alpine;

Alpine.start();

