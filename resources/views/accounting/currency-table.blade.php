<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        [x-cloak] { display: none !important; }
        .row-unsaved { background-color: #fffbeb !important; }
        .active-filter { background-color: #3b82f6 !important; color: white !important; border-color: #3b82f6 !important; }
        .filter-input {
            width: 100%;
            height: 20px;
            font-size: 10px;
            padding: 2px 4px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background: #ffffff;
        }
        .filter-row th {
            background-color: #f1f5f9 !important;
            padding: 3px 4px !important;
        }
    </style>
    @endpush

    {{-- ═══════ TOAST CONTAINER ═══════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════ DELETE CONFIRM MODAL ═══════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Rate(s)?</h4>
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
    <div class="page-content">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Settings <i class="fa fa-angle-right"></i></li>
                <li>Accounting <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">Currency Table</span></li>
            </ul>
        </div>

        <div class="portlet light" x-data="currencyTable()">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:12px;">
                    <span class="caption-subject">CURRENCY TABLE</span>
                    <div style="font-size:10px;color:#64748b;font-weight:400;">
                        <span style="font-weight:600;color:#475569;">Current Rate:</span> 
                        <span style="color:#3b82f6;font-weight:700;" x-text="latestRate"></span>
                        <span style="margin:0 12px;">|</span>
                        <span style="font-weight:600;color:#475569;">Last Accounting Block Date:</span> 
                        <span style="color:#16a34a;font-weight:700;" x-text="lastBlockDate"></span>
                    </div>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round white" :class="{'active-filter': showFilters}" @click="toggleFilters()" title="Toggle Column Filters">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button class="btn-action-round white" @click="printTable()" title="Print this page">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button class="btn-action-round white" @click="exportExcel()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── CURRENCY CONVERSION SELECTOR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div style="display:flex;align-items:center;gap:6px;font-size:10px;font-weight:600;color:#475569;">
                        <span>From:</span>
                        <select class="select-tool" x-model="fromCurrency" style="width:80px;">
                            <option value="CAD">CAD</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="JPY">JPY</option>
                            <option value="AUD">AUD</option>
                            <option value="CNY">CNY</option>
                            <option value="PKR">PKR</option>
                            <option value="INR">INR</option>
                        </select>
                        <i class="fa fa-arrow-right" style="color:#94a3b8;font-size:12px;"></i>
                        <span>To:</span>
                        <select class="select-tool" x-model="toCurrency" style="width:80px;background:#f8fafc;font-weight:600;" disabled title="Exchange-rate entry supports USD only">
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool green" @click="addRate()" title="Add New Rate">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button class="btn-tool" :disabled="selectedRates.length === 0" @click="confirmDelete()" title="Delete Selected">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button class="btn-tool" @click="refreshRates()" title="Refresh Data">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin:0;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" x-model="searchTerm" @input="filterRates()" class="input-inline" style="width:160px;"
                           placeholder="Quick search...">
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table">
                            <thead>
                                <tr>
                                    <th class="sticky-col sticky-col-header" style="width:30px;text-align:center;">
                                        <input type="checkbox" @change="toggleSelectAll($event.target.checked)" :checked="allSelected">
                                    </th>
                                    <th class="sticky-col sticky-col-header" style="width:25px;left:30px;text-align:center;"><i class="fa fa-trash"></i></th>
                                    <th class="sticky-col sticky-col-header" style="width:60px;left:55px;">From</th>
                                    <th class="sticky-col sticky-col-header" style="width:60px;left:115px;">To</th>
                                    <th style="width:100px;">As of</th>
                                    <th style="width:100px;text-align:right;">Rate (Internal)</th>
                                    <th style="width:100px;text-align:right;">Rate (External)</th>
                                    <th style="width:140px;">Created By</th>
                                    <th style="width:100px;">Created Date</th>
                                    <th style="width:140px;">Modified By</th>
                                    <th style="width:100px;">Last Modified</th>
                                    <th style="width:140px;">Generated By</th>
                                    <th style="width:200px;">Remark</th>
                                </tr>
                                {{-- COLUMN FILTER ROW --}}
                                <tr x-show="showFilters" class="filter-row" x-cloak>
                                    <th colspan="2" class="sticky-col" style="left:0;"></th>
                                    <th class="sticky-col" style="left:55px;">
                                        <input type="text" x-model="filters.from_currency" @input="filterRates()" class="filter-input" placeholder="Filter...">
                                    </th>
                                    <th class="sticky-col" style="left:115px;">
                                        <input type="text" x-model="filters.to_currency" @input="filterRates()" class="filter-input" placeholder="Filter...">
                                    </th>
                                    <th>
                                        <input type="text" x-model="filters.as_of_date" @input="filterRates()" class="filter-input" placeholder="Date...">
                                    </th>
                                    <th>
                                        <input type="text" x-model="filters.rate_internal" @input="filterRates()" class="filter-input" placeholder="Rate...">
                                    </th>
                                    <th>
                                        <input type="text" x-model="filters.rate_external" @input="filterRates()" class="filter-input" placeholder="Rate...">
                                    </th>
                                    <th>
                                        <input type="text" x-model="filters.created_by" @input="filterRates()" class="filter-input" placeholder="User...">
                                    </th>
                                    <th></th>
                                    <th>
                                        <input type="text" x-model="filters.modified_by" @input="filterRates()" class="filter-input" placeholder="User...">
                                    </th>
                                    <th></th>
                                    <th>
                                        <input type="text" x-model="filters.generated_by" @input="filterRates()" class="filter-input" placeholder="Gen by...">
                                    </th>
                                    <th>
                                        <input type="text" x-model="filters.remark" @input="filterRates()" class="filter-input" placeholder="Remark...">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(rate, index) in filteredRates" :key="rate.id || index">
                                    <tr @click="toggleRowSelect(rate)" :class="{'row-selected': rate.selected, 'row-unsaved': rate._unsaved}">
                                        <td class="sticky-col" style="text-align:center;" @click.stop>
                                            <input type="checkbox" :checked="rate.selected" @change="rate.selected = !rate.selected; updateToolbar()">
                                        </td>
                                        <td class="sticky-col" style="left:30px;text-align:center;" @click.stop>
                                            <i class="fa fa-trash" style="color:#ef4444;cursor:pointer;font-size:10px;" 
                                               @click="deleteSingleRate(rate)" title="Delete"></i>
                                        </td>
                                        <td class="sticky-col" style="left:55px;" @click.stop>
                                            <input type="text" x-model="rate.from_currency" 
                                                   class="input-inline" style="width:100%;font-weight:600;text-transform:uppercase;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td class="sticky-col" style="left:115px;" @click.stop>
                                            <input type="text" x-model="rate.to_currency" 
                                                   class="input-inline" style="width:100%;font-weight:600;text-transform:uppercase;background:#f8fafc;color:#1e293b;"
                                                   readonly title="Exchange rate entry supports USD only">
                                        </td>
                                        <td @click.stop>
                                            <input type="date" x-model="rate.as_of_date" 
                                                   class="input-inline" style="width:100%;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td @click.stop>
                                            <input type="number" step="0.000001" x-model="rate.rate_internal" 
                                                   class="input-inline" style="width:100%;text-align:right;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td @click.stop>
                                            <input type="number" step="0.000001" x-model="rate.rate_external" 
                                                   class="input-inline" style="width:100%;text-align:right;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td @click.stop>
                                            <input type="text" x-model="rate.created_by" 
                                                   class="input-inline" style="width:100%;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td x-text="rate.created_at"></td>
                                        <td @click.stop>
                                            <input type="text" x-model="rate.modified_by" 
                                                   class="input-inline" style="width:100%;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td x-text="rate.updated_at"></td>
                                        <td @click.stop>
                                            <input type="text" x-model="rate.generated_by" 
                                                   class="input-inline" style="width:100%;"
                                                   @input="rate._unsaved = true">
                                        </td>
                                        <td @click.stop>
                                            <input type="text" x-model="rate.remark" 
                                                   class="input-inline" style="width:100%;"
                                                   placeholder="Remark..."
                                                   @input="rate._unsaved = true">
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredRates.length === 0">
                                    <tr>
                                        <td colspan="13" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No currency rates found matching criteria.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── BOTTOM TOOLBAR ── --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;gap:8px;">
                    <button class="btn-tool green" @click="saveAll()" 
                            :disabled="!hasUnsavedChanges()"
                            title="Save all unsaved changes">
                        <i class="fa fa-save"></i> Save All
                    </button>
                    <button class="btn-tool" @click="cancelChanges()" 
                            :disabled="!hasUnsavedChanges()"
                            title="Cancel all changes">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:10px;color:#64748b;">
                    <span>Total Records: <span style="font-weight:600;color:#334155;" x-text="filteredRates.length"></span></span>
                    <template x-if="selectedRates.length > 0">
                        <span style="color:#3b82f6;font-weight:600;">
                            | <span x-text="selectedRates.length"></span> selected
                        </span>
                    </template>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    function currencyTable() {
        return {
            rates: @json($rates ?? []),
            filteredRates: [],
            selectedRates: [],
            fromCurrency: 'CAD',
            toCurrency: 'USD',
            searchTerm: '',
            allSelected: false,
            showFilters: false,
            latestRate: @json($latestRate ?? '1.0000'),
            lastBlockDate: @json($lastBlockDate ?? 'N/A'),
            filters: {
                from_currency: '',
                to_currency: '',
                as_of_date: '',
                rate_internal: '',
                rate_external: '',
                created_by: '',
                modified_by: '',
                generated_by: '',
                remark: ''
            },

            init() {
                this.filteredRates = [...this.rates];
                this.filterRates();
            },

            async loadRates() {
                try {
                    const url = new URL('/api/currency-rates', window.location.origin);
                    if (this.searchTerm) url.searchParams.set('search', this.searchTerm);

                    const response = await fetch(url.toString());
                    if (response.ok) {
                        const data = await response.json();
                        this.rates = data.rates || [];
                        if (data.latest_rate) this.latestRate = data.latest_rate;
                        if (data.last_block_date) this.lastBlockDate = data.last_block_date;
                        this.filterRates();
                    }
                } catch (error) {
                    console.error('Failed to load rates:', error);
                }
            },

            toggleFilters() {
                this.showFilters = !this.showFilters;
            },

            filterRates() {
                let filtered = [...this.rates];

                // Quick Search Term
                if (this.searchTerm) {
                    const search = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(rate => {
                        return rate.from_currency?.toLowerCase().includes(search) ||
                               rate.to_currency?.toLowerCase().includes(search) ||
                               rate.remark?.toLowerCase().includes(search) ||
                               rate.created_by?.toLowerCase().includes(search) ||
                               rate.modified_by?.toLowerCase().includes(search) ||
                               rate.generated_by?.toLowerCase().includes(search);
                    });
                }

                // Column Level Filters
                if (this.filters.from_currency) {
                    const search = this.filters.from_currency.toLowerCase();
                    filtered = filtered.filter(r => r.from_currency?.toLowerCase().includes(search));
                }
                if (this.filters.to_currency) {
                    const search = this.filters.to_currency.toLowerCase();
                    filtered = filtered.filter(r => r.to_currency?.toLowerCase().includes(search));
                }
                if (this.filters.as_of_date) {
                    const search = this.filters.as_of_date.toLowerCase();
                    filtered = filtered.filter(r => r.as_of_date?.toLowerCase().includes(search));
                }
                if (this.filters.rate_internal) {
                    filtered = filtered.filter(r => String(r.rate_internal).includes(this.filters.rate_internal));
                }
                if (this.filters.rate_external) {
                    filtered = filtered.filter(r => String(r.rate_external).includes(this.filters.rate_external));
                }
                if (this.filters.created_by) {
                    const search = this.filters.created_by.toLowerCase();
                    filtered = filtered.filter(r => r.created_by?.toLowerCase().includes(search));
                }
                if (this.filters.modified_by) {
                    const search = this.filters.modified_by.toLowerCase();
                    filtered = filtered.filter(r => r.modified_by?.toLowerCase().includes(search));
                }
                if (this.filters.generated_by) {
                    const search = this.filters.generated_by.toLowerCase();
                    filtered = filtered.filter(r => r.generated_by?.toLowerCase().includes(search));
                }
                if (this.filters.remark) {
                    const search = this.filters.remark.toLowerCase();
                    filtered = filtered.filter(r => r.remark?.toLowerCase().includes(search));
                }

                this.filteredRates = filtered;
                this.updateToolbar();
            },

            addRate() {
                const newRate = {
                    id: null,
                    from_currency: this.fromCurrency || 'CAD',
                    to_currency: 'USD',
                    as_of_date: new Date().toISOString().split('T')[0],
                    rate_internal: 1.000000,
                    rate_external: 1.000000,
                    created_by: 'System Admin',
                    created_at: new Date().toLocaleDateString(),
                    modified_by: '',
                    updated_at: '',
                    generated_by: 'Accounting',
                    remark: '',
                    selected: false,
                    _unsaved: true
                };
                this.rates.unshift(newRate);
                this.filterRates();
                showToast('info', 'New rate added. Click Save All to persist changes.');
            },

            toggleRowSelect(rate) {
                rate.selected = !rate.selected;
                this.updateToolbar();
            },

            toggleSelectAll(checked) {
                this.filteredRates.forEach(rate => rate.selected = checked);
                this.updateToolbar();
            },

            updateToolbar() {
                this.selectedRates = this.filteredRates.filter(r => r.selected);
                this.allSelected = this.filteredRates.length > 0 && 
                                   this.selectedRates.length === this.filteredRates.length;
            },

            confirmDelete() {
                const n = this.selectedRates.length;
                if (!n) return;
                document.getElementById('confirm-msg').textContent =
                    `You are about to permanently delete ${n} currency rate(s). This cannot be undone.`;
                document.getElementById('confirm-overlay').classList.add('open');
            },

            async executeDelete() {
                closeConfirm();
                const ids = this.selectedRates.map(r => r.id).filter(id => id !== null);
                
                if (ids.length === 0) {
                    this.rates = this.rates.filter(r => !r.selected || r.id !== null);
                    this.filterRates();
                    showToast('success', 'Unsaved rates removed');
                    return;
                }

                showToast('info', 'Deleting...');
                
                try {
                    const response = await fetch('/api/currency-rates/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ ids })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.rates = this.rates.filter(r => !ids.includes(r.id));
                        this.filterRates();
                        showToast('success', data.message || 'Deleted successfully');
                    } else {
                        showToast('error', data.message || 'Failed to delete');
                    }
                } catch (error) {
                    showToast('error', 'Failed to delete rates');
                }
            },

            async deleteSingleRate(rate) {
                if (!confirm('Delete this rate?')) return;

                if (!rate.id) {
                    this.rates = this.rates.filter(r => r !== rate);
                    this.filterRates();
                    showToast('success', 'Rate removed');
                    return;
                }

                showToast('info', 'Deleting...');

                try {
                    const response = await fetch(`/api/currency-rates/${rate.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.rates = this.rates.filter(r => r.id !== rate.id);
                        this.filterRates();
                        showToast('success', 'Rate deleted');
                    } else {
                        showToast('error', data.message || 'Failed to delete');
                    }
                } catch (error) {
                    showToast('error', 'Failed to delete rate');
                }
            },

            hasUnsavedChanges() {
                return this.rates.some(r => r._unsaved);
            },

            async saveAll() {
                const unsaved = this.rates.filter(r => r._unsaved);
                if (unsaved.length === 0) return;

                showToast('info', `Saving ${unsaved.length} rate(s)...`);

                try {
                    const response = await fetch('/api/currency-rates/bulk-save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ rates: unsaved })
                    });

                    const data = await response.json();
                    if (data.success) {
                        if (data.rates) {
                            data.rates.forEach((savedRate, index) => {
                                const unsavedRate = unsaved[index];
                                Object.assign(unsavedRate, savedRate);
                                unsavedRate._unsaved = false;
                            });
                        } else {
                            unsaved.forEach(r => r._unsaved = false);
                        }
                        showToast('success', data.message || 'All changes saved successfully');
                        await this.loadRates();
                    } else {
                        showToast('error', data.message || 'Failed to save');
                    }
                } catch (error) {
                    showToast('error', 'Failed to save rates');
                }
            },

            cancelChanges() {
                if (!confirm('Discard all unsaved changes?')) return;
                this.loadRates();
                showToast('info', 'Changes cancelled');
            },

            refreshRates() {
                showToast('info', 'Refreshing currency rates...');
                this.loadRates();
            },

            printTable() {
                const url = new URL('{{ route("accounting.currency-table-print") }}');
                
                if (this.searchTerm) url.searchParams.set('search', this.searchTerm);
                
                showToast('info', 'Opening print view...');
                window.open(url.toString(), '_blank');
            },

            async exportExcel() {
                showToast('info', 'Preparing Excel export...');
                
                try {
                    const url = new URL('/api/currency-rates/export', window.location.origin);
                    if (this.searchTerm) url.searchParams.set('search', this.searchTerm);

                    const response = await fetch(url.toString());
                    if (!response.ok) throw new Error('Export failed');
                    
                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'currency-rates-' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(downloadUrl);
                    showToast('success', 'Excel file downloaded successfully');
                } catch (error) {
                    console.error('Export error:', error);
                    showToast('error', 'Failed to export Excel');
                }
            }
        }
    }

    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }

    window.executeDelete = function() {
        const component = Alpine.$data(document.querySelector('[x-data="currencyTable()"]'));
        if (component) component.executeDelete();
    };

    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> <span>${msg}</span>`;
        const container = document.getElementById('toast-container');
        if (container) {
            container.appendChild(t);
            setTimeout(() => {
                if (t.parentElement) {
                    t.style.opacity = '0';
                    t.style.transform = 'translateX(100%)';
                    setTimeout(() => t.remove(), 300);
                }
            }, 3000);
        }
    }

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
