{{-- Universal Form Usability: Tab Navigation & Name Autocomplete across all modules --}}
<style>
    .fms-kb-dropdown {
        position: fixed;
        max-width: 380px;
        max-height: 220px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #3b82f6;
        border-radius: 4px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        z-index: 9999999;
        font-size: 11px;
        font-family: inherit;
        animation: fmsFadeIn 0.12s ease-out;
    }
    .fms-kb-dropdown-header {
        padding: 4px 8px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 9.5px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
    .fms-kb-dropdown-item {
        padding: 5px 8px;
        cursor: pointer;
        border-bottom: 1px solid #f8fafc;
        color: #334155;
        transition: background 0.1s, color 0.1s;
    }
    .fms-kb-dropdown-item.active {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 600 !important;
    }
    @keyframes fmsFadeIn {
        from { opacity: 0; transform: translateY(-3px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Universal High-Visibility Toast Container & Toast Styles */
    .toast-container, #toast-container {
        position: fixed !important;
        top: 24px !important;
        right: 24px !important;
        z-index: 9999999 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        pointer-events: none !important;
    }
    .toast {
        pointer-events: auto !important;
        min-width: 280px !important;
        max-width: 440px !important;
        padding: 12px 18px !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #ffffff !important;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.28), 0 6px 10px -3px rgba(0, 0, 0, 0.12) !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        line-height: 1.4 !important;
        animation: fmsToastSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        transition: all 0.25s ease !important;
    }
    .toast.error {
        background: #ef4444 !important;
        border-left: 5px solid #991b1b !important;
    }
    .toast.success {
        background: #10b981 !important;
        border-left: 5px solid #065f46 !important;
    }
    .toast.warning {
        background: #f59e0b !important;
        border-left: 5px solid #92400e !important;
    }
    .toast.info {
        background: #3b82f6 !important;
        border-left: 5px solid #1e40af !important;
    }
    @keyframes fmsToastSlideIn {
        from { transform: translateX(110%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .fms-input-invalid-pulse {
        outline: 2px solid #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25) !important;
        transition: all 0.2s ease !important;
    }
</style>

<script>
(function() {
    'use strict';

    let activeDropdown = null;
    let currentSelect = null;
    let selectedOptionIndex = 0;
    let searchBuffer = '';
    let bufferTimer = null;

    // 1. Mark auxiliary buttons with tabindex="-1" so Tab moves cleanly between actual inputs
    function enhanceTabNavigation(root = document) {
        if (!root || !root.querySelectorAll) return;


        const selectors = [
            'button[onclick*="window.open"]',
            'button[onclick*="openAddNewModal"]',
            'button[onclick*="openCertificateModal"]',
            'button.btn-tool-icon'
        ];
        root.querySelectorAll(selectors.join(',')).forEach(btn => {
            if (!btn.hasAttribute('tabindex')) btn.setAttribute('tabindex', '-1');
        });

        // Also mark buttons containing external link / wrench / plus icons
        root.querySelectorAll('button, a').forEach(el => {
            if (el.querySelector('i.fa-external-link, i.fa-wrench') || 
                (el.classList.contains('btn-default-gf') && el.querySelector('i.fa-plus'))) {
                if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '-1');
            }
        });

    }

    function removeDropdown() {
        if (activeDropdown) {
            activeDropdown.remove();
            activeDropdown = null;
        }
        currentSelect = null;
        selectedOptionIndex = 0;
    }

    function createDropdown(selectEl, matches) {
        removeDropdown();
        currentSelect = selectEl;

        const rect = selectEl.getBoundingClientRect();
        const drop = document.createElement('div');
        drop.className = 'fms-kb-dropdown';

        // Position: place below if room, otherwise above
        const spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow < 220 && rect.top > 220) {
            drop.style.bottom = (window.innerHeight - rect.top + 2) + 'px';
        } else {
            drop.style.top = (rect.bottom + 2) + 'px';
        }
        drop.style.left = Math.max(8, rect.left) + 'px';
        drop.style.minWidth = Math.max(rect.width, 220) + 'px';

        const header = document.createElement('div');
        header.className = 'fms-kb-dropdown-header';
        header.textContent = 'Matching Names (Press Tab or Enter to select)';
        drop.appendChild(header);

        matches.forEach((opt, idx) => {
            const item = document.createElement('div');
            item.className = 'fms-kb-dropdown-item' + (idx === 0 ? ' active' : '');
            item.textContent = opt.text;
            item.onmousedown = (e) => {
                e.preventDefault();
                chooseOption(selectEl, opt.value);
            };
            drop.appendChild(item);
        });

        document.body.appendChild(drop);
        activeDropdown = drop;
        selectedOptionIndex = 0;
    }

    function updateHighlight() {
        if (!activeDropdown) return;
        const items = activeDropdown.querySelectorAll('.fms-kb-dropdown-item');
        items.forEach((item, idx) => {
            if (idx === selectedOptionIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    function chooseOption(selectEl, value) {
        selectEl.value = value;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        selectEl.dispatchEvent(new Event('input', { bubbles: true }));
        removeDropdown();
        searchBuffer = '';
    }

    // 2. Global Keyboard listener for Tab, Enter, Arrows, and Typing on Selects
    document.addEventListener('keydown', function(e) {
        const target = e.target;
        if (!target) return;

        if (e.key === 'Escape') {
            removeDropdown();
            searchBuffer = '';
            return;
        }

        // Active Autocomplete Dropdown handling
        if (activeDropdown && currentSelect) {
            const items = activeDropdown.querySelectorAll('.fms-kb-dropdown-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (selectedOptionIndex < items.length - 1) {
                    selectedOptionIndex++;
                    updateHighlight();
                }
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (selectedOptionIndex > 0) {
                    selectedOptionIndex--;
                    updateHighlight();
                }
                return;
            }
            if (e.key === 'Enter' || e.key === 'Tab') {
                const matches = currentSelect._kbMatches || [];
                if (matches[selectedOptionIndex]) {
                    chooseOption(currentSelect, matches[selectedOptionIndex].value);
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                    // If Tab: selection is applied, and browser naturally advances to next input field!
                }
                removeDropdown();
                return;
            }
        }

        // Typing characters on focused <select>
        if (target.tagName === 'SELECT' && !target.disabled) {
            if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                clearTimeout(bufferTimer);
                searchBuffer += e.key;

                bufferTimer = setTimeout(() => {
                    searchBuffer = '';
                    removeDropdown();
                }, 1600);

                // Show matches when user has typed 2 or more characters
                if (searchBuffer.length >= 2) {
                    const q = searchBuffer.toLowerCase();
                    const options = Array.from(target.options).filter(o => o.value && o.value !== 'ADD_NEW');
                    const matches = options.filter(o => o.text.toLowerCase().includes(q));

                    if (matches.length > 0) {
                        target._kbMatches = matches;
                        createDropdown(target, matches);
                    } else {
                        removeDropdown();
                    }
                }
            } else if (e.key === 'Backspace') {
                searchBuffer = searchBuffer.slice(0, -1);
                if (searchBuffer.length < 2) {
                    removeDropdown();
                }
            }
        }
    });

    document.addEventListener('focusout', function(e) {
        setTimeout(() => {
            if (activeDropdown && document.activeElement !== currentSelect) {
                removeDropdown();
            }
        }, 150);
    });

    window.addEventListener('scroll', function() {
        if (activeDropdown) removeDropdown();
    }, true);

    // Initial run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => enhanceTabNavigation());
    } else {
        enhanceTabNavigation();
    }

    // Observer for dynamic elements added by Alpine / AJAX
    const observer = new MutationObserver((mutations) => {
        for (const m of mutations) {
            if (m.addedNodes.length > 0) {
                enhanceTabNavigation();
                break;
            }
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('turbo:load', () => enhanceTabNavigation());
})();

// Universal Notification & Feedback Helpers
window.showToast = function(type, msg) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle', warning: 'exclamation-triangle' };
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> <span>' + msg + '</span>';
    container.appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateY(-10px)';
        setTimeout(() => t.remove(), 250);
    }, 5000);
};

window.highlightAndScrollTo = function(el, errorMsg) {
    if (!el) return;
    if (errorMsg && typeof window.showToast === 'function') {
        window.showToast('error', errorMsg);
    }
    try {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.focus();
    } catch(e) {}
    el.classList.add('fms-input-invalid-pulse');
    setTimeout(() => {
        el.classList.remove('fms-input-invalid-pulse');
    }, 2500);
};

window.triggerPrimarySave = function() {
    // 1. Ocean Import handling
    const oiAlpine = document.querySelector('[x-data*="oceanImportModule"]');
    if (oiAlpine && oiAlpine._x_dataStack && oiAlpine._x_dataStack[0]) {
        const stack = oiAlpine._x_dataStack[0];
        if (!stack.saved && typeof stack.saveMainTab === 'function') {
            stack.saveMainTab();
            return;
        }
        if (typeof stack.validateForm === 'function') {
            if (!stack.validateForm()) return;
        }
        const f = oiAlpine.querySelector('form') || document.querySelector('form[action*="ocean-import"]');
        if (f) { f.submit(); return; }
    }

    // 2. Ocean Export handling
    const oeAlpine = document.querySelector('[x-data*="oceanExportModule"]');
    if (oeAlpine && oeAlpine._x_dataStack && oeAlpine._x_dataStack[0]) {
        const stack = oeAlpine._x_dataStack[0];
        if (typeof stack.validateForm === 'function') {
            if (!stack.validateForm()) return;
        }
        const f = oeAlpine.closest('form') || oeAlpine.querySelector('form') || document.querySelector('form[action*="ocean-export"]');
        if (f) { f.submit(); return; }
    }

    // 3. Air Export handling
    const airExportForm = document.getElementById('airExportForm');
    if (airExportForm) {
        const fileNo = airExportForm.querySelector('input[name="file_no"]');
        if (fileNo && !fileNo.value.trim()) {
            window.highlightAndScrollTo(fileNo, 'File No. is required');
            return;
        }
        if (typeof airExportForm.requestSubmit === 'function') {
            airExportForm.requestSubmit();
        } else {
            airExportForm.submit();
        }
        return;
    }

    // 4. Air Import handling
    const airImportForm = document.getElementById('air-import-form');
    if (airImportForm) {
        const mawb = airImportForm.querySelector('input[name="mawb_no"]');
        const office = airImportForm.querySelector('select[name="office_id"]');
        const aiAlpine = document.querySelector('[x-data*="airImportModule"]');
        if (mawb && !mawb.value.trim()) {
            if (aiAlpine && aiAlpine._x_dataStack && aiAlpine._x_dataStack[0]) {
                aiAlpine._x_dataStack[0].showMblSection = true;
            }
            setTimeout(() => {
                window.highlightAndScrollTo(mawb, 'MAWB No. is required');
            }, 80);
            return;
        }
        if (office && !office.value) {
            if (aiAlpine && aiAlpine._x_dataStack && aiAlpine._x_dataStack[0]) {
                aiAlpine._x_dataStack[0].showMblSection = true;
            }
            setTimeout(() => {
                window.highlightAndScrollTo(office, 'Office is required');
            }, 80);
            return;
        }
        if (typeof airImportForm.requestSubmit === 'function') {
            airImportForm.requestSubmit();
        } else {
            airImportForm.submit();
        }
        return;
    }

    // 5. Fallback: Click any top save button or primary submit button
    const topBtn = document.querySelector('#topSaveButton, button[form="air-import-form"], button[form="airExportForm"], form button[type="submit"]');
    if (topBtn) {
        topBtn.click();
        return;
    }
    const anyForm = document.querySelector('form');
    if (anyForm) {
        if (typeof anyForm.requestSubmit === 'function') anyForm.requestSubmit();
        else anyForm.submit();
    }
};
</script>
