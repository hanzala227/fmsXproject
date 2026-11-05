<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Shipment Memo Auto-Load - Ocean Import Theme */
        .memo-header {
            background: #67809f;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #5a6d84;
        }
        
        .memo-header h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .memo-header i {
            font-size: 13px;
            color: rgba(255,255,255,0.9);
        }
        
        .memo-tabs {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 0 20px;
            display: flex;
            gap: 0;
            overflow-x: auto;
        }
        
        .memo-tab {
            padding: 12px 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.15s;
            color: #64748b;
            background: transparent;
            border: none;
            outline: none;
            white-space: nowrap;
        }
        
        .memo-tab:hover {
            color: #1e293b;
            background: #f1f5f9;
        }
        
        .memo-tab.active {
            color: #1e293b;
            font-weight: 700;
            border-bottom-color: #3b82f6;
            background: #fff;
        }
        
        .memo-content {
            padding: 24px 30px;
            background: #fff;
            min-height: 380px;
            position: relative;
        }
        
        .memo-section {
            margin-bottom: 30px;
        }
        
        .memo-section-title {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .memo-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 12px;
        }
        
        .memo-checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            transition: all 0.15s;
        }

        .memo-checkbox-item:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }
        
        .memo-checkbox-item input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #3b82f6;
        }
        
        .memo-checkbox-item label {
            font-size: 11px;
            color: #334155;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            margin: 0;
        }

        .save-footer {
            padding: 16px 20px;
            text-align: center;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }
        
        .btn-save {
            background: #3b82f6;
            color: #fff;
            border: 1px solid #2563eb;
            padding: 8px 24px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-save:hover {
            background: #2563eb;
            box-shadow: 0 2px 4px rgba(37,99,235,0.2);
        }

        .spinner-sm {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Toast Container --}}
    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        {{-- Breadcrumb --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/settings">Setting</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="#">Customers</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">Shipment Memo Auto-Load</span></li>
            </ul>
        </div>

        <div class="portlet light">
            
            {{-- Header --}}
            <div class="memo-header">
                <h4><i class="fa fa-info-circle"></i> Shipment Memo Auto-Load</h4>
                <div id="memo-loading" style="display:none; font-size:11px; color:#e2e8f0;">
                    <i class="fa fa-spinner fa-spin"></i> Syncing with database...
                </div>
            </div>

            {{-- Module Tabs --}}
            <div class="memo-tabs">
                <button class="memo-tab active" data-module="ocean-export" onclick="switchModule('ocean-export')">Ocean Export</button>
                <button class="memo-tab" data-module="ocean-import" onclick="switchModule('ocean-import')">Ocean Import</button>
                <button class="memo-tab" data-module="air-export" onclick="switchModule('air-export')">Air Export</button>
                <button class="memo-tab" data-module="air-import" onclick="switchModule('air-import')">Air Import</button>
                <button class="memo-tab" data-module="trucker" onclick="switchModule('trucker')">Trucker</button>
                <button class="memo-tab" data-module="misc" onclick="switchModule('misc')">Misc</button>
            </div>

            {{-- Content Area --}}
            <div class="memo-content" id="memo-content">
                
                {{-- Master B/L Section --}}
                <div class="memo-section">
                    <div class="memo-section-title" style="display:flex;justify-content:space-between;align-items:center;">
                        <span>Master B/L</span>
                        <div style="font-size:10px;font-weight:400;display:flex;gap:8px;">
                            <a href="javascript:void(0)" onclick="toggleSection('master_bl', true)" style="color:#3b82f6;text-decoration:none;font-weight:600;">Select All</a>
                            <span style="color:#cbd5e1;">|</span>
                            <a href="javascript:void(0)" onclick="toggleSection('master_bl', false)" style="color:#64748b;text-decoration:none;">Deselect All</a>
                        </div>
                    </div>
                    <div class="memo-checkboxes" id="master-bl-checkboxes">
                        <!-- Checkboxes rendered synchronously -->
                    </div>
                </div>

                {{-- House B/L Section --}}
                <div class="memo-section">
                    <div class="memo-section-title" style="display:flex;justify-content:space-between;align-items:center;">
                        <span>House B/L</span>
                        <div style="font-size:10px;font-weight:400;display:flex;gap:8px;" id="house-bl-actions">
                            <a href="javascript:void(0)" onclick="toggleSection('house_bl', true)" style="color:#3b82f6;text-decoration:none;font-weight:600;">Select All</a>
                            <span style="color:#cbd5e1;">|</span>
                            <a href="javascript:void(0)" onclick="toggleSection('house_bl', false)" style="color:#64748b;text-decoration:none;">Deselect All</a>
                        </div>
                    </div>
                    <div class="memo-checkboxes" id="house-bl-checkboxes">
                        <!-- Checkboxes rendered synchronously -->
                    </div>
                </div>

            </div>

            {{-- Save Button --}}
            <div class="save-footer">
                <button class="btn-save" id="save-btn" onclick="saveConfig()">
                    <i class="fa fa-save"></i> <span>Save Configuration</span>
                </button>
                <span id="unsaved-badge" style="display:none;font-size:11px;color:#d97706;background:#fef3c7;padding:4px 10px;border-radius:12px;border:1px solid #fde68a;font-weight:600;">
                    <i class="fa fa-exclamation-circle"></i> Unsaved changes
                </span>
            </div>

        </div>
    </div>

    <script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    let currentModule = 'ocean-export';
    let isDirty = false;

    // Field definitions for each module
    const moduleFields = {
        'ocean-export': {
            master_bl: ['Oversea Agent', 'Carrier', 'Consignee', 'Notify', 'Customer', 'Bill To'],
            house_bl: ['Actual Shipper', 'Consignee', 'Customer', 'Notify', 'Also Notify', 'Bill To', 'Customs Broker', 'Trucker']
        },
        'ocean-import': {
            master_bl: ['Oversea Agent', 'Carrier', 'Consignee', 'Shipper', 'Notify', 'Customer', 'Bill To'],
            house_bl: ['Consignee', 'Shipper', 'Customer', 'Notify', 'Bill To', 'Customs Broker', 'Trucker']
        },
        'air-export': {
            master_bl: ['Oversea Agent', 'Carrier', 'Consignee', 'Notify', 'Customer', 'Bill To'],
            house_bl: ['Actual Shipper', 'Oversea Agent', 'Consignee', 'Customer', 'Notify', 'Bill To', 'Issuing Carrier/Agent', 'Trucker']
        },
        'air-import': {
            master_bl: ['Oversea Agent', 'Carrier', 'Shipper', 'Consignee', 'Notify', 'Customer', 'Bill To'],
            house_bl: ['Consignee', 'Actual Shipper', 'Customer', 'Notify', 'Bill To', 'Customs Broker', 'Trucker']
        },
        'trucker': {
            master_bl: ['Shipper', 'Consignee', 'Customer', 'Bill To', 'Trucker'],
            house_bl: []
        },
        'misc': {
            master_bl: ['Oversea Agent', 'Shipper', 'Consignee', 'Customer', 'Bill To', 'Trucker'],
            house_bl: []
        }
    };

    function markDirty(dirty = true) {
        isDirty = dirty;
        const badge = document.getElementById('unsaved-badge');
        if (badge) badge.style.display = dirty ? 'inline-flex' : 'none';
    }

    // Synchronously render checkboxes so UI is never blank
    function renderDefaultCheckboxes() {
        const fields = moduleFields[currentModule] || { master_bl: [], house_bl: [] };
        
        // Master B/L checkboxes
        const masterContainer = document.getElementById('master-bl-checkboxes');
        if (masterContainer) {
            if (fields.master_bl && fields.master_bl.length > 0) {
                masterContainer.innerHTML = fields.master_bl.map(field => {
                    const fieldKey = field.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    return `
                        <div class="memo-checkbox-item">
                            <input type="checkbox" 
                                   id="master_${fieldKey}" 
                                   data-section="master_bl" 
                                   data-field="${fieldKey}" 
                                   onchange="markDirty(true)">
                            <label for="master_${fieldKey}">${field}</label>
                        </div>
                    `;
                }).join('');
            } else {
                masterContainer.innerHTML = '<p style="color:#94a3b8;font-size:11px;font-style:italic;margin:0;">No fields available for this section.</p>';
            }
        }
        
        // House B/L checkboxes
        const houseContainer = document.getElementById('house-bl-checkboxes');
        const houseActions = document.getElementById('house-bl-actions');
        if (houseContainer) {
            if (fields.house_bl && fields.house_bl.length > 0) {
                if (houseActions) houseActions.style.display = 'flex';
                houseContainer.innerHTML = fields.house_bl.map(field => {
                    const fieldKey = field.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    return `
                        <div class="memo-checkbox-item">
                            <input type="checkbox" 
                                   id="house_${fieldKey}" 
                                   data-section="house_bl" 
                                   data-field="${fieldKey}" 
                                   onchange="markDirty(true)">
                            <label for="house_${fieldKey}">${field}</label>
                        </div>
                    `;
                }).join('');
            } else {
                if (houseActions) houseActions.style.display = 'none';
                houseContainer.innerHTML = '<p style="color:#94a3b8;font-size:11px;font-style:italic;margin:0;">No House B/L section for this module.</p>';
            }
        }
    }

    // Fetch database configuration and apply state
    function syncConfigFromDatabase() {
        const loadingIndicator = document.getElementById('memo-loading');
        if (loadingIndicator) loadingIndicator.style.display = 'inline-block';

        fetch(`/api/shipment-memo-configs/module?module=${currentModule}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                applySavedState(data.data);
            }
        })
        .catch(err => {
            console.error('Failed to load memo config:', err);
        })
        .finally(() => {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            markDirty(false);
        });
    }

    function applySavedState(savedData) {
        if (savedData.master_bl) {
            savedData.master_bl.forEach(item => {
                const cb = document.querySelector(`input[data-section="master_bl"][data-field="${item.field_name}"]`);
                if (cb) cb.checked = !!item.is_enabled;
            });
        }
        if (savedData.house_bl) {
            savedData.house_bl.forEach(item => {
                const cb = document.querySelector(`input[data-section="house_bl"][data-field="${item.field_name}"]`);
                if (cb) cb.checked = !!item.is_enabled;
            });
        }
    }

    function loadModuleConfig(module) {
        currentModule = module;
        renderDefaultCheckboxes();
        syncConfigFromDatabase();
    }

    function switchModule(module) {
        if (isDirty) {
            if (!confirm('You have unsaved configuration changes. Switch tab anyway?')) return;
        }
        document.querySelectorAll('.memo-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.module === module);
        });
        loadModuleConfig(module);
    }

    function toggleSection(section, checked) {
        document.querySelectorAll(`input[type="checkbox"][data-section="${section}"]`).forEach(cb => {
            cb.checked = checked;
        });
        markDirty(true);
    }

    function saveConfig() {
        const items = [];
        let order = 0;
        
        document.querySelectorAll('input[type="checkbox"][data-section]').forEach(checkbox => {
            items.push({
                module: currentModule,
                section: checkbox.dataset.section,
                field_name: checkbox.dataset.field,
                is_enabled: checkbox.checked,
                order: order++
            });
        });
        
        showToast('info', 'Saving configuration to database...');
        
        fetch('/api/shipment-memo-configs/bulk-save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ items })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                markDirty(false);
                showToast('success', data.message || 'Configuration saved successfully');
            } else {
                showToast('error', data.message || 'Failed to save configuration');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            showToast('error', 'Failed to save configuration');
        });
    }

    function showToast(type, msg) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        toast.className = 'toast ' + type;
        toast.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> <span>${msg}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function initPage() {
        const activeTab = document.querySelector('.memo-tab.active');
        const module = activeTab ? activeTab.dataset.module : 'ocean-export';
        loadModuleConfig(module);
    }

    // Bind page load & SPA/Turbo transitions
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initPage();
    } else {
        document.addEventListener('DOMContentLoaded', initPage);
    }
    document.addEventListener('turbo:load', initPage);
    document.addEventListener('turbolinks:load', initPage);
    </script>
</x-layout>
