<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .tp-credit-entry-page .grid-wrapper { height: calc(100vh - 260px); min-height: 300px; overflow: auto; width: 100%; }
        .tp-credit-entry-page .grid-table input[type="text"],
        .tp-credit-entry-page .grid-table input[type="number"],
        .tp-credit-entry-page .grid-table select {
            width: 100%; height: 22px; border: 1px solid #cbd5e1; padding: 0 6px;
            font-size: 10px; border-radius: 2px; outline: none; box-sizing: border-box;
            background: #fff; color: #1e293b;
        }
        .tp-credit-entry-page .grid-table input:focus,
        .tp-credit-entry-page .grid-table select:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 1px rgba(59,130,246,0.25);
        }
        .tp-credit-entry-page .grid-table td.editable-cell { padding: 1px 2px; }
        .tp-credit-entry-page .summary-bar { background: #f8fafc; padding: 6px 12px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 16px; font-size: 11px; color: #64748b; align-items: center; }
        .tp-credit-entry-page .summary-bar .val { color: #1e293b; font-weight: 600; }
        .tp-credit-entry-page .summary-bar .val.over { color: #dc2626; }
        .tp-credit-entry-page .sticky-save-bar { position: sticky; bottom: 0; background: #fff; border-top: 2px solid #3b82f6; padding: 8px 16px; display: flex; justify-content: center; gap: 10px; z-index: 50; box-shadow: 0 -4px 12px rgba(0,0,0,0.08); flex-wrap: wrap; }
        .tp-credit-entry-page .btn-primary-save { background: #3b82f6; color: #fff; border: none; padding: 6px 30px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
        .tp-credit-entry-page .btn-primary-save:hover { background: #2563eb; }
        .tp-credit-entry-page .btn-primary-save:disabled { opacity: 0.5; cursor: not-allowed; }
        .tp-credit-entry-page .btn-secondary { background: #fff; color: #475569; border: 1px solid #cbd5e1; padding: 6px 20px; border-radius: 4px; font-size: 11px; cursor: pointer; }
        .tp-credit-entry-page .btn-secondary:hover { background: #f1f5f9; }

        /* Validation styles */
        .tp-credit-entry-page .field-invalid { border-color: #ef4444 !important; box-shadow: 0 0 0 1px rgba(239,68,68,0.2) !important; }
        .tp-credit-entry-page .validation-error { font-size: 9px; color: #ef4444; display: block; line-height: 1.2; }

        /* ── Ocean Module Tabs ── */
        .tp-credit-entry-page .ce-tabs { display: flex; flex-wrap: wrap; gap: 2px; border-bottom: 1px solid #cbd5e1; padding: 0 12px; background: #fff; margin: 0; list-style: none; overflow-x: auto; }
        .tp-credit-entry-page .ce-tabs li { margin-bottom: -1px; list-style: none; }
        .tp-credit-entry-page .ce-tabs li a {
            display: inline-flex; align-items: center; padding: 7px 16px; font-size: 11px; font-weight: 600;
            color: #64748b; text-decoration: none;
            border: 1px solid transparent; border-bottom: none;
            border-radius: 3px 3px 0 0;
            transition: all 0.15s ease;
            position: relative; white-space: nowrap;
        }
        .tp-credit-entry-page .ce-tabs li a:hover { color: #1e293b; background: #f1f5f9; }
        .tp-credit-entry-page .ce-tabs li.active a {
            color: #1e293b; background: #fff;
            border-color: #cbd5e1; border-bottom-color: #fff;
        }
        .tp-credit-entry-page .ce-tabs li.active a::after {
            content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
            height: 2px; background: #3b82f6;
        }

        /* ── Action Buttons ── */
        .tp-credit-entry-page .btn-action-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 3px; border: 1px solid #cbd5e1;
            background: #ffffff; color: #475569; font-size: 11px; cursor: pointer;
            transition: all 0.15s ease; box-sizing: border-box; line-height: 1;
        }
        .tp-credit-entry-page .btn-action-icon:hover { background: #f1f5f9; border-color: #94a3b8; color: #1e293b; }
        .tp-credit-entry-page .btn-action-icon.edit:hover { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
        .tp-credit-entry-page .btn-action-icon.delete:hover { background: #fef2f2; color: #ef4444; border-color: #fca5a5; }

        .tp-credit-entry-page .btn-tool.danger-outline { background: #fff; color: #ef4444; border-color: #fca5a5; }
        .tp-credit-entry-page .btn-tool.danger-outline:hover:not(:disabled) { background: #fef2f2; border-color: #ef4444; color: #dc2626; }
        .tp-credit-entry-page .btn-tool.danger-outline:disabled { background: #f8fafc; color: #cbd5e1; border-color: #e2e8f0; opacity: 0.6; cursor: not-allowed; }

        /* ── Responsive rules ── */
        @media (max-width: 768px) {
            .tp-credit-entry-page .portlet-title { flex-direction: column; align-items: flex-start; gap: 8px; }
            .tp-credit-entry-page .portlet-title .actions { width: 100%; justify-content: flex-start; }
            .tp-credit-entry-page .portlet-tool { flex-direction: column; align-items: stretch; gap: 8px; }
            .tp-credit-entry-page .summary-bar { font-size: 10px; gap: 8px; }
            .tp-credit-entry-page .grid-wrapper { height: calc(100vh - 340px); }
        }

        /* Fix overlay display conflict with Alpine x-show */
        .tp-credit-entry-page .portlet.light .overlay { display: flex; }
        .tp-credit-entry-page .portlet.light .overlay[x-cloak] { display: none !important; }

        .tp-credit-entry-page .group-name-link { color: #2563eb; font-weight: 600; text-decoration: none; cursor: pointer; }
        .tp-credit-entry-page .group-name-link:hover { text-decoration: underline; color: #1d4ed8; }
        .tp-credit-entry-page .members-count { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 18px; padding: 0 6px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 10px; font-size: 10px; font-weight: 600; }
    </style>
    @endpush

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- MAIN PAGE --}}
    <div class="page-content tp-credit-entry-page">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/trade-partner/list">Customers</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #1e293b; font-weight: 700;">Customer Credit Entry</span></li>
            </ul>
        </div>

        <div class="portlet light" x-data="creditEntryApp()">

            {{-- Confirm Reset Modal (Alpine-native) --}}
            <div class="overlay" x-show="showResetConfirm" x-cloak @click.self="showResetConfirm = false">
                <div class="confirm-box">
                    <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <h4>Reset Changes?</h4>
                    <p x-text="resetConfirmMsg">You have unsaved changes. Discard them?</p>
                    <div class="confirm-actions">
                        <button class="btn-tool" style="padding:0 18px;height:26px;" @click="showResetConfirm = false">Cancel</button>
                        <button class="btn-tool danger" style="padding:0 18px;height:26px;" @click="executeReset()">
                            <i class="fa fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Customer Credit Entry</span>
                    <span style="font-size:10px;color:#64748b;font-weight:400;text-transform:none;">
                        Manage credit terms &amp; limits
                    </span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" id="btn-filter" @click="toggleFilter()" title="Toggle filter row">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <a class="btn-action-round white" href="{{ route('trade-partner.credit-entry.export-csv') }}" title="Download credit entries as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <button class="btn-action-round white" @click="refreshPage()" title="Refresh data">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>

            {{-- TABS — Ocean Module themed --}}
            <ul class="ce-tabs">
                <li :class="{ 'active': activeTab === 'partners' }">
                    <a href="javascript:;" @click="switchTab('partners')"><i class="fa fa-users" style="margin-right:4px;"></i>Customers</a>
                </li>
                <li :class="{ 'active': activeTab === 'groups' }">
                    <a href="javascript:;" @click="switchTab('groups')"><i class="fa fa-tags" style="margin-right:4px;"></i>Credit Limit Groups</a>
                </li>
            </ul>

            {{-- SUMMARY BAR --}}
            <div class="summary-bar" x-show="activeTab === 'partners'">
                <span>Total Partners: <span class="val" x-text="totalPartners"></span></span>
                <span>Credit Limit Total: <span class="val" x-text="formatCurrency(totalCreditLimitAll)"></span></span>
                <span>Modified: <span class="val" x-text="modifiedCount" :class="modifiedCount > 0 ? 'over' : ''">0</span></span>
            </div>

            {{-- ========== CUSTOMERS TAB ========== --}}
            <div x-show="activeTab === 'partners'">
                {{-- TOOLBAR --}}
                <div class="portlet-tool">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <div class="btn-group">
                            <button class="btn-tool green" @click="saveAll()" :disabled="saving || modifiedCount === 0" title="Save all credit entries">
                                <i class="fa fa-save"></i> Save
                            </button>
                            <button class="btn-tool" @click="confirmReset()" :disabled="modifiedCount === 0" title="Reset all changes">
                                <i class="fa fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                        <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                               placeholder="Quick search..." x-model="searchQuery"
                               @input.debounce.400ms="performSearch()">
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper">
                            <table class="grid-table" id="main-grid">
                                <thead>
                                    <tr id="header-row">
                                        <th style="width:50px;">Code</th>
                                        <th style="width:200px;">Customer</th>
                                        <th style="width:100px;">Alias</th>
                                        <th style="width:60px;">Type</th>
                                        <th style="width:150px;">Account Group</th>
                                        <th style="width:100px;">Payment Type</th>
                                        <th style="width:120px;">Credit Term</th>
                                        <th style="width:50px;">Days</th>
                                        <th style="width:110px;">Credit Limit</th>
                                        <th style="width:100px;">Current Balance</th>
                                        <th style="width:100px;">Over Limit</th>
                                        <th style="width:150px;">Remark</th>
                                    </tr>
                                    {{-- FILTER ROW --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td><input class="filter-input" data-param="filter_code" placeholder="Code..." @keyup.enter="applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_name" placeholder="Name..." @keyup.enter="applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_alias" placeholder="Alias..." @keyup.enter="applyFilters()"></td>
                                        <td>
                                            <select class="filter-input" data-param="filter_type" @change="applyFilters()" style="height:18px;">
                                                <option value="">All</option>
                                                @foreach(['CS'=>'Client','CN'=>'Consignee','KS'=>'Shipper(K)','SH'=>'Shipper(U)','PR'=>'Agent','CR'=>'Carrier','AC'=>'Air Carrier','FR'=>'Forwarder','CB'=>'Customs Broker','TK'=>'Trucker','WH'=>'Warehouse','VR'=>'Vendor','BK'=>'Bank'] as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="filter-input" data-param="filter_account_group" @change="applyFilters()" style="height:18px;">
                                                <option value="">All</option>
                                                @foreach($accountGroups as $ag)
                                                    <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="filter-input" data-param="filter_payment" @change="applyFilters()" style="height:18px;">
                                                <option value="">All</option>
                                                <option value="COD">COD</option>
                                                <option value="CREDIT">CREDIT</option>
                                                <option value="PREPAID">PREPAID</option>
                                                <option value="COLLECT">COLLECT</option>
                                            </select>
                                        </td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td>
                                        <td style="text-align:center;"><button class="btn-tool green" @click="applyFilters()" style="height:18px;">Filter</button></td>
                                    </tr>
                                </thead>
                                <tbody id="grid-body">
                                    @forelse($partners as $p)
                                    <tr id="tp-row-{{ $p->id }}" data-id="{{ $p->id }}">
                                        <td style="text-align:center;color:#64748b;font-weight:600;">{{ $p->code }}</td>
                                        <td><span style="color:#3b82f6;font-weight:600;">{{ $p->name }}</span></td>
                                        <td style="color:#64748b;">{{ $p->alias ?? '--' }}</td>
                                        <td style="text-align:center;"><span class="badge-status bg-blue">{{ $p->type }}</span></td>
                                        <td class="editable-cell">
                                            <select x-model="entries[{{ $p->id }}].account_group_id"
                                                    @change="markModified({{ $p->id }})">
                                                <option value="">-- Select --</option>
                                                @foreach($accountGroups as $ag)
                                                    <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="editable-cell">
                                            <select x-model="entries[{{ $p->id }}].payment_type"
                                                    @change="markModified({{ $p->id }})">
                                                <option value="CREDIT">CREDIT</option>
                                                <option value="COD">COD</option>
                                                <option value="PREPAID">PREPAID</option>
                                                <option value="COLLECT">COLLECT</option>
                                            </select>
                                        </td>
                                        <td class="editable-cell">
                                            <select x-model="entries[{{ $p->id }}].credit_term_unit"
                                                    @change="markModified({{ $p->id }})">
                                                <option value="Days">Days after ETA</option>
                                                <option value="Monthly">Monthly</option>
                                                <option value="Weekly">Weekly</option>
                                                <option value="Custom">Custom</option>
                                            </select>
                                        </td>
                                        <td class="editable-cell">
                                            <input type="number" x-model="entries[{{ $p->id }}].credit_term_days"
                                                   @input="markModified({{ $p->id }})"
                                                   min="0" max="9999" step="1"
                                                   :class="{ 'field-invalid': entries[{{ $p->id }}].credit_term_days < 0 }">
                                        </td>
                                        <td class="editable-cell">
                                            <input type="number" x-model="entries[{{ $p->id }}].credit_limit"
                                                   @input="markModified({{ $p->id }})"
                                                   min="0" step="0.01"
                                                   :class="{ 'field-invalid': entries[{{ $p->id }}].credit_limit < 0 }">
                                        </td>
                                        <td style="text-align:right;padding-right:10px;font-weight:600;color:#1e293b;">0.00</td>
                                        <td style="text-align:right;padding-right:10px;color:#16a34a;font-weight:600;">0.00</td>
                                        <td class="editable-cell">
                                            <input type="text" x-model="entries[{{ $p->id }}].remark"
                                                   @input="markModified({{ $p->id }})"
                                                   placeholder="Remark...">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="12" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No customers found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PAGINATION — Ocean Module themed --}}
                <div class="portlet-tool bottom">
                    <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                        <div id="pagination-container">{{ $partners->links('vendor.pagination.ce-credits') }}</div>
                        <div style="font-size:10px;color:#64748b;">
                            Showing <span id="stat-first">{{ $partners->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $partners->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $partners->total() }}</span> records
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== CREDIT LIMIT GROUPS TAB ========== --}}
            <div x-show="activeTab === 'groups'" x-cloak>
                {{-- Toolbar with + and bulk delete --}}
                <div class="portlet-tool">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <div class="btn-group">
                            <button class="btn-tool green" @click="openGroupModal()" title="Add new credit limit group">
                                <i class="fa fa-plus"></i> Add
                            </button>
                            <button class="btn-tool" @click="refreshGroups()" title="Refresh groups list">
                                <i class="fa fa-refresh"></i>
                            </button>
                            <button class="btn-tool danger-outline" @click="confirmBulkDeleteGroups()" :disabled="selectedGroups.size === 0" title="Delete selected groups">
                                <i class="fa fa-trash"></i> Delete (<span x-text="selectedGroups.size">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper" style="height:auto;min-height:200px;">
                            <table class="grid-table" id="groups-table">
                                <thead>
                                    <tr>
                                        <th style="width:25px;text-align:center;">
                                            <input type="checkbox" id="group-select-all" @change="toggleSelectAllGroups($event.target.checked)" title="Select all">
                                        </th>
                                        <th style="width:30px;text-align:center;">#</th>
                                        <th style="width:200px;">Group Name</th>
                                        <th style="width:130px;">Payment Type</th>
                                        <th style="width:120px;">Credit Term</th>
                                        <th style="width:50px;text-align:center;">Days</th>
                                        <th style="width:100px;text-align:right;">Credit Limit</th>
                                        <th style="width:80px;text-align:center;">Members</th>
                                        <th style="width:100px;text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="groups-body">
                                    @forelse($creditLimitGroups as $index => $group)
                                    <tr id="group-row-{{ $group->id }}" :class="{ 'row-selected': selectedGroups.has({{ $group->id }}) }">
                                        <td style="text-align:center;" @click="event.stopPropagation()">
                                            <input type="checkbox" :checked="selectedGroups.has({{ $group->id }})" @change="toggleGroupSelection({{ $group->id }})" class="group-check">
                                        </td>
                                        <td style="text-align:center;color:#64748b;">{{ $index + 1 }}</td>
                                        <td><span class="group-name-link" data-group-edit data-group-id="{{ $group->id }}">{{ $group->name }}</span></td>
                                        <td style="color:#64748b;">{{ $group->payment_type ?? '--' }}</td>
                                        <td style="color:#64748b;">{{ $group->credit_term_unit ?? '--' }}</td>
                                        <td style="text-align:center;">{{ $group->credit_term_days ?? '--' }}</td>
                                        <td style="text-align:right;font-weight:600;">{{ $group->credit_limit ? number_format($group->credit_limit, 2) : '0.00' }}</td>
                                        <td style="text-align:center;">
                                            <span class="members-count">{{ $group->tradePartners->count() }}</span>
                                        </td>
                                        <td style="text-align:center;">
                                            <div style="display:inline-flex;align-items:center;gap:4px;justify-content:center;">
                                                <button type="button" class="btn-action-icon edit" data-group-edit data-group-id="{{ $group->id }}" title="Edit Group">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <button type="button" class="btn-action-icon delete" data-group-delete data-group-id="{{ $group->id }}" title="Delete Group">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="groups-empty">
                                        <td colspan="9" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No credit limit groups defined. Click <strong>+ Add</strong> to create one.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CREDIT LIMIT GROUP MODAL — Add / Edit --}}
            <div class="overlay" x-show="showGroupModal" x-cloak @click.self="closeGroupModal()">
                <div class="modal-box" style="width:460px;">
                    <div class="modal-header">
                        <div class="modal-header-title">
                            <i class="fa fa-tags" style="color:#3b82f6;"></i>
                            <span x-text="groupModalMode === 'edit' ? 'Edit Credit Limit Group' : 'New Credit Limit Group'"></span>
                        </div>
                        <button class="modal-close" @click="closeGroupModal()"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body" style="min-width:auto;">
                        {{-- Name --}}
                        <div style="margin-bottom:14px;">
                            <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">
                                Credit Limit Group Name <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text" x-model="groupForm.name"
                                   placeholder="e.g. Against BL, Premium Customers, ..."
                                   :class="{ 'field-invalid': groupFormErrors.name }"
                                   style="width:100%;height:28px;border:1px solid #cbd5e1;padding:0 8px;font-size:11px;border-radius:3px;outline:none;box-sizing:border-box;">
                            <span x-show="groupFormErrors.name" x-text="groupFormErrors.name" class="validation-error" style="margin-top:2px;"></span>
                        </div>

                        {{-- Payment Type + Credit Term (row) --}}
                        <div style="display:flex;gap:12px;margin-bottom:14px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">Payment Type</label>
                                <select x-model="groupForm.payment_type"
                                        style="width:100%;height:28px;border:1px solid #cbd5e1;padding:0 6px;font-size:11px;border-radius:3px;outline:none;background:#fff;box-sizing:border-box;">
                                    <option value="">-- Select --</option>
                                    <option value="COD">COD</option>
                                    <option value="CREDIT">CREDIT</option>
                                    <option value="PREPAID">PREPAID</option>
                                    <option value="COLLECT">COLLECT</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">Credit Term</label>
                                <select x-model="groupForm.credit_term_unit"
                                        style="width:100%;height:28px;border:1px solid #cbd5e1;padding:0 6px;font-size:11px;border-radius:3px;outline:none;background:#fff;box-sizing:border-box;">
                                    <option value="">-- Select --</option>
                                    <option value="Days">Days after ETA</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                        </div>

                        {{-- Days + Credit Limit (row) --}}
                        <div style="display:flex;gap:12px;margin-bottom:14px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">Days</label>
                                <input type="number" x-model="groupForm.credit_term_days"
                                       min="0" max="9999" step="1"
                                       :class="{ 'field-invalid': groupFormErrors.credit_term_days }"
                                       style="width:100%;height:28px;border:1px solid #cbd5e1;padding:0 8px;font-size:11px;border-radius:3px;outline:none;box-sizing:border-box;">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">Credit Limit</label>
                                <input type="number" x-model="groupForm.credit_limit"
                                       min="0" step="0.01"
                                       :class="{ 'field-invalid': groupFormErrors.credit_limit }"
                                       style="width:100%;height:28px;border:1px solid #cbd5e1;padding:0 8px;font-size:11px;border-radius:3px;outline:none;box-sizing:border-box;">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div style="margin-bottom:14px;">
                            <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:4px;">Description</label>
                            <textarea x-model="groupForm.description"
                                      placeholder="Optional notes..."
                                      style="width:100%;height:50px;border:1px solid #cbd5e1;padding:4px 8px;font-size:10px;border-radius:3px;outline:none;resize:vertical;box-sizing:border-box;font-family:inherit;"></textarea>
                        </div>

                        {{-- Actions --}}
                        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:10px;border-top:1px solid #e2e8f0;">
                            <button class="btn-tool" @click="closeGroupModal()" style="padding:0 16px;height:26px;">Cancel</button>
                            <button class="btn-tool green" @click="saveGroup()" :disabled="groupSaving" style="padding:0 20px;height:26px;">
                                <i class="fa fa-save"></i>
                                <span x-text="groupSaving ? 'Saving...' : 'Save'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DELETE GROUP CONFIRM MODAL (single + bulk) --}}
            <div class="overlay" x-show="showDeleteConfirm" x-cloak @click.self="showDeleteConfirm = false; _bulkDeleteMode = false">
                <div class="confirm-box">
                    <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <h4 x-text="_bulkDeleteMode ? 'Delete Selected Groups?' : 'Delete Credit Limit Group?'"></h4>
                    <p x-text="deleteGroupMsg">This action cannot be undone.</p>
                    <div class="confirm-actions">
                        <button class="btn-tool" style="padding:0 18px;height:26px;" @click="showDeleteConfirm = false; _bulkDeleteMode = false">Cancel</button>
                        <button class="btn-tool danger" style="padding:0 18px;height:26px;" @click="executeDeleteGroup()">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sticky Save Bar --}}
            <div class="sticky-save-bar" x-show="activeTab === 'partners' && modifiedCount > 0" x-cloak>
                <button class="btn-primary-save" @click="saveAll()" :disabled="saving">
                    <i class="fa fa-save"></i> <span x-text="saving ? 'Saving...' : 'Save Changes (' + modifiedCount + ')'"></span>
                </button>
                <button class="btn-secondary" @click="confirmReset()">Cancel</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function creditEntryApp() {
        return {
            activeTab: 'partners',
            saving: false,
            showResetConfirm: false,
            resetConfirmMsg: 'You have unsaved changes. Are you sure you want to discard them?',
            searchQuery: '',
            totalPartners: {{ $partners->total() }},
            totalCreditLimitAll: {{ $totalCreditLimitAll }},
            modifiedIds: new Set(),
            entries: {
                @foreach($partners as $p)
                {{ $p->id }}: {
                    id: {{ $p->id }},
                    account_group_id: '{{ $p->account_group_id ?? '' }}',
                    payment_type: '{{ $p->payment_type ?? 'CREDIT' }}',
                    credit_term_unit: '{{ $p->credit_term_unit ?? 'Days' }}',
                    credit_term_days: {{ $p->credit_term_days ?? 0 }},
                    credit_limit: {{ number_format($p->credit_limit ?? 0, 2, '.', '') }},
                    remark: '{{ addslashes($p->remark ?? '') }}',
                    touched: false,
                    _modified: false
                },
                @endforeach
            },

            // ── Group Modal State ──
            showGroupModal: false,
            showDeleteConfirm: false,
            groupModalMode: 'create', // 'create' | 'edit'
            groupSaving: false,
            editGroupId: null,
            deleteGroupId: null,
            deleteGroupMsg: '',
            selectedGroups: new Set(),
            _bulkDeleteMode: false,
            groupsData: [
                @foreach($creditLimitGroups as $group)
                {
                    id: {{ $group->id }},
                    name: '{{ addslashes($group->name) }}',
                    payment_type: '{{ $group->payment_type ?? '' }}',
                    credit_term_unit: '{{ $group->credit_term_unit ?? '' }}',
                    credit_term_days: {{ $group->credit_term_days ?? 'null' }},
                    credit_limit: {{ $group->credit_limit ?? 'null' }},
                    description: '{{ addslashes($group->description ?? '') }}',
                    trade_partners_count: {{ $group->tradePartners->count() }},
                },
                @endforeach
            ],
            groupForm: {
                name: '',
                payment_type: '',
                credit_term_unit: '',
                credit_term_days: '',
                credit_limit: '',
                description: '',
            },
            groupFormErrors: {},

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const tabFromUrl = urlParams.get('tab');
                const savedTab = sessionStorage.getItem('ceActiveTab');
                if (tabFromUrl && ['partners', 'groups'].includes(tabFromUrl)) {
                    this.activeTab = tabFromUrl;
                } else if (savedTab && ['partners', 'groups'].includes(savedTab)) {
                    this.activeTab = savedTab;
                }

                this.$watch('activeTab', (val) => {
                    sessionStorage.setItem('ceActiveTab', val);
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', val);
                    window.history.replaceState({}, '', url.toString());
                });

                this.$nextTick(() => {
                    this.wirePagination();
                    this.wireGroupEvents();
                });
            },

            get modifiedCount() {
                return this.modifiedIds.size;
            },

            markModified(id) {
                if (this.entries[id]) {
                    this.entries[id]._modified = true;
                    this.entries[id].touched = true;
                    this.modifiedIds.add(id);
                }
            },

            /**
             * Rebuild the entries object from the current DOM after an AJAX grid update.
             * This is critical — without it, new rows rendered by pagination/search
             * would reference non-existent entries[id] keys.
             */
            rebuildEntriesFromDom() {
                const newEntries = {};
                const rows = document.querySelectorAll('#grid-body tr[data-id]');
                rows.forEach(row => {
                    const id = row.dataset.id;
                    if (!id) return;
                    const numId = parseInt(id);
                    newEntries[numId] = {
                        id: numId,
                        account_group_id: this._getSelectValue(row, 'account_group_id') || '',
                        payment_type: this._getSelectValue(row, 'payment_type') || 'CREDIT',
                        credit_term_unit: this._getSelectValue(row, 'credit_term_unit') || 'Days',
                        credit_term_days: parseInt(this._getInputValue(row, 'credit_term_days')) || 0,
                        credit_limit: parseFloat(this._getInputValue(row, 'credit_limit')) || 0,
                        remark: this._getInputValue(row, 'remark') || '',
                        touched: false,
                        _modified: false
                    };
                });
                this.entries = newEntries;
                this.modifiedIds.clear();
            },

            _getSelectValue(row, name) {
                const sel = row.querySelector(`select[name*="${name}"], [x-model*="${name}"]`);
                return sel ? sel.value : '';
            },

            _getInputValue(row, name) {
                const inp = row.querySelector(`input[name*="${name}"], [x-model$=".${name}"]`);
                return inp ? inp.value : '';
            },

            switchTab(tab) {
                this.activeTab = tab;
            },

            toggleFilter() {
                const filterRow = document.getElementById('filter-row');
                if (!filterRow) return;
                const isVisible = filterRow.style.display === 'table-row';
                filterRow.style.display = isVisible ? 'none' : 'table-row';
                document.getElementById('btn-filter')?.classList.toggle('active', !isVisible);
                if (isVisible) {
                    // Clear filters when hiding the row
                    filterRow.querySelectorAll('.filter-input').forEach(i => { i.value = ''; });
                    this.updateGrid(window.location.pathname);
                }
            },

            applyFilters() {
                var url = new URL(window.location.href);
                url.search = '';
                document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                    const v = inp.value?.trim();
                    const param = inp.dataset.param;
                    if (param && v) url.searchParams.set(param, v);
                });
                // Use AJAX instead of full page reload for filters
                this.updateGrid(url.toString());
            },

            performSearch() {
                const q = this.searchQuery.trim();
                const url = new URL(window.location.href);
                if (!q) url.searchParams.delete('search');
                else url.searchParams.set('search', q);
                url.searchParams.delete('page');
                this.updateGrid(url.toString());
            },

            async updateGrid(url) {
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html, */*'
                        }
                    });
                    if (!response.ok) throw new Error('Network error: ' + response.status);
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newBody = doc.getElementById('grid-body');
                    const newPagination = doc.getElementById('pagination-container');

                    if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
                    if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

                    // Update statistics
                    const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
                    if (stats) {
                        const text = stats.textContent;
                        const matches = text.match(/\d+/g);
                        if (matches && matches.length >= 3) {
                            document.getElementById('stat-first').textContent = matches[0];
                            document.getElementById('stat-last').textContent = matches[1];
                            document.getElementById('stat-total').textContent = matches[2];
                        }
                    }

                    // CRITICAL: Rebuild entries from the new DOM to keep Alpine bindings working
                    this.rebuildEntriesFromDom();

                    const total = document.getElementById('stat-total')?.textContent;
                    if (total) this.totalPartners = parseInt(total) || 0;

                    this.wirePagination();
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to update grid');
                }
            },

            wirePagination() {
                const container = document.getElementById('pagination-container');
                if (!container) return;

                const handler = container._paginationHandler;
                if (handler) container.removeEventListener('click', handler);

                const newHandler = (e) => {
                    const link = e.target.closest('.ce-page-link');
                    if (link && link.tagName === 'A') {
                        e.preventDefault();
                        this.updateGrid(link.href);
                    }
                };

                container.addEventListener('click', newHandler);
                container._paginationHandler = newHandler;
            },

            async saveAll() {
                if (this.modifiedIds.size === 0) return;
                this.saving = true;

                // Validate before submit
                let hasErrors = false;
                const entries = [];
                this.modifiedIds.forEach(id => {
                    if (this.entries[id]) {
                        const limit = parseFloat(this.entries[id].credit_limit);
                        const days = parseInt(this.entries[id].credit_term_days);
                        if (limit < 0) hasErrors = true;
                        if (days < 0) hasErrors = true;

                        entries.push({
                            id: parseInt(id),
                            account_group_id: this.entries[id].account_group_id || null,
                            payment_type: this.entries[id].payment_type || 'CREDIT',
                            credit_term_unit: this.entries[id].credit_term_unit || 'Days',
                            credit_term_days: days || 0,
                            credit_limit: limit || 0,
                            remark: this.entries[id].remark || '',
                        });
                    }
                });

                if (hasErrors) {
                    this.showToast('error', 'Please fix validation errors before saving.');
                    this.saving = false;
                    return;
                }

                try {
                    const response = await fetch('{{ route("trade-partner.credit-entry.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ entries })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.showToast('success', data.message || 'Credit entries saved successfully');
                        this.modifiedIds.clear();
                        if (data.totalCreditLimitAll !== undefined) {
                            this.totalCreditLimitAll = data.totalCreditLimitAll;
                        }
                        Object.keys(this.entries).forEach(id => {
                            if (this.entries[id]) {
                                this.entries[id]._modified = false;
                                this.entries[id].touched = false;
                            }
                        });
                        await this.updateGrid(window.location.href);
                    } else {
                        this.showToast('error', data.message || 'Failed to save');
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to save credit entries');
                } finally {
                    this.saving = false;
                }
            },

            confirmReset() {
                if (this.modifiedIds.size === 0) return;
                this.resetConfirmMsg = 'You have ' + this.modifiedIds.size + ' unsaved change(s). Discard them?';
                this.showResetConfirm = true;
            },

            executeReset() {
                this.modifiedIds.clear();
                this.showResetConfirm = false;
                this.showToast('info', 'Changes reset');
            },

            // ── CREDIT LIMIT GROUP CRUD METHODS ──

            openGroupModal() {
                this.groupModalMode = 'create';
                this.editGroupId = null;
                this.groupForm = { name: '', payment_type: '', credit_term_unit: '', credit_term_days: '', credit_limit: '', description: '' };
                this.groupFormErrors = {};
                this.showGroupModal = true;
            },

            closeGroupModal() {
                this.showGroupModal = false;
                this.groupFormErrors = {};
            },

            wireGroupEvents() {
                const tbody = document.getElementById('groups-body');
                if (!tbody) return;
                // Remove old handler to avoid duplicates
                const old = tbody._groupHandler;
                if (old) tbody.removeEventListener('click', old);

                const handler = (e) => {
                    // Edit: click on group name link or pencil button
                    const editTarget = e.target.closest('[data-group-edit]');
                    if (editTarget) {
                        const id = parseInt(editTarget.dataset.groupId);
                        const group = this.groupsData.find(g => g.id === id);
                        if (group) this.editGroupFromData(group);
                        return;
                    }
                    // Delete: click on trash button
                    const delTarget = e.target.closest('[data-group-delete]');
                    if (delTarget) {
                        const id = parseInt(delTarget.dataset.groupId);
                        const group = this.groupsData.find(g => g.id === id);
                        if (group) this.deleteGroup(id, group.name);
                    }
                };
                tbody.addEventListener('click', handler);
                tbody._groupHandler = handler;
            },

            editGroupFromData(group) {
                this.groupModalMode = 'edit';
                this.editGroupId = group.id;
                this.groupFormErrors = {};
                this.groupForm = {
                    name: group.name || '',
                    payment_type: group.payment_type || '',
                    credit_term_unit: group.credit_term_unit || '',
                    credit_term_days: group.credit_term_days ?? '',
                    credit_limit: group.credit_limit ?? '',
                    description: group.description || '',
                };
                this.showGroupModal = true;
            },

            async saveGroup() {
                this.groupFormErrors = {};
                let hasErrors = false;

                if (!this.groupForm.name?.trim()) {
                    this.groupFormErrors.name = 'Group name is required.';
                    hasErrors = true;
                }
                if (this.groupForm.credit_term_days && this.groupForm.credit_term_days < 0) {
                    this.groupFormErrors.credit_term_days = 'Cannot be negative.';
                    hasErrors = true;
                }
                if (this.groupForm.credit_limit && this.groupForm.credit_limit < 0) {
                    this.groupFormErrors.credit_limit = 'Cannot be negative.';
                    hasErrors = true;
                }
                if (hasErrors) return;

                this.groupSaving = true;
                const isEdit = this.groupModalMode === 'edit';
                const url = isEdit
                    ? '{{ route("trade-partner.credit-limit-groups.update", "ID") }}'.replace('ID', this.editGroupId)
                    : '{{ route("trade-partner.credit-limit-groups.store") }}';
                const method = isEdit ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.groupForm)
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.showToast('success', data.message || (isEdit ? 'Group updated.' : 'Group created.'));
                        this.closeGroupModal();
                        await this.refreshGroups();
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                this.groupFormErrors[field] = data.errors[field][0];
                            });
                        }
                        this.showToast('error', data.message || 'Failed to save group.');
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to save group.');
                } finally {
                    this.groupSaving = false;
                }
            },

            // ── Group Bulk Delete ──

            toggleGroupSelection(id) {
                if (this.selectedGroups.has(id)) {
                    this.selectedGroups.delete(id);
                } else {
                    this.selectedGroups.add(id);
                }
                this.$el.querySelector('#group-select-all').checked =
                    this.selectedGroups.size > 0 && this.selectedGroups.size === this.groupsData.length;
            },

            toggleSelectAllGroups(checked) {
                this.selectedGroups.clear();
                if (checked) {
                    this.groupsData.forEach(g => this.selectedGroups.add(g.id));
                }
            },

            confirmBulkDeleteGroups() {
                if (this.selectedGroups.size === 0) return;
                this._bulkDeleteMode = true;
                this.deleteGroupMsg = 'Delete ' + this.selectedGroups.size + ' selected group(s)? Groups with assigned members will be skipped.';
                this.showDeleteConfirm = true;
            },

            async executeBulkDeleteGroups() {
                if (this.selectedGroups.size === 0) return;
                const ids = [...this.selectedGroups];

                try {
                    const response = await fetch('{{ route("trade-partner.credit-limit-groups.bulk-delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.showToast('success', data.message || 'Groups deleted.');
                        this.selectedGroups.clear();
                        await this.refreshGroups();
                    } else {
                        this.showToast('error', data.message || 'Failed to delete groups.');
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to delete groups.');
                }
            },

            deleteGroup(id, name) {
                this.deleteGroupId = id;
                this.deleteGroupMsg = 'Are you sure you want to delete "' + name + '"? Customers assigned to this group will have their credit limit group cleared.';
                this.showDeleteConfirm = true;
            },

            async executeDeleteGroup() {
                this.showDeleteConfirm = false;

                if (this._bulkDeleteMode) {
                    this._bulkDeleteMode = false;
                    await this.executeBulkDeleteGroups();
                    return;
                }

                if (!this.deleteGroupId) return;
                try {
                    const response = await fetch(
                        '{{ route("trade-partner.credit-limit-groups.destroy", "ID") }}'.replace('ID', this.deleteGroupId),
                        {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        }
                    );
                    const data = await response.json();
                    if (data.success) {
                        this.showToast('success', data.message || 'Group deleted.');
                        this.deleteGroupId = null;
                        await this.refreshGroups();
                    } else {
                        this.showToast('error', data.message || 'Failed to delete group.');
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to delete group.');
                }
            },

            async refreshGroups() {
                try {
                    const response = await fetch('{{ route("trade-partner.credit-limit-groups.list") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.success && data.data) {
                        this.groupsData = data.data;
                        const tbody = document.getElementById('groups-body');
                        if (!tbody) return;

                        if (data.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:30px 10px;color:#94a3b8;"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>No credit limit groups defined. Click <strong>+ Add</strong> to create one.</td></tr>';
                            return;
                        }

                        tbody.innerHTML = data.data.map((g, i) => {
                            const limit = parseFloat(g.credit_limit || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            const isChecked = this.selectedGroups.has(g.id);
                            return '<tr id="group-row-' + g.id + '" class="' + (isChecked ? 'row-selected' : '') + '">'
                                + '<td style="text-align:center;" onclick="event.stopPropagation()">'
                                + '<input type="checkbox" ' + (isChecked ? 'checked' : '') + ' class="group-check" data-id="' + g.id + '">'
                                + '</td>'
                                + '<td style="text-align:center;color:#64748b;">' + (i + 1) + '</td>'
                                + '<td><span class="group-name-link" data-group-edit data-group-id="' + g.id + '">' + g.name + '</span></td>'
                                + '<td style="color:#64748b;">' + (g.payment_type || '--') + '</td>'
                                + '<td style="color:#64748b;">' + (g.credit_term_unit || '--') + '</td>'
                                + '<td style="text-align:center;">' + (g.credit_term_days ?? '--') + '</td>'
                                + '<td style="text-align:right;font-weight:600;">' + limit + '</td>'
                                + '<td style="text-align:center;"><span class="members-count">' + (g.trade_partners_count || 0) + '</span></td>'
                                + '<td style="text-align:center;">'
                                + '<div style="display:inline-flex;align-items:center;gap:4px;justify-content:center;">'
                                + '<button type="button" class="btn-action-icon edit" data-group-edit data-group-id="' + g.id + '" title="Edit Group"><i class="fa fa-pencil"></i></button>'
                                + '<button type="button" class="btn-action-icon delete" data-group-delete data-group-id="' + g.id + '" title="Delete Group"><i class="fa fa-trash"></i></button>'
                                + '</div>'
                                + '</td></tr>';
                        }).join('');

                        this.wireGroupEvents();
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('error', 'Failed to refresh groups.');
                }
            },

            refreshPage() {
                window.location.href = window.location.pathname;
            },

            formatCurrency(val) {
                return parseFloat(val || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },

            showToast(type, msg) {
                const icons = {
                    success: 'check-circle',
                    error: 'times-circle',
                    info: 'info-circle',
                    warning: 'exclamation-triangle'
                };
                const t = document.createElement('div');
                t.className = 'toast ' + type;
                t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '\"></i> ' + msg;
                document.getElementById('toast-container').appendChild(t);
                setTimeout(() => t.remove(), 3500);
            }
        };
    }
    </script>
    @endpush
</x-layout>
