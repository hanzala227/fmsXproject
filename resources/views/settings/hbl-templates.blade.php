<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        [x-cloak] { display: none !important; }
        .editor-container {
            display: flex;
            gap: 15px;
            height: 480px;
        }
        .editor-textarea {
            flex: 3;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            resize: none;
            outline: none;
            line-height: 1.4;
            background: #fafafa;
        }
        .editor-textarea:focus {
            border-color: #2563eb;
            background: #fff;
        }
        .helper-panel {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            overflow-y: auto;
            font-size: 11px;
        }
        .helper-title {
            font-weight: 700;
            margin-bottom: 6px;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .helper-item {
            padding: 3px 6px;
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 3px;
            margin-bottom: 4px;
            font-family: monospace;
            cursor: pointer;
            word-break: break-all;
        }
        .helper-item:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .tab-btn {
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            border-bottom: none;
            margin-right: 2px;
            transition: all 0.15s ease-in-out;
        }
        .tab-btn.active {
            background: #fff;
            color: #2563eb;
            border-top: 2px solid #2563eb;
            height: calc(100% + 1px);
        }
    </style>
    @endpush

    {{-- ═══════ TOAST CONTAINER ═══════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════ DELETE CONFIRM MODAL ═══════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete HBL Template(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════ MAIN PAGE ═══════ --}}
    <div class="page-content" x-data="hblTemplateList()">

        {{-- ── EDIT CONTENT MODAL ── --}}
        <div class="overlay" :class="{'open': showEditContentModal}" @click.self="closeEditModal()" x-cloak>
            <div style="background:#ffffff; width:950px; max-width:92vw; max-height:92vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden;">
                
                {{-- Modal Header --}}
                <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <h3 style="font-size:13px; font-weight:700; color:#1e293b; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa fa-code" style="color:#2563eb;"></i>
                        Edit Template Code: <span x-text="editingTemplate ? editingTemplate.name : ''" style="color:#64748b;"></span>
                    </h3>
                    <button type="button" @click="closeEditModal()" style="background:none; border:none; font-size:22px; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div style="padding:15px 20px; overflow-y:auto; flex:1;" x-if="editingTemplate">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div style="display:flex; align-items:flex-end;">
                            <button type="button" class="tab-btn" :class="{'active': modalTab === 'html'}" @click="modalTab = 'html'">HTML Content (Blade)</button>
                            <button type="button" class="tab-btn" :class="{'active': modalTab === 'css'}" @click="modalTab = 'css'">Custom CSS</button>
                        </div>
                        <div style="font-size:10px; color:#64748b;">
                            <i class="fa fa-info-circle"></i> Placeholders can be inserted by clicking them in the panel.
                        </div>
                    </div>

                    <div class="editor-container">
                        {{-- Editor --}}
                        <textarea x-show="modalTab === 'html'" x-model="editingTemplate.content" class="editor-textarea" id="html-textarea"></textarea>
                        <textarea x-show="modalTab === 'css'" x-model="editingTemplate.css" class="editor-textarea" id="css-textarea" placeholder="/* Custom CSS variables & overrides here */"></textarea>

                        {{-- Helper panel --}}
                        <div class="helper-panel">
                            <div class="helper-title">Shipment Info</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->oceanExport->booking_no }}')">@{{ $hbl->oceanExport->booking_no }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->oceanExport->file_no }}')">@{{ $hbl->oceanExport->file_no }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->oceanExport->etd }}')">@{{ $hbl->oceanExport->etd }}</div>

                            <div class="helper-title" style="margin-top:10px;">HBL Info</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->hbl_no }}')">@{{ $hbl->hbl_no }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->quotation_no }}')">@{{ $hbl->quotation_no }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->vessel_name }}')">@{{ $hbl->vessel_name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->voyage_no }}')">@{{ $hbl->voyage_no }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->pre_carriage_by }}')">@{{ $hbl->pre_carriage_by }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->freight_payable_at }}')">@{{ $hbl->freight_payable_at }}</div>

                            <div class="helper-title" style="margin-top:10px;">Customers</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->shipper->name }}')">@{{ $hbl->shipper->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->shipper->address }}')">@{{ $hbl->shipper->address }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->consignee->name }}')">@{{ $hbl->consignee->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->consignee->address }}')">@{{ $hbl->consignee->address }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->notifyParty->name }}')">@{{ $hbl->notifyParty->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->notifyParty->address }}')">@{{ $hbl->notifyParty->address }}</div>

                            <div class="helper-title" style="margin-top:10px;">Ports</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->placeOfReceipt->name }}')">@{{ $hbl->placeOfReceipt->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->oceanExport->portOfLoading->name }}')">@{{ $hbl->oceanExport->portOfLoading->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->placeOfDischarge->name }}')">@{{ $hbl->placeOfDischarge->name }}</div>
                            <div class="helper-item" @click="insertAtCursor('@{{ $hbl->placeOfDelivery->name }}')">@{{ $hbl->placeOfDelivery->name }}</div>

                            <div class="helper-title" style="margin-top:10px;">Containers Loop</div>
                            <div class="helper-item" style="font-size:9px;" @click="insertAtCursor('@' + 'foreach($hbl->containers as $container)\n' + '@{{ $container->container_no }} / @{{ $container->seal_no }}\n' + '@' + 'endforeach')">@@foreach loop</div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="padding:12px 20px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn-tool" style="padding:0 14px;height:28px;" @click="closeEditModal()">Cancel</button>
                    <button type="button" class="btn-tool green" style="padding:0 14px;height:28px;" @click="saveEditModal()">
                        <i class="fa fa-check"></i> Apply Changes
                    </button>
                </div>
            </div>
        </div>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Settings <i class="fa fa-angle-right"></i></li>
                <li>Ocean Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">HBL Templates</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:12px;">
                    <span class="caption-subject">HBL TEMPLATES</span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round white" @click="printTable()" title="Print this page">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button class="btn-action-round white" @click="exportExcel()" title="Download as CSV">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <button class="btn-tool green" @click="addTemplate()" title="Add New Template">
                            <i class="fa fa-plus"></i> Add Template
                        </button>
                        <button class="btn-tool" :disabled="selectedTemplates.length === 0" @click="confirmDelete()" title="Delete Selected">
                            <i class="fa fa-trash"></i> Delete Selected
                        </button>
                        <button class="btn-tool" @click="saveAll()" :disabled="!hasUnsavedChanges()" :class="{'green': hasUnsavedChanges()}" title="Save All Changes">
                            <i class="fa fa-save"></i> Save All
                        </button>
                        <button class="btn-tool" @click="refreshTemplates()" title="Refresh">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin:0;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" x-model="searchTerm" @input="filterTemplates()" class="input-inline" style="width:180px;"
                           placeholder="Search templates...">
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table">
                            <thead>
                                <tr>
                                    <th style="width:35px;text-align:center;">
                                        <input type="checkbox" @change="toggleSelectAll($event.target.checked)" :checked="allSelected">
                                    </th>
                                    <th style="width:35px;text-align:center;"><i class="fa fa-trash"></i></th>
                                    <th style="width:180px;">Template Identifier (Name)</th>
                                    <th style="width:250px;">Display Title</th>
                                    <th style="width:100px;text-align:center;">Active</th>
                                    <th style="width:140px;text-align:center;">Layout Content</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(tpl, index) in filteredTemplates" :key="tpl.id || index">
                                    <tr :class="{'row-selected': tpl.selected, 'unsaved-row': tpl._unsaved}" style="transition: background-color 0.2s;">
                                        <td style="text-align:center;" @click.stop>
                                            <input type="checkbox" :checked="tpl.selected" @change="tpl.selected = !tpl.selected; updateToolbar()">
                                        </td>
                                        <td style="text-align:center;" @click.stop>
                                            <i class="fa fa-trash" style="color:#ef4444;cursor:pointer;font-size:11px;" 
                                               @click="deleteSingleTemplate(tpl)" title="Delete"></i>
                                        </td>
                                        <td @click.stop>
                                            <input type="text" x-model="tpl.name" 
                                                   class="input-inline" style="width:100%;font-weight:600; border: 1px solid #e2e8f0; padding: 2px 5px;"
                                                   @input="tpl._unsaved = true" placeholder="e.g. NTG_AIR">
                                        </td>
                                        <td @click.stop>
                                            <input type="text" x-model="tpl.title" 
                                                   class="input-inline" style="width:100%; border: 1px solid #e2e8f0; padding: 2px 5px;"
                                                   @input="tpl._unsaved = true" placeholder="e.g. NTG Air Template">
                                        </td>
                                        <td style="text-align:center;" @click.stop>
                                            <input type="checkbox" x-model="tpl.is_active" @change="tpl._unsaved = true" style="width:16px;height:16px;cursor:pointer;">
                                        </td>
                                        <td style="text-align:center;" @click.stop>
                                            <button type="button" class="btn-tool" style="height:22px;padding:0 8px;font-size:10px;background:#3b82f6;color:#fff;" @click="openEditModal(tpl)">
                                                <i class="fa fa-code"></i> Edit Content
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredTemplates.length === 0">
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:40px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No templates found.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function showToast(type, msg) {
            const container = document.getElementById('toast-container');
            if(!container) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle'}"></i> <span>${msg}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        let deleteCallback = null;
        function closeConfirm() {
            document.getElementById('confirm-overlay').classList.remove('open');
            deleteCallback = null;
        }
        function executeDelete() {
            if (deleteCallback) deleteCallback();
        }

        function hblTemplateList() {
            return {
                templates: [],
                filteredTemplates: [],
                selectedTemplates: [],
                searchTerm: '',
                allSelected: false,
                
                // Modal
                showEditContentModal: false,
                editingTemplate: null,
                modalTab: 'html',

                init() {
                    this.loadTemplates();
                },

                async loadTemplates() {
                    try {
                        const response = await fetch('/api/hbl-templates');
                        const data = await response.json();
                        if (data.templates) {
                            this.templates = data.templates.map(t => ({
                                ...t,
                                selected: false,
                                _unsaved: false
                            }));
                            this.filterTemplates();
                        }
                    } catch (error) {
                        showToast('error', 'Failed to load HBL templates.');
                    }
                },

                filterTemplates() {
                    let filtered = this.templates;
                    if (this.searchTerm) {
                        const s = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(t => 
                            t.name?.toLowerCase().includes(s) || 
                            t.title?.toLowerCase().includes(s)
                        );
                    }
                    this.filteredTemplates = filtered;
                    this.updateToolbar();
                },

                refreshTemplates() {
                    this.loadTemplates();
                    showToast('success', 'Refreshed templates list.');
                },

                addTemplate() {
                    const newTpl = {
                        id: null,
                        name: '',
                        title: '',
                        content: '<div>New Template Layout</div>',
                        css: '',
                        is_active: true,
                        selected: false,
                        _unsaved: true
                    };
                    this.templates.unshift(newTpl);
                    this.filterTemplates();
                    showToast('info', 'New template added. Click "Edit Content" to define its layout, and click "Save All" when done.');
                },

                toggleSelectAll(checked) {
                    this.filteredTemplates.forEach(t => t.selected = checked);
                    this.updateToolbar();
                },

                updateToolbar() {
                    this.selectedTemplates = this.filteredTemplates.filter(t => t.selected);
                    this.allSelected = this.filteredTemplates.length > 0 && 
                                      this.selectedTemplates.length === this.filteredTemplates.length;
                },

                confirmDelete() {
                    const n = this.selectedTemplates.length;
                    if (!n) return;
                    document.getElementById('confirm-msg').textContent =
                        `You are about to permanently delete ${n} template(s). This cannot be undone.`;
                    document.getElementById('confirm-overlay').classList.add('open');
                    deleteCallback = () => this.executeBulkDelete();
                },

                async executeBulkDelete() {
                    closeConfirm();
                    const ids = this.selectedTemplates.map(t => t.id).filter(id => id !== null);
                    
                    if (ids.length === 0) {
                        this.templates = this.templates.filter(t => !t.selected || t.id !== null);
                        this.filterTemplates();
                        showToast('success', 'Unsaved templates removed');
                        return;
                    }

                    showToast('info', 'Deleting...');
                    try {
                        const response = await fetch('/api/hbl-templates/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ ids })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.templates = this.templates.filter(t => !ids.includes(t.id));
                            this.filterTemplates();
                            showToast('success', data.message || 'Deleted successfully');
                        } else {
                            showToast('error', data.message || 'Failed to delete');
                        }
                    } catch (error) {
                        showToast('error', 'Failed to delete templates');
                    }
                },

                async deleteSingleTemplate(tpl) {
                    if (!confirm('Delete this template?')) return;

                    if (!tpl.id) {
                        this.templates = this.templates.filter(t => t !== tpl);
                        this.filterTemplates();
                        showToast('success', 'Template removed');
                        return;
                    }

                    showToast('info', 'Deleting...');
                    try {
                        const response = await fetch(`/api/hbl-templates/${tpl.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.templates = this.templates.filter(t => t.id !== tpl.id);
                            this.filterTemplates();
                            showToast('success', 'Template deleted');
                        } else {
                            showToast('error', data.message || 'Failed to delete');
                        }
                    } catch (error) {
                        showToast('error', 'Failed to delete template');
                    }
                },

                hasUnsavedChanges() {
                    return this.templates.some(t => t._unsaved);
                },

                async saveAll() {
                    const unsaved = this.templates.filter(t => t._unsaved);
                    if (unsaved.length === 0) return;

                    showToast('info', `Saving ${unsaved.length} HBL template(s)...`);
                    try {
                        const response = await fetch('/api/hbl-templates/bulk-save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ templates: unsaved })
                        });

                        const data = await response.json();
                        if (data.success) {
                            if (data.templates) {
                                data.templates.forEach((savedTpl, index) => {
                                    const unsavedTpl = unsaved[index];
                                    Object.assign(unsavedTpl, savedTpl);
                                    unsavedTpl._unsaved = false;
                                });
                            }
                            this.filterTemplates();
                            showToast('success', data.message || 'Saved successfully');
                        } else {
                            showToast('error', data.message || 'Failed to save templates');
                        }
                    } catch (error) {
                        showToast('error', 'Failed to save templates');
                    }
                },

                // Modal methods
                openEditModal(tpl) {
                    this.editingTemplate = { ...tpl };
                    this.modalTab = 'html';
                    this.showEditContentModal = true;
                },

                closeEditModal() {
                    this.showEditContentModal = false;
                    this.editingTemplate = null;
                },

                saveEditModal() {
                    if (this.editingTemplate) {
                        const original = this.templates.find(t => t.id === this.editingTemplate.id && t.id !== null);
                        if (original) {
                            original.content = this.editingTemplate.content;
                            original.css = this.editingTemplate.css;
                            original._unsaved = true;
                        } else {
                            // Match unsaved new items by their reference index
                            const unsavedIdx = this.filteredTemplates.indexOf(this.templates.find(t => t.id === null && t.name === this.editingTemplate.name));
                            if (unsavedIdx !== -1) {
                                this.filteredTemplates[unsavedIdx].content = this.editingTemplate.content;
                                this.filteredTemplates[unsavedIdx].css = this.editingTemplate.css;
                                this.filteredTemplates[unsavedIdx]._unsaved = true;
                            }
                        }
                        showToast('success', 'Template layout changes applied. Don\'t forget to click Save All to persist.');
                    }
                    this.closeEditModal();
                },

                insertAtCursor(val) {
                    const activeTextarea = this.modalTab === 'html' 
                        ? document.getElementById('html-textarea') 
                        : document.getElementById('css-textarea');
                        
                    if (!activeTextarea) return;

                    const start = activeTextarea.selectionStart;
                    const end = activeTextarea.selectionEnd;
                    const text = activeTextarea.value;
                    const before = text.substring(0, start);
                    const after = text.substring(end, text.length);
                    
                    activeTextarea.value = before + val + after;
                    activeTextarea.selectionStart = activeTextarea.selectionEnd = start + val.length;
                    activeTextarea.focus();
                    
                    if (this.modalTab === 'html') {
                        this.editingTemplate.content = activeTextarea.value;
                    } else {
                        this.editingTemplate.css = activeTextarea.value;
                    }
                },

                exportExcel() {
                    const url = new URL('/api/hbl-templates/export', window.location.origin);
                    if (this.searchTerm) {
                        url.searchParams.append('search', this.searchTerm);
                    }
                    showToast('info', 'Preparing Excel/CSV export...');
                    fetch(url.toString())
                        .then(res => res.blob())
                        .then(blob => {
                            const dlUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = dlUrl;
                            a.download = `hbl-templates-${new Date().toISOString().split('T')[0]}.csv`;
                            a.click();
                            window.URL.revokeObjectURL(dlUrl);
                            showToast('success', 'Export downloaded successfully');
                        })
                        .catch(() => showToast('error', 'Failed to export templates'));
                },

                printTable() {
                    const url = new URL('/settings/hbl-templates-print', window.location.origin);
                    if (this.searchTerm) {
                        url.searchParams.append('search', this.searchTerm);
                    }
                    window.open(url.toString(), '_blank');
                }
            }
        }
    </script>
    @endpush
</x-layout>
