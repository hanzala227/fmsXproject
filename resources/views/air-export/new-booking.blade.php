<x-layout>
<x-form-styles />
<div class="page-content" style="background: #eef1f5; min-height: 100vh; padding: 15px;">
    <script>
        window.bookingModule = function() {
            return {
                showQuoteModal: window.location.search.includes('load_from_quotation=true'),
                step: 1,
                commodities: [],
                form: {
                    pkg_qty: '{{ old('pkg_qty', isset($booking) ? $booking->pkg_qty : '') }}',
                    gross_weight: '{{ old('gross_weight', isset($booking) ? $booking->gross_weight : '') }}',
                    volume: '{{ old('volume', isset($booking) ? $booking->volume : '0') }}',
                    chargeable_weight: '{{ old('chargeable_weight', isset($booking) ? $booking->chargeable_weight : '0') }}'
                },
                quotations: @json($quotations ?? []),
                selectedQuote: null,
                quoteForm: {},
                quoteSearch: { quote_no: '', customer_id: '', valid_date: '' },
                matchQuote(q) {
                    if (this.quoteSearch.quote_no && !q.quote_no.toLowerCase().includes(this.quoteSearch.quote_no.toLowerCase())) return false;
                    if (this.quoteSearch.customer_id && q.customer_id != this.quoteSearch.customer_id) return false;
                    return true;
                },
                selectQuote(q) {
                    this.selectedQuote = q;
                    this.quoteForm = Object.assign({}, q);
                    // Pre-fill Step 2 form using quote data
                    this.form.gross_weight = q.gross_weight_kg || '0';
                    this.form.volume = q.volume || '0';
                    this.form.chargeable_weight = q.chargeable_weight_kg || '0';
                    this.step = 2;
                },
                confirmQuote() {
                    // Finalize conversion
                    if (this.selectedQuote) {
                        const q = this.selectedQuote;
                        document.querySelector('[name="customer_id"]').value = q.customer_id || '';
                        document.querySelector('[name="sales_person_id"]').value = q.sales_person_id || '';
                        document.querySelector('[name="dep_port_id"]').value = q.pol_id || '';
                        document.querySelector('[name="dst_port_id"]').value = q.pod_id || '';
                    }
                    this.showQuoteModal = false;
                },
                addCommodity() {
                    this.commodities.push({ description: '', hts_code: '', po_number: '', selected: false });
                },
                removeSelectedCommodities() {
                    this.commodities = this.commodities.filter(c => !c.selected);
                },
                removeCommodity(idx) {
                    if (confirm('Remove this commodity?')) this.commodities.splice(idx, 1);
                },
                calculateVolumeWeight() {
                    let pkgQty = parseFloat(this.form.pkg_qty) || 0;
                    let grossWt = parseFloat(this.form.gross_weight) || 0;
                    let vol = parseFloat(this.form.volume) || 0;
                    let calculatedChargeable = Math.max(grossWt, vol * 167);
                    this.form.chargeable_weight = calculatedChargeable.toFixed(2);
                    this.form.volume = vol.toFixed(4);
                },
                closeModal() {
                    if(window.location.search.includes('load_from_quotation=true')) {
                        window.location.href = '/air-export/booking/entry';
                    } else {
                        this.showQuoteModal = false;
                    }
                },
                formErrors: {},
                validateForm(el) {
                    this.formErrors = {};
                    if (!this.form.pkg_qty || this.form.pkg_qty.trim() === '') {
                        this.formErrors.pkg_qty = 'Package qty is required';
                    }
                    if (!this.form.gross_weight || this.form.gross_weight.trim() === '') {
                        this.formErrors.gross_weight = 'Gross weight is required';
                    }
                    if (Object.keys(this.formErrors).length > 0) {
                        return;
                    }
                    el.submit();
                },
                init() {
                    // Load commodities from session if returning from validation error
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-blue: #4b77be;
            --accent-yellow: #f2bc00;
            --text-main: #333;
            --text-muted: #8e9eae;
            --border-color: #ddd;
            --well-bg: #f9fafb;
        }

        [x-cloak] { display: none !important; }
        .booking-container {
            max-width: 100%;
            margin: 0;
            font-family: 'Open Sans', sans-serif !important;
        }

        /* Modal Premium Styling */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-card {
            background: white;
            width: 1000px;
            max-height: 90vh;
            border-radius: 4px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .modal-header-premium { padding: 10px 15px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: #f5f5f5; }
        .modal-title-premium { font-size: 12px; font-weight: 700; color: #333; text-transform: uppercase; }
        
        /* Stepper Styling */
        .stepper-container { padding: 15px 30px; display: flex; align-items: center; justify-content: center; background: #fff; border-bottom: 1px solid var(--border-color); }
        .step-item { display: flex; flex-direction: column; align-items: center; gap: 4px; width: 180px; position: relative; }
        .step-circle { width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px; transition: all 0.2s; }
        .step-item.active .step-circle { background: #32c5d2; color: white; box-shadow: none; }
        .step-item.completed .step-circle { background: var(--primary-blue); color: white; }
        .step-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: center; }
        .step-line { flex: 0.5; height: 2px; background: #e2e8f0; margin-bottom: 15px; }

        /* Search & Grid Area */
        .modal-content-area { padding: 15px; overflow-y: auto; flex: 1; }
        .search-grid-lite { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; }
        
        .premium-mini-table { width: 100%; border-collapse: collapse; border: 1px solid var(--border-color); border-radius: 0; }
        .premium-mini-table th { background: #f9f9f9; padding: 6px 10px; font-size: 10px; font-weight: 600; text-align: left; border: 1px solid #ddd; }
        .premium-mini-table td { padding: 6px 10px; font-size: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; }

        .modal-footer-premium { padding: 10px 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px; background: #f5f5f5; }
        
        /* Consistency with main layout styles... */
        .breadcrumb-card {
            background: transparent;
            padding: 0;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .breadcrumb-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            font-size: 11px;
            color: #8e9eae;
        }
        .breadcrumb-list li i { margin: 0 5px; font-size: 10px; }
        .breadcrumb-list a { color: #8e9eae; font-weight: normal; text-decoration: none; }

        /* Tabs Styling */
        .nav-tabs-custom {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
            border-bottom: 1px solid #ddd;
            background: #fff;
            border-radius: 4px 4px 0 0;
            overflow-x: auto;
            white-space: nowrap;
        }
        .nav-tab-item {
            padding: 10px 20px;
            display: block;
            color: #555;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: -1px;
            border-radius: 0;
            transition: none;
            background: transparent;
        }
        .nav-tab-item.active {
            background: #fff;
            border: 1px solid #ddd;
            border-bottom-color: #fff;
            border-top: 3px solid #32c5d2;
            color: #333;
            box-shadow: none;
        }

        /* Main Board Card */
        .main-board {
            background: #fff;
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .board-header {
            background: #f9fafb;
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 35px;
        }
        .board-header h2 { margin: 0; font-size: 13px; font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: normal; }
        .op-badge { background: transparent; padding: 0; border-radius: 0; font-size: 10px; font-weight: 600; color: #666; }

        /* Form Grid Styling */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px 20px;
            padding: 15px;
            background: #fff;
        }
        .form-column {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .section-well {
            background: #f9fafb;
            border: 1px solid #ebedf2;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: none;
        }

        .form-group-custom {
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 19px;
        }
        .label-custom {
            display: inline-block;
            width: 110px;
            font-size: 10px;
            font-weight: 500;
            color: #333;
            margin-bottom: 0;
            text-align: right;
            margin-right: 6px;
            white-space: nowrap;
            flex-shrink: 0;
            text-transform: none;
            line-height: 1;
        }
        .input-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 0;
            background: #fff;
            box-sizing: border-box;
            height: 18px;
        }
        .input-wrapper:focus-within { border-color: #32c5d2; box-shadow: none; }
        
        .input-custom, select.input-custom {
            width: 100%;
            height: 100%;
            border: none;
            padding: 0 4px;
            font-size: 10px;
            outline: none;
            background: #fff;
            color: #333;
            font-family: inherit;
            box-sizing: border-box;
        }
        select.input-custom {
            padding: 0 2px;
            color: #333;
        }
        .input-addon {
            background: #eee;
            padding: 0 5px;
            display: flex;
            align-items: center;
            border-left: 1px solid #ccc;
            color: #666;
            font-size: 10px;
            height: 100%;
            box-sizing: border-box;
        }
        .input-addon-left { border-left: 0; border-right: 1px solid #ccc; }

        .btn-edit {
            background: #fff;
            border: 1px solid #ccc;
            color: #666;
            padding: 0 4px;
            height: 18px;
            cursor: pointer;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-edit:hover { background: #eee; opacity: 1; }

        /* Section Dividers */
        .form-section-title {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 15px 0 5px;
            color: #4b77be;
            font-size: 11px;
            font-weight: 600;
        }
        .form-section-title::after { content: ''; flex: 1; height: 1px; background: #eee; }

        /* Table Styling */
        .data-table-container { padding: 0 15px; margin-bottom: 15px; }
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            border-radius: 0;
        }
        .modern-table th {
            background: #f9f9f9;
            padding: 4px;
            font-size: 10px;
            font-weight: 600;
            color: #333;
            border: 1px solid #ddd;
            text-transform: none;
        }
        .modern-table td {
            padding: 4px;
            background: white;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .empty-row { text-align: center; color: #999; padding: 10px !important; }

        /* Floating Footer replaced with standard FreightX layout */
        .footer-save {
            position: relative;
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
            padding: 15px;
            background: #fff;
            border-top: 1px solid #eee;
            bottom: auto;
            right: auto;
            z-index: auto;
        }
        .btn-save {
            background: #32c5d2;
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 0;
            font-weight: 600;
            box-shadow: none;
            cursor: pointer;
            font-size: 11px;
            transition: none;
        }
        .btn-save:hover { background: #26a69a; transform: none; box-shadow: none; }

        /* Radio Styling */
        .radio-group { display: flex; gap: 15px; padding-top: 2px; }
        .radio-item { display: flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; cursor: pointer; }

        .btn-action-group { display: flex; gap: 5px; }
        .btn-action { width: 20px; height: 18px; border-radius: 0; border: 1px solid #ccc; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; font-size: 10px; }
        .btn-action.green { background: #32c5d2; color: white; border: none; }

        textarea.custom-text {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 0;
            padding: 4px 6px;
            font-size: 10px;
            outline: none;
            resize: vertical;
            transition: none;
            font-family: inherit;
        }
        textarea.custom-text:focus { border-color: #32c5d2; box-shadow: none; }
    </style>


    <div class="booking-container" x-cloak x-data="bookingModule()" x-init="init()">
        <!-- Stepper Modal -->
       <template x-teleport="body">
    <!-- QUOTE MODAL (Restyled to match the reference "perfect" form exactly) -->
    <div x-show="showQuoteModal" class="modal-overlay" style="display:none; z-index: 999999;" x-cloak>
        <div class="modal-container" style="max-width: 950px; display: flex; flex-direction: column;" @click.away="closeModal()">

            <!-- Header -->
            <div class="modal-header">
                <span><i class="fa fa-file-text-o text-blue-500"></i> Load Quotation Data</span>
                <i class="fa fa-times cursor-pointer text-gray-500 hover:text-gray-700" @click="closeModal()"></i>
            </div>

            <div class="modal-body hide-scrollbar">
                <style>
                    .hide-scrollbar::-webkit-scrollbar { display: none; }
                    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                    .wizard-circle { width: 18px; height: 18px; min-width: 18px; min-height: 18px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 10px; font-weight: bold; }
                </style>

                <!-- Wizard Steps Header -->
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <div class="wizard-circle" :style="step >= 1 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                            <template x-if="step > 1"><i class="fa fa-check"></i></template>
                            <template x-if="step === 1"><span>1</span></template>
                        </div>
                        <span :style="step >= 1 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Select Quotation</span>
                    </div>
                    <div style="height: 1px; width: 20px; background: #e2e8f0;"></div>

                    <div style="display: flex; align-items: center; gap: 5px;">
                        <div class="wizard-circle" :style="step >= 2 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                            <template x-if="step > 2"><i class="fa fa-check"></i></template>
                            <template x-if="step <= 2"><span>2</span></template>
                        </div>
                        <span :style="step >= 2 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Fill Shipment Data</span>
                    </div>
                    <div style="height: 1px; width: 20px; background: #e2e8f0;"></div>

                    <div style="display: flex; align-items: center; gap: 5px;">
                        <div class="wizard-circle" :style="step >= 3 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                            <span>3</span>
                        </div>
                        <span :style="step >= 3 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Select Invoice Items</span>
                    </div>
                </div>

                <!-- Step 1 Content -->
               <div x-show="step === 1">
                            <div class="search-grid-lite">
                                <div class="form-group-custom">
                                    <label class="label-custom">Customer</label>
                                    <select class="input-custom" x-model="quoteSearch.customer_id" style="border: 1px solid #d1d9e6; border-radius: 4px; width:100%;">
                                        <option value="">Select Customer...</option>
                                        @foreach($tradePartners ?? [] as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label class="label-custom">Valid Date</label>
                                    <input type="date" class="input-custom" x-model="quoteSearch.valid_date" style="border: 1px solid #d1d9e6; border-radius: 4px; width:100%;">
                                </div>
                                <div class="form-group-custom">
                                    <label class="label-custom">Quote No.</label>
                                    <input type="text" class="input-custom" x-model="quoteSearch.quote_no" style="border: 1px solid #d1d9e6; border-radius: 4px; width:100%;" placeholder="Search Quote...">
                                </div>
                            </div>
                            <div style="text-align: center; margin-bottom: 25px;">
                                <button type="button" class="btn-premium primary" style="padding: 8px 30px;">Search Quotations....</button>
                            </div>
                            <table class="premium-mini-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Select</th>
                                        <th>Quote No.</th>
                                        <th>Valid Date</th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="q in quotations" :key="q.id">
                                        <tr x-show="matchQuote(q)">
                                            <td style="text-align: center;"><input type="radio" name="quote_select_radio" @click="selectQuote(q)"></td>
                                            <td x-text="q.quote_no"></td>
                                            <td x-text="q.valid_date || '-'"></td>
                                            <td x-text="q.customer ? q.customer.name : '-'"></td>
                                            <td x-text="q.sales_person ? q.sales_person.name : '-'"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="quotations.filter(q => matchQuote(q)).length === 0">
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #999;">No quotation found matching filters.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                <!-- Step 2 Content -->
               <div x-show="step === 2">  
                                          <h4 class="form-section-title">Verify Booking Information</h4>                            <div class="form-grid" style="padding: 0; gap: 15px;">                                <div class="form-group-custom">                                    <label class="label-custom">Quote No.</label>                                    <input type="text" class="input-custom" :value="quoteForm.quote_no" disabled style="background:#f5f5f5; border:1px solid #ddd; width:100%;">                                </div>                                <div class="form-group-custom">                                    <label class="label-custom">Quote Date</label>                                    <input type="date" class="input-custom" style="border:1px solid #ddd; width:100%;" :value="quoteForm.quote_date">                                </div>                                <div class="form-group-custom">                                    <label class="label-custom">Valid Date</label>                                    <input type="date" class="input-custom" style="border:1px solid #ddd; width:100%;" :value="quoteForm.valid_date">                                </div>                            </div>                        </div>
                <!-- Step 3 Content -->
                <div x-show="step === 3" x-cloak>
                    <div class="hbl-header">Select Charge Items</div>
                    <p style="font-size: 10px; color: #64748b; margin-bottom: 10px;">Select which items from the quotation should be converted to invoice items.</p>

                    <div class="table-responsive" style="margin-bottom: 10px;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 40px;"><input type="checkbox" checked></th>
                                    <th>Freight Code</th>
                                    <th>Description</th>
                                    <th style="text-align: right;">Rate</th>
                                    <th style="text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in (selectedQuote ? selectedQuote.items : [])" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td style="text-align: center;"><input type="checkbox" checked></td>
                                        <td x-text="item.freight_code"></td>
                                        <td x-text="item.description"></td>
                                        <td style="text-align: right;" x-text="item.rate"></td>
                                        <td style="text-align: right; font-weight: 600; color: #3b82f6;" x-text="item.amount"></td>
                                    </tr>
                                </template>
                                <tr x-show="!selectedQuote || !selectedQuote.items || selectedQuote.items.length === 0">
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 40px;">No charge items found in this quotation.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn-default-gf" @click="closeModal()">Cancel</button>
                <button type="button" x-show="step > 1" class="btn-default-gf" @click="step--">Previous</button>
                <button type="button" class="btn-freightx" @click="step < 3 ? step++ : confirmQuote()">
                    <span x-text="step === 3 ? 'Convert to Booking' : 'Next Step'"></span>
                    <i class="fa fa-arrow-right" x-show="step < 3"></i>
                    <i class="fa fa-check" x-show="step === 3"></i>
                </button>
            </div>
        </div>
    </div>
</template>
        <!-- Breadcrumb & Toolbar -->
        <div style="font-size: 11px; margin-bottom: 10px;">
            <a href="/air-export/list" style="color: #8e9eae; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#8e9eae';">Air Export</a> <i class="fa fa-angle-right" style="margin: 0 5px;"></i> 
            <span style="color: #333; font-weight: 700;">{{ isset($booking) ? 'Edit Booking' : 'New Booking' }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($booking) ? 'Edit Air Export Booking' : 'Create Air Export Booking' }}</h1>
            <div style="display: flex; gap: 8px;">
                <button type="submit" form="airBookingForm" class="btn-freightx"><i class="fa fa-save"></i> SAVE BOOKING</button>
                <a href="/air-export/list" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-custom">
            <a href="/air-export/booking/entry" class="nav-tab-item active">BASIC INFO</a>
            @if(isset($booking))
                <a href="{{ route('air-bookings.accounting', $booking->id) }}" class="nav-tab-item">ACCOUNTING</a>
                <a href="{{ route('air-bookings.status', $booking->id) }}" class="nav-tab-item">STATUS / TRACKING</a>
            @else
                <span class="nav-tab-item" style="opacity: 0.5; cursor: not-allowed;" title="Save the booking first">ACCOUNTING</span>
                <span class="nav-tab-item" style="opacity: 0.5; cursor: not-allowed;" title="Save the booking first">STATUS / TRACKING</span>
            @endif
        </div>

        <!-- Main Form Board -->
        <div class="main-board">
            <div class="board-header">
                <h2>Booking Entry Details</h2>
                <div class="op-badge"><i class="fa fa-user-circle mr-2"></i> OPERATOR: {{ strtoupper($currentUser->name ?? auth()->user()->name ?? 'N/A') }}</div>
            </div>

            <form action="{{ isset($booking) ? route('air-bookings.update', $booking->id) : route('air-bookings.store') }}" method="POST" id="airBookingForm" @submit.prevent="validateForm($el)">
                @csrf
                @if(isset($booking))
                    @method('PUT')
                @endif
                <input type="hidden" name="status" value="{{ isset($booking) ? $booking->status : 'OPEN' }}">
                <div class="form-grid-4" style="padding: 15px; padding-bottom: 0;">
                <!-- Column 1 -->
                <div class="flex flex-col">
                    <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*Booking No.</label><div class="form-input-container"><input type="text" name="booking_no" class="form-control-gf" value="{{ old('booking_no', isset($booking) ? $booking->booking_no : $nextBookingNo) }}" style="font-weight: 700;" required></div></div>
                    <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*Booking Date</label><div class="form-input-container"><input type="date" name="booking_date" class="form-control-gf" value="{{ old('booking_date', isset($booking) && $booking->booking_date ? $booking->booking_date->format('Y-m-d') : date('Y-m-d')) }}" required></div></div>
                    <div class="form-group-gf"><label class="form-label-gf">MAWB Reference</label><div class="form-input-container">
                        <select name="mawb_reference" class="form-control-gf">
                            <option value="">Select existing MAWB...</option>
                            @foreach($airExports ?? [] as $ae)
                                <option value="{{ $ae->id }}" {{ old('mawb_reference', isset($booking) ? $booking->mawb_reference : '') == $ae->id ? 'selected' : '' }}>{{ $ae->mawb_no }} ({{ $ae->file_no }})</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Sales Person</label><div class="form-input-container">
                        <select name="sales_person_id" class="form-control-gf"><option value="">Select Sales...</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('sales_person_id', isset($booking) ? $booking->sales_person_id : '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach</select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*Office Branch</label><div class="form-input-container">
                        <select name="office_id" class="form-control-gf" required><option value="">Select Office...</option>
                            @foreach($offices ?? [] as $office)
                                <option value="{{ $office->id }}" {{ old('office_id', isset($booking) ? $booking->office_id : '') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                            @endforeach</select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Operator</label><div class="form-input-container">
                        <select name="op_id" class="form-control-gf"><option value="">Select Operator...</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('op_id', isset($booking) ? $booking->op_id : '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach</select>
                    </div></div>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col">
                    <div class="form-group-gf"><label class="form-label-gf">Primary Carrier</label><div class="form-input-container">
                        <select name="carrier_id" class="form-control-gf">
                            <option value="">Select Carrier...</option>
                            @foreach($tradePartners ?? [] as $tp)
                                <option value="{{ $tp->id }}" {{ old('carrier_id', isset($booking) ? $booking->carrier_id : '') == $tp->id ? 'selected' : '' }}>{{ $tp->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container">
                        <select name="shipper_id" class="form-control-gf">
                            <option value="">Select Shipper...</option>
                            @foreach($tradePartners ?? [] as $tp)
                                <option value="{{ $tp->id }}" {{ old('shipper_id', isset($booking) ? $booking->shipper_id : '') == $tp->id ? 'selected' : '' }}>{{ $tp->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container">
                        <select name="customer_id" class="form-control-gf">
                            <option value="">Select Customer...</option>
                            @foreach($tradePartners ?? [] as $tp)
                                <option value="{{ $tp->id }}" {{ old('customer_id', isset($booking) ? $booking->customer_id : '') == $tp->id ? 'selected' : '' }}>{{ $tp->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container">
                        <select name="oversea_agent_id" class="form-control-gf">
                            <option value="">Select Agent...</option>
                            @foreach($tradePartners ?? [] as $tp)
                                <option value="{{ $tp->id }}" {{ old('oversea_agent_id', isset($booking) ? $booking->oversea_agent_id : '') == $tp->id ? 'selected' : '' }}>{{ $tp->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container">
                        <select name="incoterms_id" class="form-control-gf"><option value="">Select Incoterm...</option>
                            @foreach($incoterms ?? [] as $incoterm)
                                <option value="{{ $incoterm->id }}" {{ old('incoterms_id', isset($booking) ? $booking->incoterms_id : '') == $incoterm->id ? 'selected' : '' }}>{{ $incoterm->name }}</option>
                            @endforeach</select>
                    </div></div>
                </div>

                <!-- Column 3 -->
                <div class="flex flex-col">
                    <div class="form-group-gf"><label class="form-label-gf">Port of Departure</label><div class="form-input-container">
                        <select name="dep_port_id" class="form-control-gf" data-ajax-search="true" data-type="AIR" data-search-url="{{ route('ports.search') }}">
                            <option value="">Select Airport...</option>
                            @foreach($ports ?? [] as $p)
                                <option value="{{ $p->id }}" {{ old('dep_port_id', isset($booking) ? $booking->dep_port_id : '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Departure Date</label><div class="form-input-container">
                        <input type="date" name="etd" class="form-control-gf" value="{{ old('etd', isset($booking) && $booking->etd ? $booking->etd->format('Y-m-d') : '') }}">
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Port of Destination</label><div class="form-input-container">
                        <select name="dst_port_id" class="form-control-gf" data-ajax-search="true" data-type="AIR" data-search-url="{{ route('ports.search') }}">
                            <option value="">Select Airport...</option>
                            @foreach($ports ?? [] as $p)
                                <option value="{{ $p->id }}" {{ old('dst_port_id', isset($booking) ? $booking->dst_port_id : '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Arrival Date</label><div class="form-input-container">
                        <input type="date" name="eta" class="form-control-gf" value="{{ old('eta', isset($booking) && $booking->eta ? $booking->eta->format('Y-m-d') : '') }}">
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Flight Number</label><div class="form-input-container">
                        <input type="text" name="flight_no" class="form-control-gf" value="{{ old('flight_no', isset($booking) ? $booking->flight_no : '') }}" placeholder="e.g. CX888">
                    </div></div>
                </div>

                <!-- Column 4 -->
                <div class="flex flex-col">
                    <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container">
                        <select name="cargo_type" class="form-control-gf">
                            @php $cargoTypes = ['GENERAL CARGO','DANGEROUS GOODS','PERISHABLE','VALUABLE CARGO']; @endphp
                            @foreach($cargoTypes as $ct)
                                <option value="{{ $ct }}" {{ old('cargo_type', isset($booking) ? $booking->cargo_type : 'GENERAL CARGO') == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Ship Type</label><div class="form-input-container">
                        <select name="ship_type" class="form-control-gf">
                            @php $shipTypes = ['NORMAL','CONSOL','EXPRESS']; @endphp
                            @foreach($shipTypes as $st)
                                <option value="{{ $st }}" {{ old('ship_type', isset($booking) ? $booking->ship_type : 'NORMAL') == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">WT/VAL</label><div class="form-input-container">
                        <select name="wt_val_payment" class="form-control-gf">
                            @php $wtVal = old('wt_val_payment', isset($booking) ? $booking->wt_val_payment : 'PPD'); @endphp
                            <option value="PPD" {{ $wtVal == 'PPD' ? 'selected' : '' }}>PPD</option>
                            <option value="COLL" {{ $wtVal == 'COLL' ? 'selected' : '' }}>COLL</option>
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Other Charges</label><div class="form-input-container">
                        <select name="other_charges_payment" class="form-control-gf">
                            @php $otherChg = old('other_charges_payment', isset($booking) ? $booking->other_charges_payment : 'PPD'); @endphp
                            <option value="PPD" {{ $otherChg == 'PPD' ? 'selected' : '' }}>PPD</option>
                            <option value="COLL" {{ $otherChg == 'COLL' ? 'selected' : '' }}>COLL</option>
                        </select>
                    </div></div>
                    <div class="form-group-gf"><label class="form-label-gf">Stackable Cargo</label><div class="form-input-container">
                        <select name="stackable" class="form-control-gf">
                            @php $stackable = old('stackable', isset($booking) ? $booking->stackable : '1'); @endphp
                            <option value="1" {{ $stackable == '1' ? 'selected' : '' }}>YES</option>
                            <option value="0" {{ $stackable == '0' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div></div>
                </div>
            </div>

            <div class="form-grid-4" style="padding: 15px; padding-top: 0; padding-bottom: 0;">
                <!-- Cargo Details Section -->
                <div class="form-section-title" style="margin-top: 5px;">CARGO & WEIGHT SPECIFICATIONS</div>
                <div class="flex flex-col" style="grid-column: 1 / span 2;">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Package Details</label>
                        <div class="form-input-container" style="display: flex; gap: 5px;">
                            <div style="flex: 0.3; display: flex; flex-direction: column;">
                                <input type="text" name="pkg_qty" class="form-control-gf" placeholder="Qty" x-model="form.pkg_qty">
                                <div x-show="formErrors.pkg_qty" x-text="formErrors.pkg_qty" style="color:#e7505a;font-size:9px;"></div>
                            </div>
                            <select name="pkg_unit_id" class="form-control-gf" style="flex: 0.7;">
                                <option value="">Select Unit...</option>
                                @foreach($packageUnits ?? [] as $unit)
                                    <option value="{{ $unit->id }}" {{ old('pkg_unit_id', isset($booking) ? $booking->pkg_unit_id : '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Gross Weight (KG)</label>
                        <div class="form-input-container">
                            <input type="text" name="gross_weight" class="form-control-gf" placeholder="0.00" x-model="form.gross_weight" @input="calculateVolumeWeight">
                            <div x-show="formErrors.gross_weight" x-text="formErrors.gross_weight" style="color:#e7505a;font-size:9px;margin-top:2px;"></div>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Volume (CBM)</label>
                        <div class="form-input-container">
                            <input type="text" name="volume" class="form-control-gf" placeholder="0.00" x-model="form.volume" @input="calculateVolumeWeight">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col" style="grid-column: 3 / span 2;">
                    <div class="form-group-gf" style="align-items: flex-start;">
                        <label class="form-label-gf" style="padding-top: 5px;">Chargeable Weight Calculation</label>
                    </div>
                    
                        <div class="form-input-container">
                            <div style="background: #f8faff; padding: 15px; border-radius: 8px; border: 1px dashed #c0ccda; width: 100%;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-size: 12px; color: #666;">Total Volume:</span>
                                    <span style="font-weight: 700; color: var(--primary-blue);" x-text="(parseFloat(form.volume) || 0).toFixed(4) + ' CBM'">0.00 CBM</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-size: 12px; color: #666;">Chargeable Weight:</span>
                                    <span style="font-weight: 700; color: #e83e8c;" x-text="(parseFloat(form.chargeable_weight) || 0).toFixed(2) + ' KG'">0.00 KG</span>
                                </div>
                                <input type="hidden" name="chargeable_weight" :value="form.chargeable_weight">
                            </div>
                        </div>

                            <div style="margin-top: 10px; font-size: 10px; color: #999;">
                                <i class="fa fa-info-circle"></i> Chargeable weight is calculated using Max(Gross Weight, Volume * 167)
                            </div>
                </div>
            </div>
            <div style="padding: 0 15px 15px 15px;">
                <!-- Tables Section -->
                <div style="font-size: 11px; font-weight: 600; color: #4b77be; display: flex; align-items: center; gap: 15px; margin: 10px 0 10px;">
                    COMMODITY & WAREHOUSE INVENTORY
                    <span style="flex: 1; height: 1px; background: #eee;"></span>
                </div>
                <div style="background: white; border: 1px solid #ddd; padding: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 11px; font-weight: 700; color: #444;">ITEMIZED COMMODITY LIST</span>
                        <div style="display: flex; gap: 5px;">
                            <button type="button" class="btn-freightx" style="padding: 2px 6px; font-size: 10px;" @click="addCommodity"><i class="fa fa-plus"></i> Add</button>
                            <button type="button" class="btn-default-gf" style="padding: 2px 6px; font-size: 10px; color: #e7505a;" @click="removeSelectedCommodities"><i class="fa fa-trash"></i> Remove</button>
                        </div>
                    </div>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" @click="commodities.forEach(c => c.selected = $event.target.checked)"></th>
                                <th>Description</th>
                                <th>HTS Code</th>
                                <th>P.O. Number</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(com, idx) in commodities" :key="idx">
                                <tr>
                                    <td style="text-align:center;"><input type="checkbox" x-model="com.selected"></td>
                                    <td><input type="text" :name="'commodities[' + idx + '][description]'" class="form-control-gf" style="width:100%;height:18px;" x-model="com.description"></td>
                                    <td><input type="text" :name="'commodities[' + idx + '][hts_code]'" class="form-control-gf" style="width:100%;height:18px;" x-model="com.hts_code"></td>
                                    <td><input type="text" :name="'commodities[' + idx + '][po_number]'" class="form-control-gf" style="width:100%;height:18px;" x-model="com.po_number"></td>
                                    <td style="text-align:center;"><i class="fa fa-times" style="cursor:pointer;color:#e7505a;" @click="removeCommodity(idx)"></i></td>
                                </tr>
                            </template>
                            <tr x-show="commodities.length === 0">
                                <td colspan="5" style="text-align: center; color: #999; padding: 20px !important;">
                                    No commodities added yet. Click <strong>Add</strong> to begin.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-grid-4" style="padding: 15px; padding-top: 0;">
                <!-- Remarks Section -->
                <div class="form-section-title" style="margin-top: 5px;">OPERATIONAL REMARKS & INSTRUCTIONS</div>
                <div class="flex flex-col" style="grid-column: 1 / span 2;">
                    <div class="form-group-gf" style="align-items: flex-start;">
                        <label class="form-label-gf" style="padding-top: 5px;">Handling Info</label>
                        <div class="form-input-container">
                            <textarea name="handling_info" class="form-control-gf" rows="4" style="height: auto; resize: vertical;" placeholder="Enter special handling instructions...">{{ old('handling_info', isset($booking) ? $booking->handling_info : '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col" style="grid-column: 3 / span 2;">
                    <div class="form-group-gf" style="align-items: flex-start;">
                        <label class="form-label-gf" style="padding-top: 5px;">Pickup & Delivery</label>
                        <div class="form-input-container">
                            <textarea name="pickup_delivery_instructions" class="form-control-gf" rows="4" style="height: auto; resize: vertical;" placeholder="Enter logistics instructions...">{{ old('pickup_delivery_instructions', isset($booking) ? $booking->pickup_delivery_instructions : '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>


</div>
</x-layout>