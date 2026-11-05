<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* User Management - Ocean Import Parity */
        .user-table input[type="text"],
        .user-table input[type="email"],
        .user-table select {
            width: 100%;
            height: 22px;
            padding: 2px 6px;
            border: 1px solid transparent;
            background: transparent;
            font-size: 10px;
            color: #334155;
            border-radius: 2px;
        }
        
        .user-table input:focus,
        .user-table select:focus {
            border-color: #3b82f6;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 1px rgba(59,130,246,0.1);
        }

        .user-table input[readonly] {
            color: #475569;
            font-weight: 600;
            cursor: not-allowed;
        }
        
        .user-table tbody tr.unsaved {
            background-color: #fef3c7 !important;
            border-left: 3px solid #f59e0b;
        }
        
        .btn-reset-pwd {
            background: #3b82f6;
            color: #fff;
            border: 1px solid #2563eb;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }
        
        .btn-reset-pwd:hover {
            background: #2563eb;
        }

        .btn-delete-row {
            color: #ef4444;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 12px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .btn-delete-row:hover {
            color: #dc2626;
            transform: scale(1.15);
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Toast Container --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- Delete Confirmation Modal --}}
    <div class="overlay" id="delete-overlay" style="display:none;">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete User?</h4>
            <p id="delete-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeDeleteConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Reset Password Modal --}}
    <div class="overlay" id="reset-pwd-overlay" style="display:none;">
        <div class="modal-box" style="min-width:400px;">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-lock" style="color:#3b82f6;"></i> Reset Password</div>
                <button class="modal-close" onclick="closeResetPwd()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-size:10px;font-weight:600;margin-bottom:4px;color:#334155;">New Password</label>
                    <input type="password" id="new-password" class="input-inline" style="width:100%;height:26px;font-size:11px;" placeholder="Enter new password (min 6 chars)">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:10px;font-weight:600;margin-bottom:4px;color:#334155;">Re-type New Password</label>
                    <input type="password" id="new-password-confirm" class="input-inline" style="width:100%;height:26px;font-size:11px;" placeholder="Confirm new password">
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeResetPwd()">Close</button>
                    <button class="btn-tool green" style="padding:0 18px;height:26px;" onclick="saveResetPwd()">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Module Permissions Modal --}}
    <div class="overlay" id="module-overlay" style="display:none;">
        <div class="modal-box" style="min-width:480px; max-width:600px;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:12px 18px; display:flex; justify-content:space-between; align-items:center;">
                <div class="modal-header-title" style="font-weight:700; font-size:12px; color:#1e293b;">
                    <i class="fa fa-shield" style="color:#0284c7; margin-right:6px;"></i> Module Permissions: <span id="module-user-name" style="color:#0284c7;"></span>
                </div>
                <button type="button" class="modal-close" onclick="closeModuleModal()" style="background:none; border:none; font-size:16px; cursor:pointer; color:#64748b;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 16px 20px;">
                <p style="font-size: 11px; color:#64748b; margin-bottom: 12px;">
                    Select the modules that this employee is authorized to access:
                </p>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:6px; border-bottom:1px dashed #cbd5e1;">
                    <span style="font-size:10px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">Company Modules</span>
                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="selectAllModules(true)" style="background:none; border:none; color:#0284c7; font-size:10px; font-weight:700; cursor:pointer;">Select All</button>
                        <span style="color:#cbd5e1;">|</span>
                        <button type="button" onclick="selectAllModules(false)" style="background:none; border:none; color:#ef4444; font-size:10px; font-weight:700; cursor:pointer;">Deselect All</button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 8px; max-height: 280px; overflow-y: auto; padding: 4px;">
                    @foreach($availableModules ?? [] as $modKey => $modInfo)
                    <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; cursor:pointer; font-size:11px; font-weight:500; transition:all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <input type="checkbox" class="module-checkbox" value="{{ $modKey }}" style="cursor:pointer; width:14px; height:14px;">
                        <i class="fa {{ $modInfo['icon'] ?? 'fa-cube' }}" style="color:#0284c7; width:16px; text-align:center;"></i>
                        <span>{{ $modInfo['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn-tool" style="padding:0 16px; height:26px;" onclick="closeModuleModal()">Cancel</button>
                <button type="button" class="btn-tool green" style="padding:0 18px; height:26px;" id="btn-save-modules" onclick="saveModuleAccess()">
                    <i class="fa fa-check"></i> Save Permissions
                </button>
            </div>
        </div>
    </div>

    {{-- Datalist for Role Suggestions --}}
    <datalist id="role-suggestions">
        <option value="Operation">
        <option value="Operation Manager">
        <option value="Sales">
        <option value="Sales Manager">
        <option value="Accounting">
        <option value="Accounting Manager">
        <option value="Documentation Officer">
        <option value="Logistics Coordinator">
        <option value="Admin">
    </datalist>

    {{-- Datalist for Office Suggestions --}}
    <datalist id="office-suggestions">
        @foreach(\App\Models\Office::where('is_active', 1)->orWhereNull('is_active')->get() as $off)
        <option value="{{ $off->code }} - {{ $off->name }}">
        @endforeach
    </datalist>

    <div class="page-content">
        {{-- Breadcrumb --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/settings">Settings</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">User Management</span></li>
            </ul>
        </div>

        <div class="portlet light">
            
            {{-- Title --}}
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject">USER MANAGEMENT</span>
                </div>
                <div class="actions" style="display:flex;gap:4px;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button class="btn-action-round white" onclick="exportExcel()" title="Export to Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:6px;align-items:center;">
                    <button class="btn-tool green" onclick="addUser()" title="Add New User">
                        <i class="fa fa-plus"></i> Add User
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:150px;" placeholder="Quick search..." oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- Table --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table user-table" id="user-table">
                            <thead>
                                <tr id="header-row">
                                    <th style="width:30px;text-align:center;"><i class="fa fa-plus"></i></th>
                                    <th style="width:30px;text-align:center;"><i class="fa fa-trash"></i></th>
                                    <th style="width:100px;">User ID</th>
                                    <th style="width:120px;">First Name</th>
                                    <th style="width:120px;">Last Name</th>
                                    <th style="width:200px;">Email</th>
                                    <th style="width:150px;">Office (Code - Name)</th>
                                    <th style="width:180px;">Department (Code - Name)</th>
                                    <th style="width:120px;">Branch</th>
                                    <th style="width:140px;">Role</th>
                                    <th style="width:130px;text-align:center;">Module Access</th>
                                    <th style="width:80px;">Status</th>
                                    <th style="width:100px;">Create Date</th>
                                    <th style="width:120px;text-align:center;">Reset Password</th>
                                </tr>

                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td colspan="2"></td>
                                    <td><input class="filter-input" data-field="user_id" placeholder="User ID..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="first_name" placeholder="First Name..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="last_name" placeholder="Last Name..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="email" placeholder="Email..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="office" placeholder="Office..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="department" placeholder="Department..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="branch" placeholder="Branch..." oninput="applyFilter()"></td>
                                    <td><input class="filter-input" data-field="role" placeholder="Role..." oninput="applyFilter()"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="user-tbody">
                                @forelse($users as $user)
                                <tr data-id="{{ $user->id }}" id="row-{{ $user->id }}">
                                    <td></td>
                                    <td style="text-align:center;">
                                        <button class="btn-delete-row" onclick="confirmDelete(this)" title="Delete user">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->user_id }}" data-field="user_id" onchange="markUnsaved(this)" oninput="markUnsaved(this)" readonly style="background:#f8fafc;">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->first_name }}" data-field="first_name" onchange="markUnsaved(this)" oninput="markUnsaved(this)">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->last_name }}" data-field="last_name" onchange="markUnsaved(this)" oninput="markUnsaved(this)">
                                    </td>
                                    <td>
                                        <input type="email" value="{{ $user->email }}" data-field="email" onchange="markUnsaved(this)" oninput="markUnsaved(this)">
                                    </td>
                                    <td>
                                        <input type="text" list="office-suggestions" value="{{ $user->office_code ? ($user->office_code . ($user->office_name ? ' - ' . $user->office_name : '')) : '' }}" data-field="office" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Code - Name">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->department_code ? ($user->department_code . ($user->department_name ? ' - ' . $user->department_name : '')) : '' }}" data-field="department" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Code - Name">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->branch }}" data-field="branch" onchange="markUnsaved(this)" oninput="markUnsaved(this)">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $user->role }}" data-field="role" list="role-suggestions" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Enter role..." style="font-weight: 500;">
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-module-access" 
                                                onclick="openModuleModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ json_encode($user->accessible_modules ?? []) }})" 
                                                style="background:#f0f9ff; color:#0284c7; border:1px solid #bae6fd; font-size:10px; font-weight:600; padding:2px 8px; border-radius:3px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"
                                                title="Configure Module Access Permissions">
                                            <i class="fa fa-th-large"></i> Modules (<span id="mod-count-{{ $user->id }}">{{ count($user->accessible_modules ?? []) }}</span>)
                                        </button>
                                    </td>
                                    <td>
                                        <select data-field="status" onchange="markUnsaved(this)">
                                            <option value="Enable" {{ $user->status === 'Enable' ? 'selected' : '' }}>Enable</option>
                                            <option value="Disable" {{ $user->status === 'Disable' ? 'selected' : '' }}>Disable</option>
                                        </select>
                                    </td>
                                    <td style="text-align:center;color:#64748b;font-size:10px;">
                                        {{ $user->create_date ? (is_string($user->create_date) ? $user->create_date : $user->create_date->format('m-d-Y')) : '' }}
                                    </td>
                                    <td style="text-align:center;">
                                        <button class="btn-reset-pwd" onclick="openResetPwd({{ $user->id }})">Reset Password</button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="14" style="text-align:center;padding:30px;color:#94a3b8;">No employees found. Click "+ Add User" above to create an employee.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Save Footer --}}
            <div class="portlet-tool bottom" style="padding:10px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                <div style="display:flex;gap:8px;justify-content:center;width:100%;">
                    <button class="btn-tool green" id="btn-save" onclick="saveAll()" disabled style="padding:6px 24px;font-size:12px;font-weight:600;">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
    // Global Helper functions attached to window
    window.getCsrfToken = function() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    };

    window.unsavedRows = new Set();
    window.userToDelete = null;
    window.userToResetPwd = null;

    // Auto-generate User ID helper
    window.getNextAutoUserId = function() {
        let maxNum = 0;
        document.querySelectorAll('#user-tbody tr[data-id]').forEach(tr => {
            const userIdInput = tr.querySelector('[data-field="user_id"]');
            const val = userIdInput ? userIdInput.value : '';
            const match = val.match(/USR-(\d+)/i);
            if (match) {
                const num = parseInt(match[1]);
                if (num > maxNum) maxNum = num;
            }
        });
        const nextNum = maxNum > 0 ? maxNum + 1 : document.querySelectorAll('#user-tbody tr[data-id]').length + 1;
        return 'USR-' + String(nextNum).padStart(4, '0');
    };

    // Toast Notifications
    window.showToast = function(type, msg) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = '<i class="fa fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + '"></i> ' + msg;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // Toggle Filter Row
    window.toggleFilter = function() {
        const row = document.getElementById('filter-row');
        const btn = document.getElementById('btn-filter');
        if (!row || !btn) return;
        if (row.style.display === 'none') {
            row.style.display = '';
            btn.style.background = '#32c5d2';
            btn.style.color = '#fff';
        } else {
            row.style.display = 'none';
            btn.style.background = '';
            btn.style.color = '';
        }
    };

    // Quick Search
    window.quickSearch = function(query) {
        const tbody = document.getElementById('user-tbody');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr[data-id]');
        const q = query.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    };

    // Apply Filter
    window.applyFilter = function() {
        const filters = {};
        document.querySelectorAll('.filter-input').forEach(input => {
            if (input.value) {
                filters[input.dataset.field] = input.value.toLowerCase();
            }
        });

        const tbody = document.getElementById('user-tbody');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr[data-id]');

        rows.forEach(row => {
            let show = true;
            for (let field in filters) {
                const input = row.querySelector(`[data-field="${field}"]`);
                if (input) {
                    const value = (input.value || '').toLowerCase();
                    if (!value.includes(filters[field])) {
                        show = false;
                        break;
                    }
                }
            }
            row.style.display = show ? '' : 'none';
        });
    };

    // Add New User Row with Auto-Generated User ID
    window.addUser = function() {
        const tbody = document.getElementById('user-tbody');
        if (!tbody) return;

        const emptyRow = document.getElementById('empty-row');
        if (emptyRow) emptyRow.remove();

        const newId = 'new-' + Date.now();
        const autoUserId = window.getNextAutoUserId();

        const tr = document.createElement('tr');
        tr.dataset.id = newId;
        tr.id = 'row-' + newId;
        tr.classList.add('unsaved');
        tr.innerHTML = `
            <td></td>
            <td style="text-align:center;">
                <button class="btn-delete-row" onclick="confirmDelete(this)" title="Delete user">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
            <td>
                <input type="text" value="${autoUserId}" data-field="user_id" onchange="markUnsaved(this)" oninput="markUnsaved(this)" readonly style="background:#f8fafc;font-weight:600;color:#2563eb;">
            </td>
            <td>
                <input type="text" value="" data-field="first_name" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="First name" style="font-weight:600;">
            </td>
            <td>
                <input type="text" value="" data-field="last_name" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Last name">
            </td>
            <td>
                <input type="email" value="${autoUserId.toLowerCase()}@fms.com" data-field="email" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="email@domain.com">
            </td>
            <td>
                <input type="text" value="" data-field="office" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Code - Name">
            </td>
            <td>
                <input type="text" value="" data-field="department" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Code - Name">
            </td>
            <td>
                <input type="text" value="" data-field="branch" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="Branch">
            </td>
            <td>
                <input type="text" value="Operation" data-field="role" list="role-suggestions" onchange="markUnsaved(this)" oninput="markUnsaved(this)" placeholder="e.g. Operation, Sales" style="font-weight:500;">
            </td>
            <td style="text-align:center;color:#94a3b8;font-size:9px;">Save user first</td>
            <td>
                <select data-field="status" onchange="markUnsaved(this)">
                    <option value="Enable" selected>Enable</option>
                    <option value="Disable">Disable</option>
                </select>
            </td>
            <td style="text-align:center;color:#94a3b8;font-size:10px;">--</td>
            <td style="text-align:center;color:#94a3b8;font-size:9px;">Save first</td>
        `;

        tbody.insertBefore(tr, tbody.firstChild);
        window.unsavedRows.add(newId);
        window.updateSaveBtn();

        const firstNameInput = tr.querySelector('[data-field="first_name"]');
        if (firstNameInput) firstNameInput.focus();

        window.showToast('info', 'New user added with User ID: ' + autoUserId + '. Enter details and click Save Changes.');
    };

    // Mark Row as Unsaved
    window.markUnsaved = function(input) {
        const tr = input.closest('tr');
        if (!tr) return;
        tr.classList.add('unsaved');
        window.unsavedRows.add(tr.dataset.id);
        window.updateSaveBtn();
    };

    // Update Save Button State
    window.updateSaveBtn = function() {
        const btn = document.getElementById('btn-save');
        if (!btn) return;
        const count = window.unsavedRows.size;
        btn.disabled = count === 0;
        btn.innerHTML = count > 0 
            ? `<i class="fa fa-save"></i> Save Changes (${count})`
            : '<i class="fa fa-check"></i> All Saved';
    };

    // Collect Row Data
    window.collectRowData = function(tr) {
        const data = {
            id: tr.dataset.id && !tr.dataset.id.startsWith('new-') ? tr.dataset.id : null
        };
        
        tr.querySelectorAll('[data-field]').forEach(input => {
            const field = input.dataset.field;
            
            if (field === 'office') {
                const parts = (input.value || '').split(' - ');
                data.office_code = parts[0]?.trim() || '';
                data.office_name = parts[1]?.trim() || '';
            } else if (field === 'department') {
                const parts = (input.value || '').split(' - ');
                data.department_code = parts[0]?.trim() || '';
                data.department_name = parts[1]?.trim() || '';
            } else {
                data[field] = input.value;
            }
        });
        
        return data;
    };

    // Save All Unsaved Rows Atomically
    window.saveAll = function() {
        if (window.unsavedRows.size === 0) {
            window.showToast('info', 'No changes to save');
            return;
        }

        const payloadUsers = [];
        let hasValidationError = false;

        window.unsavedRows.forEach(id => {
            const tr = document.querySelector(`tr[data-id="${id}"]`);
            if (!tr) return;

            const rowData = window.collectRowData(tr);
            const firstNameInput = tr.querySelector('[data-field="first_name"]');
            const emailInput = tr.querySelector('[data-field="email"]');

            if (!rowData.first_name && !rowData.email) {
                window.showToast('error', 'First name or Email is required');
                if (firstNameInput) firstNameInput.style.borderColor = '#ef4444';
                hasValidationError = true;
                return;
            }

            payloadUsers.push(rowData);
        });

        if (hasValidationError || payloadUsers.length === 0) {
            return;
        }

        window.showToast('info', 'Saving ' + payloadUsers.length + ' user(s) to database...');

        fetch('/api/users/bulk-save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.getCsrfToken()
            },
            body: JSON.stringify({ users: payloadUsers })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.unsavedRows.clear();
                window.updateSaveBtn();
                window.showToast('success', data.message || 'Users saved successfully to database');
                setTimeout(() => {
                    window.location.reload();
                }, 600);
            } else {
                window.showToast('error', data.message || 'Failed to save users');
            }
        })
        .catch(err => {
            console.error('Bulk save error:', err);
            window.showToast('error', 'Failed to save users to database');
        });
    };

    // Delete confirmation
    window.confirmDelete = function(btn) {
        window.userToDelete = btn.closest('tr');
        const overlay = document.getElementById('delete-overlay');
        if (overlay) overlay.style.display = 'flex';
    };

    window.closeDeleteConfirm = function() {
        window.userToDelete = null;
        const overlay = document.getElementById('delete-overlay');
        if (overlay) overlay.style.display = 'none';
    };

    window.executeDelete = function() {
        if (!window.userToDelete) return;
        
        const id = window.userToDelete.dataset.id;
        
        if (String(id).startsWith('new-')) {
            window.userToDelete.remove();
            window.unsavedRows.delete(id);
            window.updateSaveBtn();
            window.closeDeleteConfirm();
            window.showToast('info', 'Row removed');
            return;
        }
        
        window.showToast('info', 'Deleting user from database...');

        fetch(`/api/users/${id}`, {
            method: 'DELETE',
            headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': window.getCsrfToken()}
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.userToDelete.remove();
                window.unsavedRows.delete(id);
                window.updateSaveBtn();
                window.showToast('success', data.message || 'User deleted successfully');
            } else {
                window.showToast('error', data.message || 'Failed to delete user');
            }
            window.closeDeleteConfirm();
        })
        .catch(err => {
            console.error(err);
            window.showToast('error', 'Failed to delete user');
            window.closeDeleteConfirm();
        });
    };

    // Reset password
    window.openResetPwd = function(userId) {
        window.userToResetPwd = userId;
        document.getElementById('new-password').value = '';
        document.getElementById('new-password-confirm').value = '';
        const overlay = document.getElementById('reset-pwd-overlay');
        if (overlay) overlay.style.display = 'flex';
    };

    window.closeResetPwd = function() {
        window.userToResetPwd = null;
        const overlay = document.getElementById('reset-pwd-overlay');
        if (overlay) overlay.style.display = 'none';
    };

    window.saveResetPwd = function() {
        const newPwd = document.getElementById('new-password').value;
        const confirmPwd = document.getElementById('new-password-confirm').value;
        
        if (!newPwd || newPwd.length < 6) {
            window.showToast('error', 'Password must be at least 6 characters');
            return;
        }
        
        if (newPwd !== confirmPwd) {
            window.showToast('error', 'Passwords do not match');
            return;
        }
        
        window.showToast('info', 'Resetting password...');

        fetch(`/api/users/${window.userToResetPwd}/reset-password`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.getCsrfToken()},
            body: JSON.stringify({
                new_password: newPwd,
                new_password_confirmation: confirmPwd
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.showToast('success', data.message || 'Password reset successfully');
                window.closeResetPwd();
            } else {
                window.showToast('error', data.message || 'Failed to reset password');
            }
        })
        .catch(err => {
            console.error(err);
            window.showToast('error', 'Failed to reset password');
        });
    };

    // Excel export
    window.exportExcel = function() {
        window.showToast('info', 'Preparing Excel export...');
        
        fetch('/settings/user-management/export-csv', {
            headers: {'Accept': 'text/csv', 'X-CSRF-TOKEN': window.getCsrfToken()}
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'users-' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            window.showToast('success', 'Excel file downloaded successfully');
        })
        .catch(err => {
            console.error(err);
            window.showToast('error', 'Failed to export');
        });
    };

    // Module Access Modal Management
    window.activeModuleUserId = null;

    window.openModuleModal = function(userId, userName, modules) {
        window.activeModuleUserId = userId;
        document.getElementById('module-user-name').textContent = userName || ('User #' + userId);
        
        document.querySelectorAll('.module-checkbox').forEach(cb => {
            cb.checked = Array.isArray(modules) && modules.includes(cb.value);
        });
        
        const overlay = document.getElementById('module-overlay');
        if (overlay) overlay.style.display = 'flex';
    };

    window.closeModuleModal = function() {
        window.activeModuleUserId = null;
        const overlay = document.getElementById('module-overlay');
        if (overlay) overlay.style.display = 'none';
    };

    window.selectAllModules = function(select) {
        document.querySelectorAll('.module-checkbox').forEach(cb => {
            cb.checked = !!select;
        });
    };

    window.saveModuleAccess = function() {
        if (!window.activeModuleUserId) return;
        
        const selected = [];
        document.querySelectorAll('.module-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });
        
        const btn = document.getElementById('btn-save-modules');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        }
        
        fetch(`/api/users/${window.activeModuleUserId}/module-access`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.getCsrfToken()
            },
            body: JSON.stringify({ modules: selected })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const countEl = document.getElementById('mod-count-' + window.activeModuleUserId);
                if (countEl) countEl.textContent = data.count !== undefined ? data.count : selected.length;
                
                const btnEl = countEl?.closest('.btn-module-access');
                if (btnEl) {
                    btnEl.setAttribute('onclick', `openModuleModal(${window.activeModuleUserId}, '${document.getElementById('module-user-name').textContent.replace(/'/g, "\\'")}', ${JSON.stringify(data.modules || selected)})`);
                }
                
                window.showToast('success', data.message || 'Module access updated successfully');
                window.closeModuleModal();
            } else {
                window.showToast('error', data.message || 'Failed to update module access');
            }
        })
        .catch(err => {
            console.error('Module access save error:', err);
            window.showToast('error', 'Failed to update module access');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check"></i> Save Permissions';
            }
        });
    };

    // Page initialization
    function initUserManagementPage() {
        window.updateSaveBtn();
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initUserManagementPage();
    } else {
        document.addEventListener('DOMContentLoaded', initUserManagementPage);
    }
    document.addEventListener('turbo:load', initUserManagementPage);
    document.addEventListener('turbolinks:load', initUserManagementPage);
    </script>
</x-layout>
