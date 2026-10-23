<!-- Global Searchable Select Enhancement Component -->
<style>
    /* Styling for the custom searchable dropdown wrapper */
    .fms-searchable-select-wrapper {
        position: relative !important;
        display: inline-block !important;
        width: 100% !important;
        vertical-align: middle !important;
        box-sizing: border-box !important;
    }
    .fms-ss-control {
        width: 100% !important;
        min-height: 26px !important;
        height: 26px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 2px 24px 2px 7px !important;
        font-size: 11px !important;
        line-height: 20px !important;
        border-radius: 3px !important;
        background: #ffffff !important;
        color: #1e293b !important;
        font-family: inherit !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        text-align: left !important;
        display: flex !important;
        align-items: center !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        position: relative !important;
        user-select: none !important;
    }
    .fms-ss-control:focus, .fms-ss-control.active {
        border-color: #3b82f6 !important;
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
    }
    .fms-ss-control.disabled {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
        cursor: not-allowed !important;
        border-color: #e2e8f0 !important;
    }
    .fms-ss-arrow {
        position: absolute !important;
        right: 7px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        pointer-events: none !important;
        color: #64748b !important;
        font-size: 10px !important;
        transition: transform 0.15s ease !important;
    }
    .fms-ss-control.active .fms-ss-arrow {
        transform: translateY(-50%) rotate(180deg) !important;
    }
    .fms-ss-dropdown {
        position: fixed !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 99999999 !important;
        display: none;
        flex-direction: column !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    .fms-ss-dropdown.open {
        display: flex !important;
    }
    .fms-ss-search-wrap {
        padding: 5px !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 2 !important;
    }
    .fms-ss-search-input {
        width: 100% !important;
        height: 24px !important;
        padding: 2px 6px !important;
        font-size: 11px !important;
        border: 1px solid #94a3b8 !important;
        border-radius: 3px !important;
        outline: none !important;
        box-sizing: border-box !important;
        background: #fff !important;
    }
    .fms-ss-search-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 1px #2563eb !important;
    }
    .fms-ss-list {
        max-height: 220px !important;
        overflow-y: auto !important;
        list-style: none !important;
        margin: 0 !important;
        padding: 2px 0 !important;
        text-align: left !important;
    }
    .fms-ss-item {
        padding: 6px 10px !important;
        font-size: 11px !important;
        color: #1e293b !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
        line-height: 16px !important;
    }
    .fms-ss-item:hover, .fms-ss-item.focused {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
    }
    .fms-ss-item.selected {
        background-color: #dbeafe !important;
        color: #1e40af !important;
        font-weight: 600 !important;
    }
    .fms-ss-item.is-empty {
        color: #94a3b8 !important;
        cursor: default !important;
        font-style: italic !important;
        text-align: center !important;
        padding: 10px 8px !important;
    }
    .fms-ss-item.is-add-new {
        color: #2563eb !important;
        font-weight: 600 !important;
        background: #f0fdf4 !important;
        border-top: 1px dashed #cbd5e1 !important;
    }
</style>

<script>
(function() {
    // Universal Searchable Select Engine for all modules
    if (window.__fmsSearchableSelectInitialized) return;
    window.__fmsSearchableSelectInitialized = true;

    let activeDropdown = null;

    // Global document click listener to close open dropdown
    document.addEventListener('click', function(e) {
        if (activeDropdown && !activeDropdown.contains(e.target)) {
            closeDropdown(activeDropdown);
        }
    });

    // Global Escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activeDropdown) {
            closeDropdown(activeDropdown);
        }
    });

    function closeDropdown(wrapper) {
        if (!wrapper) return;
        const dropdown = wrapper.querySelector('.fms-ss-dropdown');
        const control = wrapper.querySelector('.fms-ss-control');
        if (dropdown) dropdown.classList.remove('open');
        if (control) control.classList.remove('active');
        if (activeDropdown === wrapper) activeDropdown = null;
    }

    function positionDropdown(wrapper) {
        const dropdown = wrapper.querySelector('.fms-ss-dropdown');
        const control = wrapper.querySelector('.fms-ss-control');
        if (!dropdown || !control) return;

        const rect = control.getBoundingClientRect();
        const dropWidth = Math.max(rect.width, 220);
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        // Horizontal positioning: align with control, but don't overflow viewport right edge
        let left = rect.left;
        if (left + dropWidth > windowWidth - 10) {
            left = Math.max(10, windowWidth - dropWidth - 10);
        }

        dropdown.style.width = dropWidth + 'px';
        dropdown.style.left = left + 'px';

        // Vertical positioning: check available space below vs above
        const spaceBelow = windowHeight - rect.bottom;
        const spaceAbove = rect.top;
        const estimatedHeight = 240;

        if (spaceBelow < estimatedHeight && spaceAbove > spaceBelow) {
            // Open upward
            dropdown.style.top = 'auto';
            dropdown.style.bottom = (windowHeight - rect.top + 2) + 'px';
        } else {
            // Open downward
            dropdown.style.bottom = 'auto';
            dropdown.style.top = (rect.bottom + 2) + 'px';
        }
    }

    function openDropdown(wrapper) {
        if (activeDropdown && activeDropdown !== wrapper) {
            closeDropdown(activeDropdown);
        }
        const select = wrapper.__fmsOriginalSelect;
        if (!select || select.disabled) return;

        // Sync items with native select options (in case options were dynamically updated)
        refreshDropdownItems(wrapper);

        const dropdown = wrapper.querySelector('.fms-ss-dropdown');
        const control = wrapper.querySelector('.fms-ss-control');
        const searchInput = wrapper.querySelector('.fms-ss-search-input');

        positionDropdown(wrapper);

        dropdown.classList.add('open');
        control.classList.add('active');
        activeDropdown = wrapper;

      if (searchInput) {
    searchInput.value = '';

    const select = wrapper.__fmsOriginalSelect;

    if (select?.dataset.ajaxSearch === 'true') {
        searchRemoteOptions(wrapper, '');
    } else {
        filterItems(wrapper, '');
    }

    setTimeout(() => searchInput.focus(), 50);
}
    }

    // Reposition active dropdown on window scroll or resize
    window.addEventListener('scroll', function() {
        if (activeDropdown) {
            positionDropdown(activeDropdown);
        }
    }, true);

    window.addEventListener('resize', function() {
        if (activeDropdown) {
            positionDropdown(activeDropdown);
        }
    });

    function refreshDropdownItems(wrapper) {
        const select = wrapper.__fmsOriginalSelect;
        const list = wrapper.querySelector('.fms-ss-list');
        const control = wrapper.querySelector('.fms-ss-control');
        if (!select || !list) return;

        list.innerHTML = '';
        const options = Array.from(select.options);
        const selectedVal = select.value;

        let selectedText = '';

        options.forEach((opt, idx) => {
            const isSelected = (opt.value === selectedVal) || (!selectedVal && idx === 0 && !opt.value);
            if (isSelected) {
                selectedText = opt.text;
            }

            const item = document.createElement('li');
            item.className = 'fms-ss-item' + (isSelected ? ' selected' : '') + (opt.value === 'ADD_NEW' ? ' is-add-new' : '');
            item.textContent = opt.text || '(Empty)';
            item.dataset.value = opt.value;
            item.title = opt.text;

            item.addEventListener('click', function(e) {
                e.stopPropagation();
                selectOption(wrapper, opt.value, opt.text);
            });

            list.appendChild(item);
        });

        // Update control label
        if (control) {
            const textSpan = control.querySelector('.fms-ss-text');
            if (textSpan) {
                textSpan.textContent = selectedText || select.options[select.selectedIndex]?.text || 'Select...';
            }
            if (select.disabled) {
                control.classList.add('disabled');
            } else {
                control.classList.remove('disabled');
            }
        }
    }
function showSearchMessage(wrapper, message) {
    const list = wrapper.querySelector('.fms-ss-list');
    if (!list) return;

    list.innerHTML = '';

    const item = document.createElement('li');
    item.className = 'fms-ss-item is-empty';
    item.textContent = message;

    list.appendChild(item);
}

async function searchRemoteOptions(wrapper, query) {
    const select = wrapper.__fmsOriginalSelect;
    const url = select?.dataset.searchUrl;

    if (!select || !url) return;

    // Har nayi search purani request ko invalidate karegi
    const requestId = (wrapper.__fmsRequestId || 0) + 1;
    wrapper.__fmsRequestId = requestId;

    const search = query.trim();

 
if (search.length > 0 && search.length < 2) {
    showSearchMessage(
        wrapper,
        'Type at least 2 characters to search'
    );
    return;
}

    showSearchMessage(wrapper, 'Searching...');

    try {
        const searchUrl = new URL(url, window.location.origin);
        searchUrl.searchParams.set('q', search);

        const response = await fetch(searchUrl, {
            headers: {
                Accept: 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const ports = await response.json();

        // Sirf latest request ke results display honge
        if (requestId !== wrapper.__fmsRequestId) return;

        if (!Array.isArray(ports)) {
            throw new Error('Invalid search response');
        }

        const selectedValue = select.value;
        const selectedOption = select.selectedOptions[0];
        const selectedText = selectedOption?.textContent || '';

        const placeholder = Array.from(select.options)
            .find(option => option.value === '');

        const placeholderText =
            placeholder?.textContent || 'Select Port';

        select.replaceChildren();

        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = placeholderText;
        select.appendChild(emptyOption);

        // Existing selected value ko preserve karo
        if (selectedValue) {
            const selected = document.createElement('option');
            selected.value = selectedValue;
            selected.textContent = selectedText;
            selected.selected = true;
            select.appendChild(selected);
        }

        // Maximum 20 database results
        ports.slice(0, 20).forEach(port => {
            const value = String(port.id);

            if (Array.from(select.options).some(
                option => option.value === value
            )) {
                return;
            }

            const option = document.createElement('option');
            option.value = value;
            option.textContent = port.name;

            select.appendChild(option);
        });

        select.value = selectedValue;

        refreshDropdownItems(wrapper);
        filterItems(wrapper, search);

    } catch (error) {
        if (requestId !== wrapper.__fmsRequestId) return;

        console.error('Global searchable select error:', error);

        showSearchMessage(
            wrapper,
            'Search failed. Please try again.'
        );
    }
}
    function filterItems(wrapper, query) {
        const list = wrapper.querySelector('.fms-ss-list');
        if (!list) return;
        const items = list.querySelectorAll('.fms-ss-item:not(.is-empty)');
        const q = (query || '').trim().toLowerCase();
        let matchCount = 0;

        items.forEach(item => {
            const text = (item.textContent || '').toLowerCase();
            const isAddNew = item.classList.contains('is-add-new');
            // If it's "+ Add New", keep it visible or match query
            if (isAddNew || !q || text.includes(q)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });

        let emptyNotice = list.querySelector('.fms-ss-item.is-empty');
        if (matchCount === 0) {
            if (!emptyNotice) {
                emptyNotice = document.createElement('li');
                emptyNotice.className = 'fms-ss-item is-empty';
                emptyNotice.textContent = 'No matching results';
                list.appendChild(emptyNotice);
            }
            emptyNotice.style.display = 'block';
        } else if (emptyNotice) {
            emptyNotice.style.display = 'none';
        }
    }

    function selectOption(wrapper, value, text) {
        const select = wrapper.__fmsOriginalSelect;
        if (!select) return;

        // Set native select value
        select.value = value;

        // Trigger native change and input events so Alpine x-model and vanilla listeners update seamlessly
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));

        // Update control text
        const control = wrapper.querySelector('.fms-ss-control');
        const textSpan = control ? control.querySelector('.fms-ss-text') : null;
        if (textSpan) {
            textSpan.textContent = text || 'Select...';
        }

        // Highlight selected item in list
        const list = wrapper.querySelector('.fms-ss-list');
        if (list) {
            list.querySelectorAll('.fms-ss-item').forEach(it => {
                it.classList.toggle('selected', it.dataset.value === value);
            });
        }

        closeDropdown(wrapper);
    }

    // Enhance a single select element
    window.enhanceSelect = function(select) {
        if (!select || select.__fmsEnhanced) return;
        // Don't enhance multiple-selects, hidden, or explicitly excluded selects
        if (select.multiple || select.classList.contains('no-search') || select.classList.contains('fms-ss-ignore')) return;
        if (select.closest('.fms-searchable-select-wrapper')) return;

        select.__fmsEnhanced = true;

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'fms-searchable-select-wrapper';
        wrapper.__fmsOriginalSelect = select;

        // Control button (replaces visible select)
        const control = document.createElement('button');
        control.type = 'button';
        control.className = 'fms-ss-control';
        if (select.disabled) control.classList.add('disabled');

        const initialText = select.options[select.selectedIndex]?.text || (select.options[0]?.text || 'Select...');
        control.innerHTML = `<span class="fms-ss-text" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left;">${initialText}</span><i class="fa fa-angle-down fms-ss-arrow"></i>`;

        control.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (activeDropdown === wrapper) {
                closeDropdown(wrapper);
            } else {
                openDropdown(wrapper);
            }
        });

        // Dropdown container
        const dropdown = document.createElement('div');
        dropdown.className = 'fms-ss-dropdown';

        // Search input
        const searchWrap = document.createElement('div');
        searchWrap.className = 'fms-ss-search-wrap';
        searchWrap.addEventListener('click', e => e.stopPropagation());

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'fms-ss-search-input';
        searchInput.placeholder = 'Type to search (e.g. you)...';
        searchInput.autocomplete = 'off';

        searchInput.addEventListener('input', function() {
    const query = searchInput.value;
    const select = wrapper.__fmsOriginalSelect;

    if (wrapper.__fmsSearchTimer) {
        clearTimeout(wrapper.__fmsSearchTimer);
    }

    if (select?.dataset.ajaxSearch === 'true') {
        wrapper.__fmsSearchTimer = setTimeout(() => {
            searchRemoteOptions(wrapper, query);
        }, 250);
    } else {
        filterItems(wrapper, query);
    }
});

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Select first visible match
                const firstVisible = dropdown.querySelector('.fms-ss-item:not(.is-empty)[style*="display: block"], .fms-ss-item:not(.is-empty):not([style*="display: none"])');
                if (firstVisible) {
                    selectOption(wrapper, firstVisible.dataset.value, firstVisible.textContent);
                }
            }
        });

        searchWrap.appendChild(searchInput);
        dropdown.appendChild(searchWrap);

        // Options List
        const list = document.createElement('ul');
        list.className = 'fms-ss-list';
        dropdown.appendChild(list);

        wrapper.appendChild(control);
        wrapper.appendChild(dropdown);

        // Hide original select visually but keep it in DOM for form submission & Alpine x-model
        select.style.position = 'absolute';
        select.style.opacity = '0';
        select.style.pointerEvents = 'none';
        select.style.height = '0';
        select.style.width = '0';
        select.style.margin = '0';
        select.style.padding = '0';
        select.style.border = 'none';
        select.tabIndex = -1;

        // Insert wrapper before select, then place select inside wrapper
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        // Sync when original select changes externally (e.g. from Alpine x-model or AJAX)
        select.addEventListener('change', function() {
            const currentText = select.options[select.selectedIndex]?.text || '';
            const textSpan = control.querySelector('.fms-ss-text');
            if (textSpan) textSpan.textContent = currentText;
            refreshDropdownItems(wrapper);
        });

        // Initial populate
        refreshDropdownItems(wrapper);
    };

    // Scan and enhance all selects
    window.enhanceAllSelects = function(root) {
        const container = root || document.body;
        if (!container) return;
        const selects = container.querySelectorAll('select:not([multiple]):not(.no-search)');
        selects.forEach(sel => {
            // Check if select should be enhanced (avoid pagination rows per page dropdown or date month pickers if small)
            if (sel.closest('.fms-searchable-select-wrapper')) return;
            // Only enhance if it has options or is standard form control
            window.enhanceSelect(sel);
        });
    };

    // Auto-initialize on DOM ready and Turbo navigations
    document.addEventListener('DOMContentLoaded', () => window.enhanceAllSelects());
    document.addEventListener('turbo:load', () => window.enhanceAllSelects());
    document.addEventListener('turbo:render', () => window.enhanceAllSelects());

    // Observe dynamically added rows / selects (e.g. "+ Add Charge", Alpine templates, etc.)
    const observer = new MutationObserver(mutations => {
        let shouldEnhance = false;
        for (const m of mutations) {
            if (m.addedNodes.length > 0) {
                for (const node of m.addedNodes) {
                    if (node.nodeType === 1 && (node.tagName === 'SELECT' || (node.querySelector && node.querySelector('select')))) {
                        shouldEnhance = true;
                        break;
                    }
                }
            }
            if (shouldEnhance) break;
        }
        if (shouldEnhance) {
            window.enhanceAllSelects();
        }
    });

    if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            observer.observe(document.body, { childList: true, subtree: true });
        });
    }
})();
</script>

