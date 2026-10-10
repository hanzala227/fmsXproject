<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .nav-tabs-custom { margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 4px 4px 0 0; }
        .nav-tabs-custom .tab-item { padding: 6px 14px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }
        .color-remark-tag { display: inline-block; width: 14px; height: 18px; border: 1px solid #ddd; margin-right: 5px; vertical-align: middle; background: #fff; }
        
        /* Ocean Import Consistent Buttons & Forms */
        .btn-gf-inline {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            text-decoration: none !important;
        }
        .btn-gf-inline:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }
        .btn-default-gf, .btn-default-gf.dark {
            background: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
            text-decoration: none !important;
        }
        .btn-default-gf:hover, .btn-default-gf.dark:hover {
            background: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        .btn-danger-gf {
            background: #ef4444 !important;
            color: #ffffff !important;
            border: 1px solid #ef4444 !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
        }
        .btn-danger-gf:hover {
            background: #dc2626 !important;
        }

        /* Table Headers & Typography Overrides */
        .memo-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 11px !important;
        }
        .memo-table th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            padding: 8px 10px !important;
            border-bottom: 2px solid #e2e8f0 !important;
            border-right: 1px solid #f1f5f9 !important;
        }
        .memo-table td {
            padding: 6px 8px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-right: 1px solid #f1f5f9 !important;
            color: #334155 !important;
        }
        .memo-table tbody tr:hover {
            background-color: #f8fafc !important;
        }
        h4, .portlet-title .caption-subject {
            color: #2563eb !important;
        }
        
        /* Form inputs & Select alignment fix */
        .form-control-gf {
            height: 26px !important;
            min-height: 26px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
            line-height: 20px !important;
            color: #1e293b !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            transition: border-color 0.15s ease !important;
        }
        select.form-control-gf {
            height: 26px !important;
            min-height: 26px !important;
            padding: 2px 20px 2px 6px !important;
            line-height: 20px !important;
            background-position: right 6px center !important;
            background-size: 9px !important;
        }
        textarea.form-control-gf {
            height: auto !important;
            min-height: 40px !important;
            line-height: 1.4 !important;
            padding: 4px 6px !important;
        }
        .form-group-gf {
            min-height: 26px !important;
            margin-bottom: 4px !important;
            display: flex !important;
            align-items: center !important;
        }
        .form-label-gf {
            font-size: 10px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            display: inline-block !important;
            width: 115px !important;
            text-align: right !important;
            margin-right: 6px !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            line-height: 26px !important;
            height: 26px !important;
        }
        .form-control-gf:focus {
            border-color: #2563eb !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
        }
        
        /* Stepper Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; display: flex; justify-content: center; align-items: flex-start; padding-top: 30px; overflow-y: auto; }
        .modal-card { background: #fff; border-radius: 6px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); width: 950px; max-width: 95%; animation: modalFadeIn 0.2s ease; display: flex; flex-direction: column; margin-bottom: 30px; }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header-premium { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff; border-radius: 6px 6px 0 0; }
        .modal-title-premium { font-size: 15px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 8px; }
        .modal-title-premium::before { content: "\f0f6"; font-family: "FontAwesome"; color: #2563eb; }
        
        .stepper-container { display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px 0; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .step-item { display: flex; align-items: center; gap: 8px; }
        .step-circle { width: 28px; height: 28px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; transition: all 0.3s; }
        .step-item.active .step-circle { background: #2563eb; color: white; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2); }
        .step-label { font-size: 13px; font-weight: 600; color: #6c757d; }
        .step-item.active .step-label { color: #333; }
        .step-line { flex: 0.5; height: 2px; background: #e2e8f0; margin-bottom: 0; max-width: 100px; }

        .modal-content-area { padding: 15px; overflow-y: auto; flex: 1; background: #fdfdfd; }
        .modal-footer-premium { padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px; background: #f8fafc; border-radius: 0 0 6px 6px; }
        
        .search-grid-lite { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; }
        .form-group-custom { display: flex; flex-direction: column; gap: 4px; }
        .label-custom { font-size: 11px; font-weight: 600; color: #555; }
        .input-custom { padding: 6px 10px; border: 1px solid #ccc; border-radius: 2px; font-size: 11px; height: 28px; box-sizing: border-box; }
        .input-custom:focus { border-color: #2563eb; outline: none; }
        
        .btn-premium { background: #2563eb; color: #fff; border: none; padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s; }
        .btn-premium:hover { background: #1d4ed8; }
        .btn-premium:disabled { background: #93c5fd; cursor: not-allowed; }
        .btn-premium-outline { background: #fff; border: 1px solid #ccc; color: #555; padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s; }
        .btn-premium-outline:hover { background: #f1f5f9; }

        .table-modern { width: 100%; border-collapse: collapse; font-size: 11px; border: 1px solid #e2e8f0; }
        .table-modern th { background: #f8fafc; color: #475569; padding: 8px 10px; text-align: left; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        .table-modern td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .table-modern tbody tr:hover { background: #f1f5f9; }
        
        /* Dropdown Item Styles */
        .dropdown-item { display: flex; align-items: center; padding: 8px 14px; font-size: 10px; font-weight: 600; color: #334155; text-decoration: none; cursor: pointer; transition: all 0.2s; }
        .dropdown-item:hover { background: #f8fafc; color: #2563eb; }
        .dropdown-item i { color: inherit; }
    </style>
    @endpush

    <div class="page-content" x-data="truckCreateApp()" x-cloak>
        {{-- TOP TABS --}}
        <div class="nav-tabs-custom">
            <a href="{{ route('truck.create') }}" class="tab-item {{ request()->routeIs('truck.create') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> New Shipment
            </a>
            <a href="{{ route('truck.index') }}" class="tab-item {{ request()->routeIs('truck.index') ? 'active' : '' }}">
                <i class="fa fa-list"></i> Shipment List
            </a>
            <a href="{{ route('truck.my-shipment-list') }}" class="tab-item {{ request()->routeIs('truck.my-shipment-list') ? 'active' : '' }}">
                <i class="fa fa-user"></i> My Shipment List
            </a>
        </div>

        <form id="truckShipmentForm" action="{{ isset($truckShipment) ? route('truck.update', $truckShipment->id) : route('truck.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="validateAndSubmit">
            @csrf
            @if(isset($truckShipment)) @method('PUT') @endif

            @if(session('success'))
                <div class="alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-danger">
                    <strong><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <!-- Breadcrumb -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/truck/list">Trucking</a> <i class="fa fa-angle-right"></i></li>
                <li>{{ isset($truckShipment) ? 'Edit Shipment' : 'New Shipment' }}</li>
            </ul>
        </div>

        <!-- Load Quotation Data Modal -->
        <template x-if="showQuoteModal">
            <div class="modal-overlay">
                <div class="modal-card" @click.away="closeQuoteModal()">
                    <div class="modal-header-premium">
                        <span class="modal-title-premium">Load Quotation Data</span>
                        <button @click="closeQuoteModal()" style="background:none;border:none;cursor:pointer;font-size:16px;color:#94a3b8;"><i class="fa fa-times"></i></button>
                    </div>

                    <!-- Stepper -->
                    <div class="stepper-container">
                        <div class="step-item" :class="quoteStep >= 1 ? 'active' : ''">
                            <div class="step-circle" x-text="quoteStep > 1 ? '✓' : '1'"></div>
                            <span class="step-label">Select Quotation</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" :class="quoteStep >= 2 ? 'active' : ''">
                            <div class="step-circle" x-text="quoteStep > 2 ? '✓' : '2'"></div>
                            <span class="step-label">Fill Shipment Data</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" :class="quoteStep >= 3 ? 'active' : ''">
                            <div class="step-circle" x-text="quoteStep > 3 ? '✓' : '3'"></div>
                            <span class="step-label">Select Invoice Items</span>
                        </div>
                    </div>

                    <div class="modal-content-area">
                        <!-- ===== STEP 1: Select Quotation ===== -->
                    <div x-show="quoteStep === 1">
                        <div class="search-grid-lite">
                            <div class="form-group-custom">
                                <label class="label-custom">Customer</label>
                                <select class="input-custom" x-model="filters.customer">
                                    <option value="">Select...</option>
                                    <template x-for="agent in agents" :key="agent.id">
                                        <option :value="agent.id" x-text="agent.company_name || agent.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">Port of Loading</label>
                                <select class="input-custom" x-model="filters.pol">
                                    <option value="">Select...</option>
                                    <template x-for="port in ports" :key="port.id">
                                        <option :value="port.id" x-text="port.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">Quote No.</label>
                                <input type="text" class="input-custom" x-model="filters.quote_no">
                            </div>

                            <div class="form-group-custom">
                                <label class="label-custom">Valid Date</label>
                                <input type="text" class="input-custom" x-model="filters.valid_date" placeholder="Start Date - End Date">
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">Port of Discharge</label>
                                <select class="input-custom" x-model="filters.pod">
                                    <option value="">Select...</option>
                                    <template x-for="port in ports" :key="port.id">
                                        <option :value="port.id" x-text="port.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">Status</label>
                                <select class="input-custom" x-model="filters.status">
                                    <option value="">Select...</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="EXPIRED">Expired</option>
                                    <option value="CANCELLED">Cancelled</option>
                                    <option value="CONFIRMED">Confirmed</option>
                                </select>
                            </div>

                            <div class="form-group-custom">
                                <label class="label-custom">Commodity</label>
                                <input type="text" class="input-custom" x-model="filters.commodity">
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">Sales</label>
                                <select class="input-custom" x-model="filters.sales">
                                    <option value="">Select...</option>
                                    <template x-for="user in users" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label class="label-custom">OP</label>
                                <select class="input-custom" x-model="filters.op">
                                    <option value="">Select...</option>
                                    <template x-for="user in users" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 20px;">
                            <button type="button" class="btn-premium-outline" @click="clearSearch()">Clear</button>
                            <button type="button" class="btn-premium" @click="searchQuotes()"><i class="fa fa-search"></i> Search</button>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                            <button type="button" class="btn-premium-outline" @click="showQuoteConfig = !showQuoteConfig"><i class="fa fa-cogs"></i> Config</button>
                        </div>

                        <div x-show="showQuoteConfig" style="margin-bottom: 8px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.quote_no" style="width: 12px; height: 12px;"> Quote No.</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.valid_date" style="width: 12px; height: 12px;"> Valid Date</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.status" style="width: 12px; height: 12px;"> Status</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.commodity" style="width: 12px; height: 12px;"> Commodity</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.pol" style="width: 12px; height: 12px;"> POL</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.pod" style="width: 12px; height: 12px;"> POD</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.sales" style="width: 12px; height: 12px;"> Sales</label>
                                <label style="font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="checkbox" x-model="colVisibility.op" style="width: 12px; height: 12px;"> OP</label>
                            </div>
                        </div>

                        <div class="table-responsive" style="margin-bottom: 10px; max-height: 250px; overflow-y: auto;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Select</th>
                                    <th x-show="colVisibility.quote_no">Quote No.</th>
                                    <th x-show="colVisibility.valid_date">Valid Date</th>
                                    <th x-show="colVisibility.status">Status</th>
                                    <th>Creation Date</th>
                                    <th x-show="colVisibility.commodity">Commodity</th>
                                    <th x-show="colVisibility.pol">POL</th>
                                    <th x-show="colVisibility.pod">POD</th>
                                    <th x-show="colVisibility.sales">Sales</th>
                                    <th x-show="colVisibility.op">OP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(quote, idx) in filteredQuotes" :key="quote.id">
                                    <tr @click="selectedQuote = quote; quoteStep = 2" style="cursor: pointer;">
                                        <td style="text-align: center;"><input type="radio" name="selected_quote" :value="quote.id" @click.stop x-model="quoteSearch.selected_id"></td>
                                        <td x-show="colVisibility.quote_no" x-text="quote.quote_no"></td>
                                        <td x-show="colVisibility.valid_date" x-text="quote.expiry_date"></td>
                                        <td x-show="colVisibility.status"><span x-text="quote.status"></span></td>
                                        <td x-text="quote.created_at ? quote.created_at.substring(0,10) : ''"></td>
                                        <td x-show="colVisibility.commodity" x-text="quote.commodity || 'N/A'"></td>
                                        <td x-show="colVisibility.pol" x-text="quote.pol_name || 'N/A'"></td>
                                        <td x-show="colVisibility.pod" x-text="quote.pod_name || 'N/A'"></td>
                                        <td x-show="colVisibility.sales" x-text="quote.sales_person_name || 'N/A'"></td>
                                        <td x-show="colVisibility.op" x-text="quote.op_name || 'N/A'"></td>
                                    </tr>
                                </template>
                                <template x-if="filteredQuotes.length === 0">
                                    <tr>
                                        <td colspan="10" style="text-align: center; color: #94a3b8; font-size: 12px; padding: 20px;">No quotations found. Use the search filters above.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <!-- ===== STEP 2: Fill in shipment data ===== -->
                    <div x-show="quoteStep === 2">
                        <div class="hbl-header">Select a Route</div>
                        <div class="table-responsive" style="margin-bottom: 15px;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Select</th>
                                    <th>Place of Receipt</th>
                                    <th>Port of Loading</th>
                                    <th>Port of Discharge</th>
                                    <th>Place of Delivery</th>
                                    <th>Final Destination</th>
                                    <th>Carrier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="selectedQuote">
                                <tr>
                                    <td style="text-align: center;"><input type="radio" :checked="true" name="route_select"></td>
                                    <td x-text="selectedQuote.pol_name || '-'"></td>
                                    <td x-text="selectedQuote.pol_name || '-'"></td>
                                    <td x-text="selectedQuote.pod_name || '-'"></td>
                                    <td x-text="selectedQuote.pod_name || '-'"></td>
                                    <td x-text="selectedQuote.pod_name || '-'"></td>
                                    <td><span x-text="selectedQuote.carrier_name || '-'"></span></td>
                                </tr>
                                </template>
                                <template x-if="!selectedQuote">
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 10px;">Please select a quotation first.</td>
                                </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>

                        <div class="hbl-header">Fill in the Shipment Information</div>
                        <div class="search-grid-lite" style="grid-template-columns: repeat(2, 1fr);">
                            <div class="form-column">
                                <div class="form-group-custom"><label class="label-custom" style="color: #ef4444;">*MB/L No.</label><input type="text" class="input-custom" x-model="quoteForm.mbl_no"></div>
                                <div class="form-group-custom"><label class="label-custom">HB/L No.</label><input type="text" class="input-custom" x-model="quoteForm.hbl_no"></div>
                                <div class="form-group-custom"><label class="label-custom">ETD</label><input type="date" class="input-custom" x-model="quoteForm.etd"></div>
                                <div class="form-group-custom"><label class="label-custom" style="color: #ef4444;">*Customer</label>
                                    <select class="input-custom" x-model="quoteForm.customer_id">
                                        <option value="">Select...</option>
                                        <template x-for="agent in agents" :key="agent.id">
                                            <option :value="agent.id" x-text="agent.company_name || agent.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="form-group-custom"><label class="label-custom">Sales</label><div class="input-custom" style="background:#f9f9f9; display:flex; align-items:center; border: 1px solid #ccc;"><span x-text="quoteForm.sales" style="font-size: 11px; color: #555;"></span></div></div>
                                <div class="form-group-custom"><label class="label-custom">OP</label><div class="input-custom" style="background:#f9f9f9; display:flex; align-items:center; border: 1px solid #ccc;"><span x-text="quoteForm.op" style="font-size: 11px; color: #555;"></span></div></div>
                            </div>
                            <div class="form-column">
                                <div class="form-group-custom"><label class="label-custom">Vessel/Flight No.</label><input type="text" class="input-custom" x-model="quoteForm.vessel_flight_no"></div>
                                <div class="form-group-custom"><label class="label-custom" style="color: #ef4444;">*ETA</label><input type="date" class="input-custom" x-model="quoteForm.eta"></div>
                                <div class="form-group-custom"><label class="label-custom">Carrier Bkg. No.</label><input type="text" class="input-custom" x-model="quoteForm.carrier_bkg_no"></div>
                                <div class="form-group-custom"><label class="label-custom">Shipper</label>
                                    <select class="input-custom" x-model="quoteForm.shipper_id">
                                        <option value="">Select...</option>
                                        <template x-for="agent in agents" :key="agent.id">
                                            <option :value="agent.id" x-text="agent.company_name || agent.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="form-group-custom"><label class="label-custom">Consignee</label>
                                    <select class="input-custom" x-model="quoteForm.consignee_id">
                                        <option value="">Select...</option>
                                        <template x-for="agent in agents" :key="agent.id">
                                            <option :value="agent.id" x-text="agent.company_name || agent.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="form-group-custom"><label class="label-custom">Trucker</label>
                                    <select class="input-custom" x-model="quoteForm.trucker_id">
                                        <option value="">Select...</option>
                                        <template x-for="tp in truckers" :key="tp.id">
                                            <option :value="tp.id" x-text="tp.company_name || tp.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-column" style="margin-top: 10px;">
                            <div class="form-group-custom"><label class="label-custom">Detail</label><input type="text" class="input-custom" x-model="quoteForm.detail"></div>
                        </div>
                    </div>

                    <!-- ===== STEP 3: Select invoice items ===== -->
                    <div x-show="quoteStep === 3">
                        <div class="hbl-header">Select Freight Item(s)</div>
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 4px;">
                            <input type="checkbox" x-model="saveAsDraftInvoice" style="margin: 0; width: 12px; height: 12px; cursor: pointer; accent-color: #3b82f6;">
                            <span style="font-size: 10px; color: #475569; font-weight: 600;">Save as a draft invoice</span>
                        </div>

                        <div class="table-responsive" style="margin-bottom: 15px; max-height: 250px; overflow-y: auto;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Select</th>
                                    <th>Freight Code</th>
                                    <th>Freight Description</th>
                                    <th>Unit</th>
                                    <th>Currency</th>
                                    <th>Volume</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in (selectedQuote?.items || [])" :key="idx">
                                <tr>
                                    <td style="text-align: center;"><input type="checkbox" x-model="item.selected"></td>
                                    <td x-text="item.charge_code"></td>
                                    <td x-text="item.charge_name"></td>
                                    <td x-text="item.unit"></td>
                                    <td x-text="item.currency"></td>
                                    <td x-text="item.qty"></td>
                                    <td x-text="item.rate"></td>
                                    <td x-text="item.amount ? item.amount.toLocaleString() : '0.00'"></td>
                                </tr>
                                </template>
                                <tr x-show="!selectedQuote?.items || selectedQuote.items.length === 0">
                                    <td colspan="8" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 20px;">No charge items available for this quotation. Items can be added after shipment creation.</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                </div>

                <div class="modal-footer-premium">
                    <button type="button" class="btn-premium-outline" @click="closeQuoteModal()">Cancel</button>
                    <button type="button" x-show="quoteStep > 1" class="btn-premium-outline" @click="quoteStep--">Back</button>

                    <button type="button" x-show="quoteStep < 3"
                            :class="((quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mbl_no || !quoteForm.eta || !quoteForm.customer_id))) ? 'btn-premium opacity-50 cursor-not-allowed' : 'btn-premium'"
                            :disabled="(quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mbl_no || !quoteForm.eta || !quoteForm.customer_id))"
                            @click="quoteStep === 1 ? selectQuote(selectedQuote) : quoteStep < 3 ? quoteStep++ : null">
                        Next
                    </button>

                    <button type="button" x-show="quoteStep === 3" class="btn-premium" @click="confirmQuoteSelection()">
                        <i class="fa fa-check"></i> Confirm & Load
                    </button>
                </div>
            </div>
        </div>
        </template>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px; margin: 0;">{{ isset($truckShipment) ? 'Edit Truck Shipment' : 'New Truck Shipment' }}</h1>
            <div style="display: flex; gap: 8px;">
                <button type="submit" form="truckShipmentForm" class="btn-freightx" style="padding:6px 24px;"><i class="fa fa-save"></i> {{ isset($truckShipment) ? 'UPDATE' : 'SAVE' }}</button>
                
                <!-- Tools Dropdown Header -->
                <div style="position: relative; display: inline-block;">
                    <button type="button" class="btn-default-gf" style="padding:6px 14px;" @click.stop="toolsOpen = !toolsOpen">
                        <i class="fa fa-cogs"></i> Tools <i class="fa fa-angle-down"></i>
                    </button>
                    <div x-show="toolsOpen" @click.away="toolsOpen = false" x-cloak
                         style="position: absolute; right: 0; top: 100%; margin-top: 4px; z-index: 1050; min-width: 160px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); padding: 4px 0;">
                        <button type="button" @click="copyShipmentForm()" 
                                style="display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #334155; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-copy" style="color: #475569; font-size: 13px; width: 14px;"></i> Copy
                        </button>
                        <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                        <button type="button" @click="deleteShipment()" 
                                :disabled="!saved"
                                :style="saved ? 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #ef4444; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;' : 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #cbd5e1; background: transparent; border: none; cursor: not-allowed; opacity: 0.6;'"
                                onmouseover="if(saved) this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-trash-o" :style="saved ? 'color: #ef4444; font-size: 13px; width: 14px;' : 'color: #cbd5e1; font-size: 13px; width: 14px;'"></i> Delete
                        </button>
                    </div>
                </div>

                <a href="/truck/list" class="btn-default-gf" style="padding:6px 20px;text-decoration:none;" target="_blank"><i class="fa fa-arrow-left"></i> BACK TO LIST</a>
            </div>
        </div>
        <ul class="gf-tabs">
            <li :class="activeTab === 'basic' ? 'active' : ''" @click="switchTab('basic')"><a>Basic</a></li>
            <li :class="activeTab === 'container' ? 'active' : ''" @click="switchTab('container')"><a>Container & Item</a></li>
            <li :class="activeTab === 'accounting' ? 'active' : ''" @click="switchTab('accounting')"><a>Accounting <i class="fa fa-sliders" style="margin-left: 4px; color: #888;"></i></a></li>
            <li :class="activeTab === 'status' ? 'active' : ''" @click="switchTab('status')"><a>Status</a></li>
        </ul>

        <!-- ==================== BASIC TAB ==================== -->
        <div x-show="activeTab === 'basic'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption caption-subject">
                        <i class="fa fa-shield" style="color: #3b82f6; margin-right: 6px;"></i> MB/L INFORMATION
                    </div>
                    <div style="position: relative; display: inline-block;">
                        <button type="button" class="btn-default-gf" @click.stop="toolsOpen = !toolsOpen"><i class="fa fa-cogs"></i> Tools <i class="fa fa-angle-down"></i></button>
                        <div x-show="toolsOpen" @click.away="toolsOpen = false" x-cloak
                             style="position: absolute; right: 0; top: 100%; margin-top: 4px; z-index: 1050; min-width: 160px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); padding: 4px 0;">
                            <button type="button" @click="copyShipmentForm()" 
                                    style="display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #334155; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;"
                                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <i class="fa fa-copy" style="color: #475569; font-size: 13px; width: 14px;"></i> Copy
                            </button>
                            <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                            <button type="button" @click="deleteShipment()" 
                                    :disabled="!saved"
                                    :style="saved ? 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #ef4444; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;' : 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #cbd5e1; background: transparent; border: none; cursor: not-allowed; opacity: 0.6;'"
                                    onmouseover="if(saved) this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                <i class="fa fa-trash-o" :style="saved ? 'color: #ef4444; font-size: 13px; width: 14px;' : 'color: #cbd5e1; font-size: 13px; width: 14px;'"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="portlet-body">
                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <span class="form-label-gf required">File No.</span>
                            <div class="form-input-container">
                                <input type="text" name="file_no" class="form-control-gf" x-model="form.file_no" readonly>
                                <span class="form-error" x-show="errors.file_no" x-text="errors.file_no"></span>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf required">Post Date</span>
                            <div class="form-input-container">
                                <input type="date" name="post_date" class="form-control-gf" x-model="form.post_date" required>
                                <span class="form-error" x-show="errors.post_date" x-text="errors.post_date"></span>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf required">Office</span>
                            <div class="form-input-container">
                                <select name="office_id" class="form-control-gf" x-model="form.office_id" required>
                                    <option value="">Select Office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                                <span class="form-error" x-show="errors.office_id" x-text="errors.office_id"></span>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Type</span>
                            <div class="form-input-container">
                                <select name="ship_type" class="form-control-gf" x-model="form.ship_type">
                                    <option value="Trucking">Trucker</option>
                                    <option value="Ocean">Ocean</option>
                                    <option value="Air">Air</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group-gf">
                            <span class="form-label-gf">MB/L No.</span>
                            <div class="form-input-container">
                                <input type="text" name="mbl_no" class="form-control-gf" x-model="form.mbl_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">HB/L No.</span>
                            <div class="form-input-container">
                                <input type="text" name="hbl_no" class="form-control-gf" x-model="form.hbl_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Vessel/Flight No.</span>
                            <div class="form-input-container">
                                <input type="text" name="vessel_flight_no" class="form-control-gf" x-model="form.vessel_flight_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Carrier Bkg. No.</span>
                            <div class="form-input-container">
                                <input type="text" name="carrier_bkg_no" class="form-control-gf" x-model="form.carrier_bkg_no">
                            </div>
                        </div>
                        
                        <div class="form-group-gf">
                            <span class="form-label-gf">Quotation No.</span>
                            <div class="form-input-container">
                                <select name="quotation_id" class="form-control-gf" x-model="form.quotation_id">
                                    <option value="">Select...</option>
                                    @foreach($quotations as $quote)
                                        <option value="{{ $quote->id }}">{{ $quote->quote_no }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" @click="showQuoteModal = true"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Customer</span>
                            <div class="form-input-container">
                                <x-inline-select name="customer_id" :options="$agents" module="trade-partner" x-model="form.customer_id" class="form-control-gf" />
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Customer Ref No.</span>
                            <div class="form-input-container">
                                <input type="text" name="customer_ref_no" class="form-control-gf" x-model="form.customer_ref_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Shipper</span>
                            <div class="form-input-container">
                                <x-inline-select name="shipper_id" :options="$agents" module="trade-partner" x-model="form.shipper_id" class="form-control-gf" />
                            </div>
                        </div>

                        <div class="form-group-gf">
                            <span class="form-label-gf">Consignee</span>
                            <div class="form-input-container">
                                <x-inline-select name="consignee_id" :options="$agents" module="trade-partner" x-model="form.consignee_id" class="form-control-gf" />
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Trucker</span>
                            <div class="form-input-container">
                                <select name="trucker_id" class="form-control-gf" x-model="form.trucker_id">
                                    <option value="">Select Trucker...</option>
                                    @foreach($truckers as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-external-link"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Bill To</span>
                            <div class="form-input-container">
                                <select name="bill_to_id" class="form-control-gf" x-model="form.bill_to_id">
                                    <option value="">Select...</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-external-link"></i></button>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;background:#3b73af;border-color:#3b73af;color:#fff;"><i class="fa fa-share"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Sales</span>
                            <div class="form-input-container">
                                <select name="sales_id" class="form-control-gf" x-model="form.sales_id">
                                    <option value="">Select Sales...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group-gf">
                            <span class="form-label-gf">OP</span>
                            <div class="form-input-container">
                                <select name="op_id" class="form-control-gf" x-model="form.op_id">
                                    <option value="">Select Operator...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <hr>

                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <span class="form-label-gf">Port of Loading</span>
                            <div class="form-input-container">
                                <x-inline-select name="pol_id" :options="$ports" module="port" x-model="form.pol_id" class="form-control-gf" />
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">ETD</span>
                            <div class="form-input-container">
                                <input type="date" name="etd" class="form-control-gf" x-model="form.etd">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Port of Discharge</span>
                            <div class="form-input-container">
                                <x-inline-select name="pod_id" :options="$ports" module="port" x-model="form.pod_id" class="form-control-gf" />
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">ETA</span>
                            <div class="form-input-container">
                                <input type="date" name="eta" class="form-control-gf" x-model="form.eta">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <span class="form-label-gf">Final Destination</span>
                            <div class="form-input-container">
                                <select name="final_destination_id" class="form-control-gf" x-model="form.final_destination_id">
                                    <option value="">Select...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}">{{ $port->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Final ETA</span>
                            <div class="form-input-container">
                                <input type="date" name="feta" class="form-control-gf" x-model="form.feta">
                            </div>
                        </div>
                    </div>
                    
                    <hr>

                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <span class="form-label-gf">Empty Pickup</span>
                            <div class="form-input-container">
                                <select name="empty_pickup_location_id" class="form-control-gf" x-model="form.empty_pickup_location_id">
                                    <option value="">Select...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-edit"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Freight Pickup</span>
                            <div class="form-input-container">
                                <select name="freight_pickup_location_id" class="form-control-gf" x-model="form.freight_pickup_location_id">
                                    <option value="">Select...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-edit"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Delivery To</span>
                            <div class="form-input-container">
                                <select name="delivery_to_location_id" class="form-control-gf" x-model="form.delivery_to_location_id">
                                    <option value="">Select...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-edit"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Empty Return</span>
                            <div class="form-input-container">
                                <select name="empty_return_location_id" class="form-control-gf" x-model="form.empty_return_location_id">
                                    <option value="">Select...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-default-gf dark" style="padding:0 6px;height:26px;" onclick="window.open('/trade-partner/create','_blank')"><i class="fa fa-edit"></i></button>
                            </div>
                        </div>

                        <div class="form-group-gf">
                            <span class="form-label-gf">Package</span>
                            <div class="form-input-container">
                                <input type="number" step="any" name="pkg_qty" class="form-control-gf" style="width: 30%; text-align: right;" x-model="form.pkg_qty">
                                <select name="pkg_unit_id" class="form-control-gf" style="width: 70%;" x-model="form.pkg_unit_id">
                                    <option value="">Select...</option>
                                    @foreach($packageUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Weight</span>
                            <div class="form-input-container">
                                <input type="number" step="any" name="weight_kg" class="form-control-gf" style="text-align: right;" x-model="form.weight_kg"> <span style="font-size: 10px; color: #555;">KG</span>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Measurement</span>
                            <div class="form-input-container">
                                <input type="number" step="any" name="volume_cbm" class="form-control-gf" style="text-align: right;" x-model="form.volume_cbm"> <span style="font-size: 10px; color: #555;">CBM</span>
                                <input type="number" step="any" name="measure_cft" class="form-control-gf" style="text-align: right;" x-model="form.measure_cft"> <span style="font-size: 10px; color: #555;">CFT</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <span class="form-label-gf">Estimated Delivery Date</span>
                            <div class="form-input-container">
                                <input type="date" name="est_delivery_date" class="form-control-gf" x-model="form.est_delivery_date">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf">Delivered</span>
                            <div class="form-input-container">
                                <input type="checkbox" name="is_delivered" x-model="form.is_delivered" value="1">
                                <input type="date" name="delivered_date" class="form-control-gf" x-model="form.delivered_date" :disabled="!form.is_delivered">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <span class="form-label-gf" style="color: #3b82f6; text-align: left; margin-left: 10px; cursor: pointer;" @click="showMore = !showMore">
                                More <i class="fa" :class="showMore ? 'fa-minus-square-o' : 'fa-plus-square-o'"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div x-show="showMore" x-cloak>
                        <hr>
                        <div class="form-grid-4">
                            <div class="form-group-gf">
                                <span class="form-label-gf">E-Commerce</span>
                                <div class="form-input-container">
                                    <input type="checkbox" name="is_ecommerce" x-model="form.is_ecommerce" value="1">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <span class="form-label-gf">Truck No.</span>
                                <div class="form-input-container">
                                    <input type="text" name="truck_no" class="form-control-gf" x-model="form.truck_no">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <span class="form-label-gf">Driver Name</span>
                                <div class="form-input-container">
                                    <input type="text" name="driver_name" class="form-control-gf" x-model="form.driver_name">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <span class="form-label-gf">Driver Phone</span>
                                <div class="form-input-container">
                                    <input type="text" name="driver_phone" class="form-control-gf" x-model="form.driver_phone">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memo Section -->
            <div class="memo-section">
                <div class="memo-header-container">
                    <span style="font-size: 12px; font-weight: 600; color: #555;">Memo</span>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button type="button" class="btn-default-gf dark" @click="openMemoModal()"><i class="fa fa-plus"></i> Add Memo</button>
                    </div>
                </div>
                <div style="display: flex;">
                    <div style="flex: 7;">
                        <table class="memo-table memo-table-dark">
                            <thead>
                                <tr>
                                    <th style="width: 30px; text-align: center;">#</th>
                                    <th style="width: 30px; text-align: center;"><i class="fa fa-bell" style="color: #fff;"></i></th>
                                    <th>Subject</th>
                                    <th>Last Modified</th>
                                    <th>Created</th>
                                    <th style="width:80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="memos.length === 0">
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #888; height: 100px;">No memos found. Click "Add Memo" to create one.</td>
                                    </tr>
                                </template>
                                <template x-for="(memo, idx) in memos" :key="memo.id || idx">
                                    <tr :style="selectedMemo === memo ? 'background: #eef7ff; cursor: pointer;' : 'cursor: pointer;'" @click="viewMemo(memo)">
                                        <td style="text-align:center;" x-text="idx + 1"></td>
                                        <td style="text-align:center;" @click.stop><input type="checkbox" x-model="memo.has_alert" @change="toggleMemoAlert(memo)"></td>
                                        <td><a href="#" @click.prevent.stop="viewMemo(memo)" style="color:#337ab7;font-weight:600;text-decoration:none;" x-text="memo.subject"></a></td>
                                        <td x-text="memo.updated_at || memo.created_at"></td>
                                        <td x-text="memo.created_at"></td>
                                        <td style="text-align:center;" @click.stop>
                                            <button type="button" class="btn-default-gf dark" style="padding:1px 5px;font-size:9px;" @click="editMemo(memo)" title="Edit Memo"><i class="fa fa-pencil"></i></button>
                                            <button type="button" class="btn-default-gf dark" style="padding:1px 5px;font-size:9px;color:#d05454;" @click="deleteMemo(idx)" title="Delete Memo"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div style="flex: 3; background: #eef1f5; padding: 10px; border: 1px solid #ddd; border-left: none;">
                        <textarea class="form-control-gf" style="height: 100px; width: 100%; border: 1px solid #ccc; resize: none; background: #fff;" x-model="selectedMemoContent" @input="onMemoContentInput()" :placeholder="selectedMemo ? 'Enter memo content...' : 'Select a memo to view content...'" :disabled="!selectedMemo"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ==================== CONTAINER & ITEM TAB ==================== -->
        <div x-show="activeTab === 'container'" x-cloak>
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption caption-subject">
                        <svg width="12" height="16" viewBox="0 0 12 16" fill="none" style="margin-right: 4px;">
                            <path d="M0 0H12V11L6 16L0 11V0Z" fill="#fff"/>
                        </svg>
                        MB/L INFORMATION
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button type="button" class="btn-default-gf" @click="saveAllContainers()" :disabled="!saved">
                            <i class="fa fa-save"></i> SAVE ALL CONTAINERS
                        </button>
                        <button type="button" class="btn-default-gf" @click="saveAllCommodities()" :disabled="!saved">
                            <i class="fa fa-save"></i> SAVE ALL COMMODITIES
                        </button>
                    </div>
                </div>
                
                <div class="portlet-body">
                    <div class="well">
                    <!-- PO No -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <h4 style="font-size: 13px; font-weight: 700; margin: 0; color: #2563eb;">P.O. No. <span style="color: #64748b; font-weight: normal; font-size: 10px; margin-left: 5px;">Please list down P.O. No. for this MB/L</span></h4>
                        <div style="display: flex; align-items: center; gap: 10px; font-size: 10px;">
                            <span style="font-weight: 600; color: #475569;">P.O. Mapping</span>
                            <label style="display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="radio" x-model="po_mapping" value="C"> Container based</label>
                            <label style="display: flex; align-items: center; gap: 4px; cursor: pointer;"><input type="radio" x-model="po_mapping" value="I"> Item based</label>
                        </div>
                    </div>
                    <div style="display:flex;gap:4px;margin-bottom:15px;">
                        <input type="text" class="form-control-gf" placeholder="Add P.O. here..." x-model="newPoNo" style="flex:1;">
                        <button type="button" class="btn-gf-inline" @click="addPoNo()" style="margin-top:0;"><i class="fa fa-plus"></i> Add</button>
                    </div>
                    <template x-if="poNos.length > 0">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
                            <template x-for="(po, idx) in poNos" :key="idx">
                                <span style="display:inline-flex;align-items:center;gap:4px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:2px 8px;font-size:11px;color:#1e40af;font-weight:600;">
                                    <span x-text="po"></span>
                                    <button type="button" @click="poNos.splice(idx,1)" style="border:none;background:none;cursor:pointer;padding:0;color:#3b82f6;font-size:12px;line-height:1;">&times;</button>
                                </span>
                            </template>
                        </div>
                    </template>
                    
                    <hr style="border-top:1px solid #e2e8f0;margin:12px 0;">
                    
                    <!-- Container List -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px; flex-wrap: wrap;">
                        <h4 style="font-size: 13px; font-weight: 700; margin: 0; color: #2563eb; margin-right: 4px;">Container List</h4>
                        <button type="button" class="btn-gf-inline" @click="addContainer()"><i class="fa fa-plus"></i> Add</button>
                        <button type="button" class="btn-default-gf" @click="addContainers(5)"><i class="fa fa-plus-circle"></i> +5</button>
                        <button type="button" class="btn-default-gf" @click="duplicateContainer()" title="Duplicate Container"><i class="fa fa-clone"></i> Duplicate</button>
                        <button type="button" class="btn-default-gf" style="color:#ef4444;border-color:#fca5a5;" @click="deleteSelectedContainers()" :disabled="selectedContainers.length === 0" title="Delete Selected"><i class="fa fa-trash"></i> Delete Selected</button>
                        <button type="button" class="btn-gf-inline" @click="createPierPassAP()"><i class="fa fa-credit-card"></i> Create Pier Pass A/P</button>
                    </div>
                    
                    <table class="memo-table" style="margin-bottom: 15px; text-align: center;">
                        <thead>
                            <tr>
                                <th style="width: 25px; text-align: center;"><input type="checkbox" @change="toggleAllContainers($event.target.checked)"></th>
                                <th style="width: 25px; text-align: center;">#</th>
                                <th style="text-align: center;">Pier Pass A/P</th>
                                <th style="text-align: center;">Container No.</th>
                                <th>TP/SZ</th>
                                <th>Seal No.</th>
                                <th>Pick Up No.</th>
                                <th>PKG</th>
                                <th>Weight</th>
                                <th>Measurement</th>
                                <th>LFD</th>
                                <th>Appt.</th>
                                <th>Pick Up</th>
                                <th>Empty Return</th>
                                <th x-show="po_mapping === 'C'">P.O. No.</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="containers.length === 0">
                                <tr>
                                    <td :colspan="po_mapping === 'C' ? 16 : 15" style="text-align: center; color: #94a3b8; height: 40px;">
                                        No Data Available. Please click <span style="color: #2563eb; font-weight: 600; cursor:pointer;" @click="addContainer()">here</span> to add a new row.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(cont, idx) in containers" :key="cont.id || idx">
                                <tr :style="cont._unsaved ? 'background: #fffbeb;' : ''">
                                    <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedContainers"></td>
                                    <td style="text-align:center;" x-text="idx + 1"></td>
                                    <td><input type="text" class="form-control-gf" x-model="cont.pier_pass" style="width:70px;" @input="cont._unsaved = true"></td>
                                    <td><input type="text" class="form-control-gf" x-model="cont.container_no" placeholder="Container No." style="width:120px;" @input="cont._unsaved = true"></td>
                                    <td>
                                        <select class="form-control-gf" x-model="cont.container_type_id" style="width:80px;" @change="cont._unsaved = true">
                                            <option value="">Select...</option>
                                            @foreach($containerTypes as $ct)<option value="{{ $ct->id }}">{{ $ct->code }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control-gf" x-model="cont.seal_no" style="width:80px;" @input="cont._unsaved = true"></td>
                                    <td><input type="text" class="form-control-gf" x-model="cont.pickup_no" style="width:80px;" @input="cont._unsaved = true"></td>
                                    <td><input type="number" step="any" class="form-control-gf" x-model="cont.pkg" style="width:60px;text-align:right;" @input="cont._unsaved = true"></td>
                                    <td><input type="number" step="any" class="form-control-gf" x-model="cont.weight" style="width:70px;text-align:right;" @input="cont._unsaved = true"></td>
                                    <td><input type="number" step="any" class="form-control-gf" x-model="cont.measurement" style="width:70px;text-align:right;" @input="cont._unsaved = true"></td>
                                    <td><input type="date" class="form-control-gf" x-model="cont.lfd" style="width:90px;" @change="cont._unsaved = true"></td>
                                    <td><input type="date" class="form-control-gf" x-model="cont.appointment" style="width:90px;" @change="cont._unsaved = true"></td>
                                    <td><input type="date" class="form-control-gf" x-model="cont.pickup_date" style="width:90px;" @change="cont._unsaved = true"></td>
                                    <td><input type="date" class="form-control-gf" x-model="cont.empty_return_date" style="width:90px;" @change="cont._unsaved = true"></td>
                                    <td x-show="po_mapping === 'C'"><input type="text" class="form-control-gf" x-model="cont.po_no" style="width:100px;" @input="cont._unsaved = true"></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-default-gf" style="padding:2px 6px;font-size:10px;color:#2563eb;" @click="saveContainer(idx)" :disabled="!cont._unsaved" title="Save">
                                            <i class="fa fa-save"></i>
                                        </button>
                                        <button type="button" class="btn-default-gf" style="padding:2px 6px;font-size:10px;color:#ef4444;" @click="deleteContainer(idx)" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr style="background: #f8fafc;">
                                <td colspan="4"></td>
                                <td style="text-align: center;"><input type="radio" name="total_source" value="container" x-model="totalSource"></td>
                                <td colspan="2" style="text-align: left; font-weight: 600; color: #475569;">Container Total</td>
                                <td style="text-align: right; color: #2563eb; font-weight: 700;" x-text="containerTotals.pkg"></td>
                                <td style="text-align: right; color: #2563eb; font-weight: 700;" x-text="containerTotals.weight"></td>
                                <td style="text-align: right; color: #2563eb; font-weight: 700;" x-text="containerTotals.measurement"></td>
                                <td :colspan="po_mapping === 'C' ? 5 : 4"></td>
                            </tr>
                            <tr style="background: #f8fafc;">
                                <td colspan="4"></td>
                                <td style="text-align: center;"><input type="radio" name="total_source" value="manual" x-model="totalSource"></td>
                                <td colspan="2" style="text-align: left; font-weight: 600; color: #475569;">Manual Input Total</td>
                                <td><input type="text" class="form-control-gf" style="text-align: right; background: #ffffff;" x-model="manualTotal.pkg" :disabled="totalSource !== 'manual'"></td>
                                <td style="text-align: right;">
                                    <input type="text" class="form-control-gf" style="width: 60px; display: inline-block; text-align: right; background: #ffffff;" x-model="manualTotal.weight" :disabled="totalSource !== 'manual'"> KG
                                </td>
                                <td style="text-align: right;">
                                    <input type="text" class="form-control-gf" style="width: 60px; display: inline-block; text-align: right; background: #ffffff;" x-model="manualTotal.measurement" :disabled="totalSource !== 'manual'"> CBM
                                </td>
                                <td :colspan="po_mapping === 'C' ? 5 : 4"></td>
                            </tr>
                            <tr style="background: #f8fafc;">
                                <td colspan="4"></td>
                                <td style="text-align: center;"><input type="radio" name="total_source" value="receiving" x-model="totalSource" disabled></td>
                                <td colspan="2" style="text-align: left; font-weight: 600; color: #475569;">Receiving Total</td>
                                <td :colspan="po_mapping === 'C' ? 8 : 7" style="text-align: left;">
                                    <div style="display:flex;gap:6px;align-items:center;">
                                        <button type="button" class="btn-gf-inline" @click="openWarehouseLoadModal()"><i class="fa fa-database"></i> Load from Warehouse</button>
                                        <button type="button" class="btn-gf-inline" @click="openCreateReceiptModal()"><i class="fa fa-link"></i> Create Receipt and Link</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <hr style="border-top:1px solid #e2e8f0;margin:12px 0;">
                    
                    <!-- Commodity -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                        <h4 style="font-size: 13px; font-weight: 700; margin: 0; color: #2563eb; margin-right: 4px;">Commodity</h4>
                        <button type="button" class="btn-gf-inline" @click="addCommodity()"><i class="fa fa-plus"></i> Add Commodity</button>
                        <button type="button" class="btn-default-gf" style="color:#ef4444;border-color:#fca5a5;" @click="deleteSelectedCommodities()" :disabled="selectedCommodities.length === 0" title="Delete Selected"><i class="fa fa-trash"></i> Delete Selected</button>
                    </div>
                    
                    <table class="memo-table" style="margin-bottom: 15px;">
                        <thead>
                            <tr>
                                <th style="width: 25px; text-align: center;"><input type="checkbox" @change="toggleAllCommodities($event.target.checked)"></th>
                                <th><span style="color: #d05454;">*</span>Commodity Description</th>
                                <th>HTS Code</th>
                                <th>Container</th>
                                <th x-show="po_mapping === 'I'">P.O. No.</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="commodities.length === 0">
                                <tr>
                                    <td :colspan="po_mapping === 'I' ? 6 : 5" style="text-align: center; color: #888; height: 35px;">
                                        No Data Available. Please click <span style="color: #32c5d2; cursor:pointer;" @click="addCommodity()">here</span> to add a new row.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(comm, idx) in commodities" :key="comm.id || idx">
                                <tr :style="comm._unsaved ? 'background: #fffbeb;' : ''">
                                    <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedCommodities"></td>
                                    <td><input type="text" class="form-control-gf" x-model="comm.description" placeholder="Commodity description" @input="comm._unsaved = true"></td>
                                    <td><input type="text" class="form-control-gf" x-model="comm.hts_code" placeholder="HTS Code" style="width:120px;" @input="comm._unsaved = true"></td>
                                    <td>
                                        <select class="form-control-gf" x-model="comm.container_idx" style="width:120px;" @change="comm._unsaved = true">
                                            <option value="">Select...</option>
                                            <template x-for="(c, ci) in containers" :key="ci">
                                                <option :value="ci" x-text="c.container_no || 'Container ' + (ci+1)"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td x-show="po_mapping === 'I'"><input type="text" class="form-control-gf" x-model="comm.po_no" style="width:100px;" @input="comm._unsaved = true"></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-default-gf" style="padding:2px 6px;font-size:10px;color:#2563eb;" @click="saveCommodity(idx)" :disabled="!comm._unsaved" title="Save">
                                            <i class="fa fa-save"></i>
                                        </button>
                                        <button type="button" class="btn-default-gf" style="padding:2px 6px;font-size:10px;color:#ef4444;" @click="deleteCommodity(idx)" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    
                    <hr style="border-top:1px solid #e2e8f0;margin:12px 0;">
                    
                    <!-- Warehouse Receipt List -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                        <h4 style="font-size: 13px; font-weight: 700; margin: 0; color: #2563eb; margin-right: 4px;">Warehouse Receipt List</h4>
                        <button type="button" class="btn-default-gf" disabled><i class="fa fa-trash"></i></button>
                    </div>
                    
                    <table class="memo-table" style="margin-bottom:15px;">
                        <thead>
                            <tr><th style="text-align:center;color:#64748b;padding:10px;">No warehouse receipts linked.</th></tr>
                        </thead>
                    </table>
                    
                    <hr style="border-top:1px solid #e2e8f0;margin:12px 0;">
                    
                    <!-- Instruction & Description -->
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <h4 style="font-size: 12px; font-weight: 700; margin: 0 0 6px 0; color: #2563eb;">Instruction</h4>
                            <textarea name="instruction_text" class="form-control-gf" style="height: 75px; resize: none;" x-model="instructionText"></textarea>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6px;">
                                <h4 style="font-size: 12px; font-weight: 700; margin: 0; color: #2563eb;">Description</h4>
                                <div style="font-size: 10px; display: flex; align-items: center; gap: 4px;">
                                    <span style="color: #64748b; margin-right: 2px;">Copy:</span>
                                    <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px;" @click="copyPoToDescription()"><i class="fa fa-copy"></i> P.O.</button>
                                    <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px;" @click="copyCommoditiesToDescription()"><i class="fa fa-copy"></i> Commodity</button>
                                    <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px;" @click="copyCommodityAndHtsToDescription()"><i class="fa fa-copy"></i> Commodity & HTS</button>
                                </div>
                            </div>
                            <textarea name="description" class="form-control-gf" style="height: 75px; resize: none;" x-model="form.description"></textarea>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== WAREHOUSE LOAD MODAL ==================== -->
        <div x-show="showWarehouseLoadModal" class="modal-overlay" style="display:none;" x-cloak @click.self="closeWarehouseLoadModal()">
            <div class="modal-container" style="max-width: 1100px;">
                <div class="modal-header">
                    <span><i class="fa fa-warehouse text-blue-500"></i> Load from Warehouse</span>
                    <i class="fa fa-times cursor-pointer text-gray-500 hover:text-gray-700" @click="closeWarehouseLoadModal()"></i>
                </div>

                <div class="modal-body">
                    <!-- Search Filters -->
                    <div class="form-grid-4" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 15px;">
                        <div class="form-group-gf">
                            <label class="form-label-gf">Warehouse</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="warehouseFilters.warehouse_id">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses ?? [] as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Receipt No.</label>
                            <div class="form-input-container">
                                <input type="text" class="form-control-gf" x-model="warehouseFilters.receipt_no" placeholder="Search...">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Customer</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="warehouseFilters.customer_id">
                                    <option value="">All Customers</option>
                                    @foreach($agents ?? [] as $agent)
                                        @if($agent->is_customer)
                                            <option value="{{ $agent->id }}">{{ $agent->company_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Status</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="warehouseFilters.status">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                    <option value="linked">Linked</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 15px;">
                        <button type="button" class="btn-default-gf" @click="clearWarehouseFilters()">Clear</button>
                        <button type="button" class="btn-freightx" @click="searchWarehouseReceipts()"><i class="fa fa-search"></i> Search</button>
                    </div>

                    <!-- Warehouse Receipts Table -->
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 30px; text-align: center;"><input type="checkbox" @change="toggleAllWarehouseReceipts($event.target.checked)"></th>
                                    <th>Receipt No.</th>
                                    <th>Warehouse</th>
                                    <th>Customer</th>
                                    <th>Receive Date</th>
                                    <th style="text-align: right;">PKG</th>
                                    <th style="text-align: right;">Weight (KG)</th>
                                    <th style="text-align: right;">CBM</th>
                                    <th>Status</th>
                                    <th>Commodity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="warehouseReceipts.length === 0">
                                    <tr>
                                        <td colspan="10" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 20px;">
                                            No warehouse receipts found. Use the search filters above.
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="receipt in warehouseReceipts" :key="receipt.id">
                                    <tr style="cursor: pointer;" :class="selectedWarehouseReceipts.includes(receipt.id) ? 'bg-blue-50' : ''" @click="toggleWarehouseReceipt(receipt.id)">
                                        <td style="text-align: center;" @click.stop>
                                            <input type="checkbox" :checked="selectedWarehouseReceipts.includes(receipt.id)" @change="toggleWarehouseReceipt(receipt.id)">
                                        </td>
                                        <td x-text="receipt.receipt_no"></td>
                                        <td x-text="receipt.warehouse_name"></td>
                                        <td x-text="receipt.customer_name"></td>
                                        <td x-text="receipt.receive_date"></td>
                                        <td style="text-align: right;" x-text="receipt.pkg"></td>
                                        <td style="text-align: right;" x-text="receipt.weight"></td>
                                        <td style="text-align: right;" x-text="receipt.cbm"></td>
                                        <td>
                                            <span :style="'padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 600; background: ' + (receipt.status === 'received' ? '#d4edda' : receipt.status === 'linked' ? '#cce5ff' : '#fff3cd') + '; color: ' + (receipt.status === 'received' ? '#155724' : receipt.status === 'linked' ? '#004085' : '#856404')" x-text="receipt.status.toUpperCase()"></span>
                                        </td>
                                        <td x-text="receipt.commodity" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 10px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 10px;">
                        <strong>Selected:</strong> <span x-text="selectedWarehouseReceipts.length"></span> receipt(s)
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="closeWarehouseLoadModal()">Cancel</button>
                    <button type="button" class="btn-freightx" @click="loadSelectedWarehouseReceipts()" :disabled="selectedWarehouseReceipts.length === 0">
                        <i class="fa fa-check"></i> Load Selected (<span x-text="selectedWarehouseReceipts.length"></span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== CREATE RECEIPT MODAL ==================== -->
        <div x-show="showCreateReceiptModal" class="modal-overlay" style="display:none;" x-cloak @click.self="closeCreateReceiptModal()">
            <div class="modal-container" style="max-width: 800px;">
                <div class="modal-header">
                    <span><i class="fa fa-plus-circle text-blue-500"></i> Create Warehouse Receipt and Link</span>
                    <i class="fa fa-times cursor-pointer text-gray-500 hover:text-gray-700" @click="closeCreateReceiptModal()"></i>
                </div>

                <div class="modal-body">
                    <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="form-group-gf">
                            <label class="form-label-gf required">Warehouse</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="receiptForm.warehouse_id" required>
                                    <option value="">Select Warehouse...</option>
                                    @foreach($warehouses ?? [] as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf required">Receipt No.</label>
                            <div class="form-input-container">
                                <input type="text" class="form-control-gf" x-model="receiptForm.receipt_no" placeholder="Auto-generated" required>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Customer</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="receiptForm.customer_id">
                                    <option value="">Select Customer...</option>
                                    @foreach($agents ?? [] as $agent)
                                        @if($agent->is_customer)
                                            <option value="{{ $agent->id }}">{{ $agent->company_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf required">Receive Date</label>
                            <div class="form-input-container">
                                <input type="date" class="form-control-gf" x-model="receiptForm.receive_date" required>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">PKG</label>
                            <div class="form-input-container">
                                <input type="number" step="any" class="form-control-gf" x-model="receiptForm.pkg" style="text-align: right;">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Weight (KG)</label>
                            <div class="form-input-container">
                                <input type="number" step="any" class="form-control-gf" x-model="receiptForm.weight" style="text-align: right;">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">CBM</label>
                            <div class="form-input-container">
                                <input type="number" step="any" class="form-control-gf" x-model="receiptForm.cbm" style="text-align: right;">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Status</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="receiptForm.status">
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-gf" style="margin-top: 10px;">
                        <label class="form-label-gf">Commodity</label>
                        <div class="form-input-container" style="flex: 1;">
                            <textarea class="form-control-gf" x-model="receiptForm.commodity" rows="3" placeholder="Enter commodity description..."></textarea>
                        </div>
                    </div>

                    <div class="form-group-gf" style="margin-top: 10px;">
                        <label class="form-label-gf">Remark</label>
                        <div class="form-input-container" style="flex: 1;">
                            <textarea class="form-control-gf" x-model="receiptForm.remark" rows="2" placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>

                    <div style="margin-top: 15px; padding: 10px; background: #e8f4fd; border: 1px solid #bee5eb; border-radius: 4px; font-size: 10px;">
                        <i class="fa fa-info-circle" style="color: #0c5460;"></i> 
                        <strong>Note:</strong> This receipt will be automatically linked to this truck shipment after creation.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="closeCreateReceiptModal()">Cancel</button>
                    <button type="button" class="btn-freightx" @click="createAndLinkReceipt()">
                        <i class="fa fa-save"></i> Create and Link
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== ACCOUNTING TAB ==================== -->
        <div x-show="activeTab === 'accounting'" x-cloak>
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption caption-subject">
                        <span style="display: inline-block; width: 14px; height: 18px; background: #fff; clip-path: polygon(100% 0, 100% 66%, 50% 100%, 0 66%, 0 0); margin-right: 5px;"></span>
                        TRUCKING ACCOUNTING
                    </div>
                    <div style="display: flex; gap: 5px; position: relative;">
                        <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 8px; font-size: 10px;" @click="toolsOpen = !toolsOpen">
                            <i class="fa fa-cogs"></i> TOOLS <i class="fa fa-angle-down"></i>
                        </button>
                        <div x-show="toolsOpen" @click.away="toolsOpen = false" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; z-index: 100; min-width: 220px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 2px;">
                            <a href="#" @click.prevent="toolsOpen = false; toggleBlock()" class="dropdown-item">
                                <i class="fa fa-ban" style="width: 16px; margin-right: 8px;"></i> 
                                <span x-text="form.is_blocked ? 'UNBLOCK' : 'BLOCK'"></span>
                            </a>
                            <a href="#" @click.prevent="toolsOpen = false; generatePickupDeliveryOrder()" class="dropdown-item">
                                <i class="fa fa-truck" style="width: 16px; margin-right: 8px;"></i> PICKUP / DELIVERY ORDER
                            </a>
                            <a href="#" @click.prevent="toolsOpen = false; printBOL()" class="dropdown-item">
                                <i class="fa fa-file-pdf-o" style="width: 16px; margin-right: 8px;"></i> BOL PRINT
                            </a>
                            <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                            <a href="#" @click.prevent="toolsOpen = false; generateProfitReportSummary()" class="dropdown-item">
                                <i class="fa fa-chart-bar" style="width: 16px; margin-right: 8px;"></i> PROFIT REPORT - SUMMARY
                            </a>
                            <a href="#" @click.prevent="toolsOpen = false; generateProfitReportDetail()" class="dropdown-item">
                                <i class="fa fa-chart-line" style="width: 16px; margin-right: 8px;"></i> PROFIT REPORT - DETAIL
                            </a>
                            <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                            <a href="#" @click.prevent="toolsOpen = false; viewCargoManifestStatus()" class="dropdown-item">
                                <i class="fa fa-list-alt" style="width: 16px; margin-right: 8px;"></i> CARGO MANIFEST STATUS
                            </a>
                            <a href="#" @click.prevent="toolsOpen = false; openInTrackTrace()" class="dropdown-item">
                                <i class="fa fa-map-marker-alt" style="width: 16px; margin-right: 8px;"></i> OPEN IN TRACK-TRACE
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="portlet-body">
                    <!-- Accounting Navigation Buttons -->
                    <div style="display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap;">
                        <button type="button" @click.prevent="createInvoice('AR')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                            <i class="fa fa-plus"></i> ORIGIN REVENUE (INV)
                        </button>
                        <button type="button" @click.prevent="createInvoice('DC')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                            <i class="fa fa-plus"></i> DESTINATION REVENUE/COST (DN)
                        </button>
                        <button type="button" @click.prevent="createInvoice('AP')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                            <i class="fa fa-plus"></i> ORIGIN COST (CN)
                        </button>
                    </div>

                    <table class="memo-table" style="text-align: center; margin-bottom: 15px;">
                        <thead>
                            <tr>
                                <th style="width: 25px;"><input type="checkbox" @change="toggleAllCharges($event)" title="Select All Unbilled"></th>
                                <th style="width: 25px;"></th>
                                <th style="text-align: left;">Invoice No.</th>
                                <th style="text-align: left;">Party</th>
                                <th style="text-align: right;">Revenue</th>
                                <th style="text-align: right;">Cost</th>
                                <th style="text-align: right;">Balance</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Post Date</th>
                                <th style="text-align: right;">Invoice Date</th>
                                <th style="text-align: center;">Email</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="charges.length === 0">
                                <tr>
                                    <td colspan="12" style="text-align: center; padding: 25px; color: #999; font-style: italic;">
                                        <i class="fa fa-file-text-o" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                                        No charges added yet. Use the buttons above to create charges.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(charge, idx) in charges" :key="charge.id || idx">
                                <tr>
                                    <td style="text-align:center;">
                                        <input type="checkbox" 
                                               x-model="charge._selected" 
                                               style="margin:0; width:13px; height:13px; cursor:pointer;"
                                        >
                                    </td>
                                    <td></td>
                                    <td style="text-align:left;" x-text="charge.invoice_no || 'Draft'"></td>
                                    <td style="text-align:left;" x-text="charge.party_name || 'N/A'"></td>
                                    <td style="text-align:right;" x-text="charge.type === 'AR' ? charge.amount : '0.00'"></td>
                                    <td style="text-align:right;" x-text="charge.type === 'AP' ? charge.amount : '0.00'"></td>
                                    <td style="text-align:right;" x-text="charge.balance || charge.amount"></td>
                                    <td style="text-align:center;"><span style="font-size:9px;padding:2px 6px;border-radius:2px;background:#e8e8e8;" x-text="charge.is_invoiced ? 'Invoiced' : 'Draft'"></span></td>
                                    <td style="text-align:right;" x-text="charge.post_date || '-'"></td>
                                    <td style="text-align:right;" x-text="charge.invoice_date || '-'"></td>
                                    <td style="text-align:center;">-</td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-default-gf dark" style="padding:1px 5px;font-size:9px;" @click="editCharge(charge)"><i class="fa fa-pencil"></i></button>
                                        <button type="button" class="btn-default-gf dark" style="padding:1px 5px;font-size:9px;color:#d05454;" @click="deleteCharge(idx)"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            </template>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong style="font-size: 11px;">Total</strong></td>
                                <td style="text-align: right; color: #3b73af;"><strong style="font-size: 11px;" x-text="totals.revenue.toFixed(2)"></strong></td>
                                <td style="text-align: right; color: #3b73af;"><strong style="font-size: 11px;" x-text="totals.cost.toFixed(2)"></strong></td>
                                <td style="text-align: right; color: #3b73af;"><strong style="font-size: 11px;" x-text="totals.balance.toFixed(2)"></strong></td>
                                <td colspan="5"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong style="font-size: 11px;">Amount</strong></td>
                                <td colspan="2" style="text-align: right; color: #3b73af;"><strong style="font-size: 11px;" x-text="totals.profit.toFixed(2)"></strong></td>
                                <td colspan="6"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <table class="memo-table" style="margin-bottom: 15px;">
                        <colgroup>
                            <col style="width: 55%;">
                            <col style="width: 15%;">
                            <col style="width: 15%;">
                            <col style="width: 15%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th></th>
                                <th style="text-align: right; font-size: 11px; color: #888; font-weight: normal;">Amount</th>
                                <th style="text-align: right; font-size: 11px; color: #888; font-weight: normal;">Profit Percentage</th>
                                <th style="text-align: right; font-size: 11px; color: #888; font-weight: normal;">Profit Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: right; font-size: 11px;">Total Profit</td>
                                <td style="text-align: right; color: #3b73af; font-weight: 600;" x-text="totals.profit.toFixed(2)"></td>
                                <td style="text-align: right; color: #3b73af; font-weight: 600;" x-text="totals.profitPercentage"></td>
                                <td style="text-align: right; color: #3b73af; font-weight: 600;" x-text="totals.profitMargin"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <!-- ==================== STATUS TAB ==================== -->
        <div x-show="activeTab === 'status'" x-cloak>
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption caption-subject">
                        <span style="display: inline-block; width: 14px; height: 18px; background: #fff; clip-path: polygon(100% 0, 100% 66%, 50% 100%, 0 66%, 0 0); margin-right: 5px;"></span>
                        Status & History
                    </div>
                    <div>
                        <button type="button" class="btn-default-gf"><i class="fa fa-cogs"></i> Tools <i class="fa fa-angle-down"></i></button>
                    </div>
                </div>
                
                <div class="portlet-body">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h4 style="font-size: 13px; font-weight: 600; color: #333; margin: 0 0 10px 0;">Role</h4>
                            <div style="display: flex; flex-direction: column; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 11px; font-weight: 600; width: 40px;">OP :</span>
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #4b77be; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;" x-text="form.op_id ? (users.find(u => u.id == form.op_id)?.name?.charAt(0) || '?') : '?'"></div>
                                    <select name="op_id" class="form-control-gf" style="width: 200px; height: 26px;" x-model="form.op_id">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 11px; font-weight: 600; width: 40px;">SALES:</span>
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #4b77be; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;" x-text="form.sales_id ? (users.find(u => u.id == form.sales_id)?.name?.charAt(0) || '?') : '?'"></div>
                                    <select name="sales_id" class="form-control-gf" style="width: 200px; height: 26px;" x-model="form.sales_id">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div style="flex: 2;">
                            <h4 style="font-size: 13px; font-weight: 600; color: #333; margin: 0 0 10px 0;">Internal Message</h4>
                            <textarea name="internal_remark" class="form-control-gf" style="width: 100%; height: 65px; resize: none; border: 1px solid #ccc;" x-model="form.internal_remark"></textarea>
                        </div>
                    </div>

                    <h4 style="font-size: 13px; font-weight: 600; color: #333; margin: 0 0 15px 0;">Change Log</h4>
                    
                    <template x-if="statusLogs.length === 0">
                        <div style="text-align:center;padding:20px;color:#888;">
                            <i class="fa fa-history" style="font-size:20px;display:block;margin-bottom:5px;"></i>
                            No status history yet. Changes will be logged automatically as the shipment is updated.
                        </div>
                    </template>
                    
                    <template x-for="(log, idx) in statusLogs" :key="log.id || idx">
                        <div style="display: flex; gap: 20px;">
                            <div style="text-align: right; width: 100px;">
                                <div style="font-size: 11px; color: #888;" x-text="log.event_time || log.created_at"></div>
                            </div>
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; width: 40px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; border: 3px solid #4b77be; display: flex; align-items: center; justify-content: center; background: #fff; z-index: 2;">
                                    <div style="width: 20px; height: 20px; border-radius: 50%; background: #4b77be; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;" x-text="log.user_name ? log.user_name.charAt(0).toUpperCase() : '?'"></div>
                                </div>
                                <div style="width: 4px; background: #ddd; position: absolute; top: 30px; bottom: -20px; z-index: 1;"></div>
                            </div>
                            <div style="flex: 1; padding-bottom: 20px;">
                                <div style="position: relative; background: #f9f9f9; padding: 10px; border: 1px solid #eef1f5;">
                                    <div style="position: absolute; left: -6px; top: 10px; width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 6px solid #f9f9f9;"></div>
                                    <h4 style="font-size: 12px; font-weight: 600; color: #333; margin: 0 0 5px 0;" x-text="log.status_name || log.action"></h4>
                                    <div style="font-size: 11px; color: #888;" x-text="log.user_name || 'System'"></div>
                                    <div style="font-size: 11px; color: #666; margin-top: 5px;" x-text="log.details || ''"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
      

        <!-- Serialized containers & memos for form submission -->
        <input type="hidden" name="containers" id="containers_input" value="">
        <input type="hidden" name="memos" id="memos_input" value="">
        </form>

    <!-- Memo Add/Edit Modal -->
    <div class="modal-backdrop" x-show="memoModalOpen" style="display: none;"></div>
    <div class="modal" x-show="memoModalOpen" style="display: none;">
        <div class="modal-dialog" style="width:500px;" @click.stop>
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" x-text="memoEditIndex === -1 ? 'Add Memo' : 'Edit Memo'"></h4>
                    <button type="button" class="close-btn" @click="memoModalOpen = false">&times;</button>
                </div>
                <form @submit.prevent="saveMemo()">
                    <div class="modal-body">
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Subject</label>
                            <input type="text" class="form-control-gf" style="height:24px;font-size:11px;" x-model="memoForm.subject" required>
                        </div>
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Content</label>
                            <textarea class="form-control-gf" style="height:120px;resize:none;font-size:11px;" x-model="memoForm.content"></textarea>
                        </div>
                        <div>
                            <label style="display:flex;align-items:center;gap:5px;font-size:11px;">
                                <input type="checkbox" x-model="memoForm.has_alert"> Enable pop-up alert
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-default-gf dark" style="padding:6px 12px;font-size:12px;" @click="memoModalOpen = false">Cancel</button>
                        <button type="submit" class="btn-gf" style="margin:0;padding:6px 20px;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Charge Add/Edit Modal (Steps/Wizard) -->
    <div class="modal-backdrop" x-show="chargeModalOpen" style="display: none;"></div>
    <div class="modal" x-show="chargeModalOpen" style="display: none;">
        <div class="modal-dialog" style="width:650px;" @click.stop>
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" x-text="chargeForm.is_dc_note ? 'Create DN' : (chargeForm.type === 'AR' ? 'Create INV' : 'Create CN')"></h4>
                    <button type="button" class="close-btn" @click="chargeModalOpen = false">&times;</button>
                </div>
                <form @submit.prevent="saveCharge()">
                    <div class="modal-body">
                        <!-- Steps Indicator -->
                        <div class="step-container" style="margin-bottom: 20px;">
                            <div class="step">
                                <div class="step-id" :class="chargeStep >= 1 ? 'active' : ''"><span>1</span></div>
                                <div class="step-title">Charge Info</div>
                            </div>
                            <div class="step-divider" :class="chargeStep > 1 ? 'active' : ''"></div>
                            <div class="step">
                                <div class="step-id" :class="chargeStep >= 2 ? 'active' : ''"><span>2</span></div>
                                <div class="step-title">Pricing</div>
                            </div>
                            <div class="step-divider" :class="chargeStep > 2 ? 'active' : ''"></div>
                            <div class="step">
                                <div class="step-id" :class="chargeStep >= 3 ? 'active' : ''"><span>3</span></div>
                                <div class="step-title">Parties & Notes</div>
                            </div>
                        </div>

                        <!-- Step 1: Charge Info -->
                        <div x-show="chargeStep === 1">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Type</label>
                                    <select class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.type" :disabled="chargeForm.is_dc_note">
                                        <option value="AR">AR - Revenue</option>
                                        <option value="AP">AP - Cost</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Charge Code</label>
                                    <input type="text" class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.charge_code" placeholder="e.g. FREIGHT">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Charge Name</label>
                                    <input type="text" class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.charge_name" placeholder="Description of charge">
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Pricing -->
                        <div x-show="chargeStep === 2">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Currency</label>
                                    <select class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.currency_id">
                                        <option value="">Select...</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Qty</label>
                                    <input type="number" step="any" class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.qty">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Unit</label>
                                    <input type="text" class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.unit" placeholder="e.g. KG, CTN">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Rate</label>
                                    <input type="number" step="any" class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.rate" @input="chargeForm.amount = parseFloat(chargeForm.rate || 0) * parseFloat(chargeForm.qty || 1)">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Amount</label>
                                    <input type="number" step="any" class="form-control-gf" style="height:24px;font-size:11px;background:#eee;" x-model="chargeForm.amount" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Parties & Notes -->
                        <div x-show="chargeStep === 3">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Bill To</label>
                                    <select class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.bill_to_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Vendor</label>
                                    <select class="form-control-gf" style="height:24px;font-size:11px;" x-model="chargeForm.vendor_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;">Remark</label>
                                    <textarea class="form-control-gf" style="height:50px;resize:none;font-size:11px;" x-model="chargeForm.remark"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <button type="button" class="btn-default-gf dark" style="padding:6px 12px;font-size:12px;" @click="chargeStep > 1 ? chargeStep-- : (chargeModalOpen = false)"><i class="fa fa-chevron-left"></i> <span x-text="chargeStep === 1 ? 'Cancel' : 'Previous'"></span></button>
                        </div>
                        <div style="font-size:11px;color:#888;">
                            Step <span x-text="chargeStep"></span> of 3
                        </div>
                        <div>
                            <template x-if="chargeStep < 3">
                                <button type="button" class="btn-gf" style="margin:0;padding:6px 20px;" @click="chargeStep++">Next <i class="fa fa-chevron-right"></i></button>
                            </template>
                            <template x-if="chargeStep === 3">
                                <button type="submit" class="btn-gf" style="margin:0;padding:6px 20px;"><i class="fa fa-check"></i> Save</button>
                            </template>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        function truckCreateApp() {
            return {
                activeTab: (function() {
                    const hash = window.location.hash.replace('#', '');
                    const validTabs = ['basic', 'container', 'accounting', 'doc', 'workorder', 'status'];
                    if (validTabs.includes(hash)) return hash;
                    const stored = sessionStorage.getItem('truck_create_active_tab');
                    if (stored && validTabs.includes(stored)) return stored;
                    return 'basic';
                })(),
                saved: typeof truckShipmentSaved !== "undefined" ? truckShipmentSaved : @json(isset($truckShipment) ? true : false),
                errors: {},
                chargeStep: 1,
                toolsOpen: false,
                showMore: false,
                loadFromQuotation: false,

                switchTab(tab) {
                    const validTabs = ['basic', 'container', 'accounting', 'doc', 'workorder', 'status'];
                    if (!validTabs.includes(tab)) return;
                    this.activeTab = tab;
                    window.location.hash = tab;
                    sessionStorage.setItem('truck_create_active_tab', tab);
                },
                
                // ===== Tools Dropdown Actions =====
                copyShipmentForm() {
                    this.toolsOpen = false;
                    @if(isset($truckShipment))
                        if (typeof showToast === 'function') {
                            showToast('info', 'Copying shipment to create new form...');
                        }
                        window.location.href = '/truck/create?copy={{ $truckShipment->id }}';
                    @else
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const rand = Math.floor(1000 + Math.random() * 9000);
                        this.form.file_no = 'TK-' + year + '-' + month + '-' + rand;
                        this.saved = false;
                        if (typeof showToast === 'function') {
                            showToast('success', 'Copied current form as new shipment (' + this.form.file_no + ')');
                        }
                    @endif
                },
                deleteShipment() {
                    this.toolsOpen = false;
                    if (!this.saved) {
                        if (typeof showToast === 'function') {
                            showToast('warning', 'Cannot delete an unsaved shipment.');
                        }
                        return;
                    }
                    
                    @if(isset($truckShipment))
                    if (!confirm('Are you sure you want to delete Truck Shipment ' + (this.form.file_no || '') + '?')) {
                        return;
                    }
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (typeof showToast === 'function') {
                        showToast('info', 'Deleting shipment...');
                    }
                    
                    fetch('/truck/{{ $truckShipment->id }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success || res.ok) {
                            if (typeof showToast === 'function') {
                                showToast('success', 'Truck Shipment deleted successfully');
                            }
                            setTimeout(() => {
                                window.location.href = '/truck/my-shipment-list';
                            }, 600);
                        } else {
                            if (typeof showToast === 'function') {
                                showToast('error', data.message || 'Failed to delete shipment');
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to delete shipment');
                        }
                    });
                    @endif
                },
                
                // Warehouse Modal States
                showWarehouseLoadModal: false,
                showCreateReceiptModal: false,
                warehouseReceipts: [],
                selectedWarehouseReceipts: [],
                warehouseFilters: {
                    warehouse_id: '',
                    receipt_no: '',
                    customer_id: '',
                    status: ''
                },
                receiptForm: {
                    warehouse_id: '',
                    receipt_no: '',
                    customer_id: '',
                    receive_date: new Date().toISOString().split('T')[0],
                    pkg: 0,
                    weight: 0,
                    cbm: 0,
                    status: 'received',
                    commodity: '',
                    remark: ''
                },
                
                // ===== Users data for dynamic rendering =====
                users: @json($users),
                
                // ===== Dropdown Options (Dynamic) =====
                agents: [],           // Trade partners (customers, shippers, consignees, etc.)
                ports: [],            // Ports
                offices: [],          // Offices
                quotations: [],       // Quotations
                truckers: [],         // Truckers
                locations: [],        // Locations
                packageUnits: [],     // Package units
                containerTypes: [],   // Container types
                warehouses: [],       // Warehouses
                currencies: [],       // Currencies
                vendors: [],          // Vendors for charges
                
                // ===== Quote Modal =====
                showQuoteModal: new URLSearchParams(window.location.search).get('load_from_quotation') === 'true' || {{ isset($page) && $page === 'create-quote' ? 'true' : 'false' }},
                showQuoteConfig: false,
                quoteStep: 1,
                selectedQuote: null,
                saveAsDraftInvoice: false,
                quoteSearch: {
                    selected_id: '',
                    results: []
                },
                colVisibility: {
                    quote_no: true,
                    valid_date: true,
                    status: true,
                    commodity: true,
                    pol: true,
                    pod: true,
                    sales: true,
                    op: true
                },
                filters: {
                    customer: '',
                    pol: '',
                    quote_no: '',
                    valid_date: '',
                    pod: '',
                    status: '',
                    commodity: '',
                    sales: '',
                    op: ''
                },
                searchFilters: {
                    customer: '',
                    pol: '',
                    quote_no: '',
                    valid_date: '',
                    pod: '',
                    status: '',
                    commodity: '',
                    sales: '',
                    op: ''
                },
                get filteredQuotes() {
                    return this.quoteSearch.results.filter(q => {
                        if (this.searchFilters.quote_no && !q.quote_no.toLowerCase().includes(this.searchFilters.quote_no.toLowerCase())) return false;
                        if (this.searchFilters.customer && q.customer_id != this.searchFilters.customer) return false;
                        if (this.searchFilters.pol && q.pol_id != this.searchFilters.pol) return false;
                        if (this.searchFilters.pod && q.pod_id != this.searchFilters.pod) return false;
                        if (this.searchFilters.status && q.status?.toUpperCase() !== this.searchFilters.status.toUpperCase()) return false;
                        if (this.searchFilters.sales && q.sales_person_id != this.searchFilters.sales) return false;
                        if (this.searchFilters.op && q.op != this.searchFilters.op) return false;
                        return true;
                    });
                },
                applySearch() {
                    this.searchFilters = { ...this.filters };
                },
                clearSearch() {
                    this.filters = {
                        customer: '',
                        pol: '',
                        quote_no: '',
                        valid_date: '',
                        pod: '',
                        status: '',
                        commodity: '',
                        sales: '',
                        op: ''
                    };
                    this.searchFilters = { ...this.filters };
                },
                quoteForm: {
                    quote_no: '',
                    mbl_no: '',
                    hbl_no: '',
                    etd: '',
                    eta: '',
                    customer: '',
                    customer_id: '',
                    sales: '',
                    sales_person_id: '',
                    pol_id: '',
                    pod_id: '',
                    pol_name: '',
                    pod_name: '',
                    vessel_flight_no: '',
                    carrier_bkg_no: '',
                    shipper: '',
                    shipper_id: '',
                    consignee: '',
                    consignee_id: '',
                    trucker: '',
                    trucker_id: '',
                    op: '',
                    detail: ''
                },
                selectQuote(data) {
                    this.selectedQuote = { ...data };
                    this.quoteForm.quote_no = data.quote_no || '';
                    this.quoteForm.mbl_no = data.mbl_no || '';
                    this.quoteForm.hbl_no = data.hbl_no || '';
                    this.quoteForm.eta = data.eta || '';
                    this.quoteForm.etd = data.etd || '';
                    this.quoteForm.customer = data.customer_name || '';
                    this.quoteForm.customer_id = data.customer_id || '';
                    this.quoteForm.sales = data.sales_person_name || '';
                    this.quoteForm.sales_person_id = data.sales_person_id || '';
                    this.quoteForm.pol_id = data.pol_id || '';
                    this.quoteForm.pod_id = data.pod_id || '';
                    this.quoteForm.pol_name = data.pol_name || '';
                    this.quoteForm.pod_name = data.pod_name || '';
                    this.quoteForm.op = data.op_name || '';
                    this.quoteStep = 2;
                },
                confirmQuoteSelection() {
                    if (this.selectedQuote && this.selectedQuote.id) this.form.quotation_id = this.selectedQuote.id;
                    
                    if (this.quoteForm.mbl_no) this.form.mbl_no = this.quoteForm.mbl_no;
                    if (this.quoteForm.hbl_no) this.form.hbl_no = this.quoteForm.hbl_no;
                    if (this.quoteForm.eta) this.form.eta = this.quoteForm.eta;
                    if (this.quoteForm.etd) this.form.etd = this.quoteForm.etd;
                    if (this.quoteForm.customer_id) this.form.customer_id = this.quoteForm.customer_id;
                    if (this.quoteForm.sales_person_id) this.form.sales_id = this.quoteForm.sales_person_id;
                    if (this.quoteForm.pol_id) this.form.pol_id = this.quoteForm.pol_id;
                    if (this.quoteForm.pod_id) this.form.pod_id = this.quoteForm.pod_id;
                    if (this.quoteForm.shipper_id) this.form.shipper_id = this.quoteForm.shipper_id;
                    if (this.quoteForm.consignee_id) this.form.consignee_id = this.quoteForm.consignee_id;
                    if (this.quoteForm.trucker_id) this.form.trucker_id = this.quoteForm.trucker_id;
                    if (this.quoteForm.vessel_flight_no) this.form.vessel_flight_no = this.quoteForm.vessel_flight_no;
                    if (this.quoteForm.carrier_bkg_no) this.form.carrier_bkg_no = this.quoteForm.carrier_bkg_no;

                    if (this.selectedQuote && this.selectedQuote.commodity) {
                        this.commodities.push({
                            id: null,
                            description: this.selectedQuote.commodity,
                            hts_code: '',
                            container_idx: '',
                            container_id: null,
                            po_no: '',
                            _unsaved: true
                        });
                    }

                    if (this.selectedQuote && this.selectedQuote.items) {
                        const items = this.selectedQuote.items.filter(item => item.selected !== false);
                        items.forEach(item => {
                            this.charges.push({
                                id: null,
                                selected: false,
                                party: 'Custom',
                                party_name_id: '',
                                sal: 'Sea',
                                pr: 'Rec',
                                ppc: 'Colle',
                                chrg_code: item.charge_code,
                                charge_name: item.charge_name,
                                currency: item.currency || 'USD',
                                rate: item.rate,
                                qty: item.qty,
                                qty_type: item.unit || 'UNIT',
                                roe: 1.0,
                                vat: 0,
                                inv_no: '',
                                financial_date: new Date().toISOString().split('T')[0],
                                eq_bl_no: '',
                                remark: false,
                                mbl_no: ''
                            });
                        });
                    }
                    this.showQuoteModal = false;
                    this.activeTab = 'basic';
                    if (typeof showToast === 'function') {
                        showToast('success', 'Quotation loaded into form successfully!');
                    }
                },
                async searchQuotes() {
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.customer) params.append('customer_id', this.filters.customer);
                        if (this.filters.commodity) params.append('commodity', this.filters.commodity);
                        if (this.filters.sales) params.append('sales_person_id', this.filters.sales);
                        if (this.filters.pol) params.append('pol_id', this.filters.pol);
                        if (this.filters.pod) params.append('pod_id', this.filters.pod);
                        if (this.filters.quote_no) params.append('quote_no', this.filters.quote_no);
                        if (this.filters.status) params.append('status', this.filters.status);
                        if (this.filters.op) params.append('op', this.filters.op);
                        
                        params.append('module', 'Truck');
                        let response = await fetch(`/api/quotations?${params.toString()}`);
                        if (!response.ok) {
                            response = await fetch(`/api/dropdown-options/quotations?${params.toString()}`);
                        }
                        if (response.ok) {
                            const data = await response.json();
                            this.quoteSearch.results = data.data || data || [];
                            this.searchFilters = { ...this.filters };
                        }
                    } catch (e) {
                        console.error('Quote search failed:', e);
                        this.quoteSearch.results = [];
                    }
                },
                closeQuoteModal() {
                    this.showQuoteModal = false;
                    this.quoteStep = 1;
                },
                
                // ===== Tab State =====
                po_mapping: 'C',
                totalSource: 'container',
                
                instructionText: '',
                
                // ===== Form Data =====
                form: {
                    file_no: '{{ isset($truckShipment) ? $truckShipment->file_no : "MTR-" . date("YmdHis") }}',
                    post_date: '{{ isset($truckShipment) && $truckShipment->post_date ? \Carbon\Carbon::parse($truckShipment->post_date)->format("Y-m-d") : date("Y-m-d") }}',
                    office_id: '{{ isset($truckShipment) ? $truckShipment->office_id : (isset($copyShipment) ? $copyShipment->office_id : "") }}',
                    ship_type: '{{ isset($truckShipment) ? $truckShipment->ship_type : (isset($copyShipment) ? $copyShipment->ship_type : "Trucking") }}',
                    mbl_no: '{{ isset($truckShipment) ? $truckShipment->mbl_no : (isset($copyShipment) ? $copyShipment->mbl_no : "") }}',
                    hbl_no: '{{ isset($truckShipment) ? $truckShipment->hbl_no : (isset($copyShipment) ? $copyShipment->hbl_no : "") }}',
                    vessel_flight_no: '{{ isset($truckShipment) ? $truckShipment->vessel_flight_no : (isset($copyShipment) ? $copyShipment->vessel_flight_no : "") }}',
                    carrier_bkg_no: '{{ isset($truckShipment) ? $truckShipment->carrier_bkg_no : (isset($copyShipment) ? $copyShipment->carrier_bkg_no : "") }}',
                    quotation_id: '{{ isset($truckShipment) ? $truckShipment->quotation_id : (isset($copyShipment) ? $copyShipment->quotation_id : "") }}',
                    customer_id: '{{ isset($truckShipment) ? $truckShipment->customer_id : (isset($copyShipment) ? $copyShipment->customer_id : "") }}',
                    customer_ref_no: '{{ isset($truckShipment) ? $truckShipment->customer_ref_no : (isset($copyShipment) ? $copyShipment->customer_ref_no : "") }}',
                    shipper_id: '{{ isset($truckShipment) ? $truckShipment->shipper_id : (isset($copyShipment) ? $copyShipment->shipper_id : "") }}',
                    consignee_id: '{{ isset($truckShipment) ? $truckShipment->consignee_id : (isset($copyShipment) ? $copyShipment->consignee_id : "") }}',
                    trucker_id: '{{ isset($truckShipment) ? $truckShipment->trucker_id : (isset($copyShipment) ? $copyShipment->trucker_id : "") }}',
                    bill_to_id: '{{ isset($truckShipment) ? $truckShipment->bill_to_id : (isset($copyShipment) ? $copyShipment->bill_to_id : "") }}',
                    sales_id: '{{ isset($truckShipment) ? $truckShipment->sales_id : (isset($copyShipment) ? $copyShipment->sales_id : "") }}',
                    op_id: '{{ isset($truckShipment) ? $truckShipment->op_id : (isset($copyShipment) ? $copyShipment->op_id : "") }}',
                    
                    pol_id: '{{ isset($truckShipment) ? $truckShipment->pol_id : (isset($copyShipment) ? $copyShipment->pol_id : "") }}',
                    pod_id: '{{ isset($truckShipment) ? $truckShipment->pod_id : (isset($copyShipment) ? $copyShipment->pod_id : "") }}',
                    final_destination_id: '{{ isset($truckShipment) ? $truckShipment->final_destination_id : (isset($copyShipment) ? $copyShipment->final_destination_id : "") }}',
                    etd: '{{ isset($truckShipment) && $truckShipment->etd ? \Carbon\Carbon::parse($truckShipment->etd)->format("Y-m-d") : (isset($copyShipment) && $copyShipment->etd ? \Carbon\Carbon::parse($copyShipment->etd)->format("Y-m-d") : "") }}',
                    eta: '{{ isset($truckShipment) && $truckShipment->eta ? \Carbon\Carbon::parse($truckShipment->eta)->format("Y-m-d") : (isset($copyShipment) && $copyShipment->eta ? \Carbon\Carbon::parse($copyShipment->eta)->format("Y-m-d") : "") }}',
                    feta: '{{ isset($truckShipment) && $truckShipment->feta ? \Carbon\Carbon::parse($truckShipment->feta)->format("Y-m-d") : (isset($copyShipment) && $copyShipment->feta ? \Carbon\Carbon::parse($copyShipment->feta)->format("Y-m-d") : "") }}',
                    
                    empty_pickup_location_id: '{{ isset($truckShipment) ? $truckShipment->empty_pickup_location_id : (isset($copyShipment) ? $copyShipment->empty_pickup_location_id : "") }}',
                    freight_pickup_location_id: '{{ isset($truckShipment) ? $truckShipment->freight_pickup_location_id : (isset($copyShipment) ? $copyShipment->freight_pickup_location_id : "") }}',
                    delivery_to_location_id: '{{ isset($truckShipment) ? $truckShipment->delivery_to_location_id : (isset($copyShipment) ? $copyShipment->delivery_to_location_id : "") }}',
                    empty_return_location_id: '{{ isset($truckShipment) ? $truckShipment->empty_return_location_id : (isset($copyShipment) ? $copyShipment->empty_return_location_id : "") }}',
                    
                    pkg_qty: '{{ isset($truckShipment) ? $truckShipment->pkg_qty : (isset($copyShipment) ? $copyShipment->pkg_qty : "") }}',
                    pkg_unit_id: '{{ isset($truckShipment) ? $truckShipment->pkg_unit_id : (isset($copyShipment) ? $copyShipment->pkg_unit_id : "") }}',
                    weight_kg: '{{ isset($truckShipment) ? $truckShipment->weight_kg : (isset($copyShipment) ? $copyShipment->weight_kg : "") }}',
                    volume_cbm: '{{ isset($truckShipment) ? $truckShipment->volume_cbm : (isset($copyShipment) ? $copyShipment->volume_cbm : "") }}',
                    measure_cft: '{{ isset($truckShipment) ? $truckShipment->measure_cft : (isset($copyShipment) ? $copyShipment->measure_cft : "") }}',
                    
                    est_delivery_date: '{{ isset($truckShipment) && $truckShipment->est_delivery_date ? \Carbon\Carbon::parse($truckShipment->est_delivery_date)->format("Y-m-d") : (isset($copyShipment) && $copyShipment->est_delivery_date ? \Carbon\Carbon::parse($copyShipment->est_delivery_date)->format("Y-m-d") : "") }}',
                    is_delivered: {{ isset($truckShipment) && $truckShipment->is_delivered ? 'true' : (isset($copyShipment) && $copyShipment->is_delivered ? 'true' : 'false') }},
                    delivered_date: '{{ isset($truckShipment) && $truckShipment->delivered_date ? \Carbon\Carbon::parse($truckShipment->delivered_date)->format("Y-m-d") : (isset($copyShipment) && $copyShipment->delivered_date ? \Carbon\Carbon::parse($copyShipment->delivered_date)->format("Y-m-d") : "") }}',
                    is_ecommerce: {{ isset($truckShipment) && $truckShipment->is_ecommerce ? 'true' : (isset($copyShipment) && $copyShipment->is_ecommerce ? 'true' : 'false') }},
                    is_blocked: {{ isset($truckShipment) && $truckShipment->is_blocked ? 'true' : (isset($copyShipment) && $copyShipment->is_blocked ? 'true' : 'false') }},
                    
                    truck_no: '{{ isset($truckShipment) ? $truckShipment->truck_no : (isset($copyShipment) ? $copyShipment->truck_no : "") }}',
                    driver_name: '{{ isset($truckShipment) ? $truckShipment->driver_name : (isset($copyShipment) ? $copyShipment->driver_name : "") }}',
                    driver_phone: '{{ isset($truckShipment) ? $truckShipment->driver_phone : (isset($copyShipment) ? $copyShipment->driver_phone : "") }}',
                    internal_remark: '{{ isset($truckShipment) ? $truckShipment->internal_remark : (isset($copyShipment) ? $copyShipment->internal_remark : "") }}',
                    description: '{{ isset($truckShipment) ? $truckShipment->description : (isset($copyShipment) ? $copyShipment->description : "") }}',
                },
                
                // ===== Container Management =====
                containers: [],
                selectedContainers: [],
                poNos: [],
                newPoNo: '',
                addPoNo() {
                    if (this.newPoNo.trim()) {
                        this.poNos.push(this.newPoNo.trim());
                        this.newPoNo = '';
                    }
                },
                addContainer() {
                    this.containers.push({
                        id: null,
                        container_no: '', 
                        tp_sz: '', 
                        container_type_id: '', 
                        seal_no: '', 
                        pickup_no: '',
                        pkg: 0, 
                        weight: 0, 
                        measurement: 0,
                        lfd: '', 
                        appointment: '', 
                        pickup_date: '', 
                        empty_return_date: '',
                        pier_pass: '', 
                        po_no: '',
                        _unsaved: true
                    });
                },
                addContainers(n) {
                    for (let i = 0; i < n; i++) this.addContainer();
                },
                duplicateContainer() {
                    if (this.selectedContainers.length > 0) {
                        this.selectedContainers.forEach(idx => {
                            const original = this.containers[idx];
                            if (original) {
                                const duplicate = { ...original, id: null, _unsaved: true };
                                this.containers.push(duplicate);
                            }
                        });
                        if (typeof showToast === 'function') {
                            showToast('success', `Duplicated ${this.selectedContainers.length} container(s)`);
                        }
                    } else if (this.containers.length > 0) {
                        const original = this.containers[this.containers.length - 1];
                        const duplicate = { ...original, id: null, _unsaved: true };
                        this.containers.push(duplicate);
                        if (typeof showToast === 'function') {
                            showToast('success', 'Container duplicated');
                        }
                    }
                },
                async saveContainer(idx) {
                    const container = this.containers[idx];
                    if (!container) return;
                    
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    try {
                        const payload = { ...container };
                        delete payload._unsaved;
                        payload.truck_shipment_id = shipmentId;
                        
                        let response;
                        if (container.id) {
                            // Update existing
                            response = await fetch(`/api/truck-shipments/${shipmentId}/containers/${container.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                        } else {
                            // Create new
                            response = await fetch(`/api/truck-shipments/${shipmentId}/containers`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                        }
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.container) {
                                Object.assign(container, data.container);
                                container._unsaved = false;
                            }
                            if (typeof showToast === 'function') {
                                showToast('success', 'Container saved successfully');
                            }
                        } else {
                            const error = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('error', error.message || 'Failed to save container');
                            }
                        }
                    } catch (e) {
                        console.error('Container save failed:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to save container');
                        }
                    }
                    @else
                    // Mark as saved locally
                    container._unsaved = false;
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first, then save containers');
                    }
                    @endif
                },
                async deleteContainer(idx) {
                    if (!confirm('Delete this container?')) return;
                    
                    const container = this.containers[idx];
                    
                    @if(isset($truckShipment))
                    if (container.id) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const shipmentId = {{ $truckShipment->id }};
                        
                        try {
                            const response = await fetch(`/api/truck-shipments/${shipmentId}/containers/${container.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (response.ok) {
                                this.containers.splice(idx, 1);
                                if (typeof showToast === 'function') {
                                    showToast('success', 'Container deleted successfully');
                                }
                            } else {
                                if (typeof showToast === 'function') {
                                    showToast('error', 'Failed to delete container');
                                }
                            }
                        } catch (e) {
                            console.error('Container delete failed:', e);
                            if (typeof showToast === 'function') {
                                showToast('error', 'Failed to delete container');
                            }
                        }
                    } else {
                        this.containers.splice(idx, 1);
                    }
                    @else
                    this.containers.splice(idx, 1);
                    @endif
                },
                deleteSelectedContainers() {
                    if (this.selectedContainers.length === 0) return;
                    if (!confirm(`Delete ${this.selectedContainers.length} selected container(s)?`)) return;
                    
                    const sorted = [...this.selectedContainers].sort((a,b) => b - a);
                    sorted.forEach(idx => { 
                        this.deleteContainer(idx);
                    });
                    this.selectedContainers = [];
                },
                async saveAllContainers() {
                    @if(isset($truckShipment))
                    let savedCount = 0;
                    let errorCount = 0;
                    
                    for (let i = 0; i < this.containers.length; i++) {
                        if (this.containers[i]._unsaved) {
                            try {
                                await this.saveContainer(i);
                                savedCount++;
                            } catch (e) {
                                errorCount++;
                            }
                        }
                    }
                    
                    if (savedCount > 0) {
                        if (typeof showToast === 'function') {
                            showToast('success', `Saved ${savedCount} container(s)`);
                        }
                    }
                    if (errorCount > 0) {
                        if (typeof showToast === 'function') {
                            showToast('error', `Failed to save ${errorCount} container(s)`);
                        }
                    }
                    @else
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first');
                    }
                    @endif
                },
                toggleAllContainers(checked) {
                    if (checked) {
                        this.selectedContainers = this.containers.map((_, idx) => idx);
                    } else {
                        this.selectedContainers = [];
                    }
                },
                get containerTotals() {
                    return {
                        pkg: this.containers.reduce((s, c) => s + (parseFloat(c.pkg) || 0), 0),
                        weight: this.containers.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0),
                        measurement: this.containers.reduce((s, c) => s + (parseFloat(c.measurement) || 0), 0)
                    };
                },
                manualTotal: { pkg: 0, weight: 0, measurement: 0 },
                
                // ===== Commodity Management =====
                commodities: [],
                selectedCommodities: [],
                addCommodity() {
                    this.commodities.push({ 
                        id: null,
                        description: '', 
                        hts_code: '', 
                        container_idx: '', 
                        po_no: '',
                        _unsaved: true
                    });
                },
                async saveCommodity(idx) {
                    const commodity = this.commodities[idx];
                    if (!commodity) return;
                    
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    try {
                        const payload = { ...commodity };
                        delete payload._unsaved;
                        payload.truck_shipment_id = shipmentId;
                        
                        // Map container_idx to container_id
                        if (payload.container_idx !== '' && this.containers[payload.container_idx]) {
                            payload.container_id = this.containers[payload.container_idx].id;
                        }
                        delete payload.container_idx;
                        
                        let response;
                        if (commodity.id) {
                            // Update existing
                            response = await fetch(`/api/truck-shipments/${shipmentId}/commodities/${commodity.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                        } else {
                            // Create new
                            response = await fetch(`/api/truck-shipments/${shipmentId}/commodities`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                        }
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.commodity) {
                                Object.assign(commodity, data.commodity);
                                commodity._unsaved = false;
                            }
                            if (typeof showToast === 'function') {
                                showToast('success', 'Commodity saved successfully');
                            }
                        } else {
                            const error = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('error', error.message || 'Failed to save commodity');
                            }
                        }
                    } catch (e) {
                        console.error('Commodity save failed:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to save commodity');
                        }
                    }
                    @else
                    // Mark as saved locally
                    commodity._unsaved = false;
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first, then save commodities');
                    }
                    @endif
                },
                async deleteCommodity(idx) {
                    if (!confirm('Delete this commodity?')) return;
                    
                    const commodity = this.commodities[idx];
                    
                    @if(isset($truckShipment))
                    if (commodity.id) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const shipmentId = {{ $truckShipment->id }};
                        
                        try {
                            const response = await fetch(`/api/truck-shipments/${shipmentId}/commodities/${commodity.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (response.ok) {
                                this.commodities.splice(idx, 1);
                                if (typeof showToast === 'function') {
                                    showToast('success', 'Commodity deleted successfully');
                                }
                            } else {
                                if (typeof showToast === 'function') {
                                    showToast('error', 'Failed to delete commodity');
                                }
                            }
                        } catch (e) {
                            console.error('Commodity delete failed:', e);
                            if (typeof showToast === 'function') {
                                showToast('error', 'Failed to delete commodity');
                            }
                        }
                    } else {
                        this.commodities.splice(idx, 1);
                    }
                    @else
                    this.commodities.splice(idx, 1);
                    @endif
                },
                deleteSelectedCommodities() {
                    if (this.selectedCommodities.length === 0) return;
                    if (!confirm(`Delete ${this.selectedCommodities.length} selected commodity(s)?`)) return;
                    
                    const sorted = [...this.selectedCommodities].sort((a,b) => b - a);
                    sorted.forEach(idx => { 
                        this.deleteCommodity(idx);
                    });
                    this.selectedCommodities = [];
                },
                async saveAllCommodities() {
                    @if(isset($truckShipment))
                    let savedCount = 0;
                    let errorCount = 0;
                    
                    for (let i = 0; i < this.commodities.length; i++) {
                        if (this.commodities[i]._unsaved) {
                            try {
                                await this.saveCommodity(i);
                                savedCount++;
                            } catch (e) {
                                errorCount++;
                            }
                        }
                    }
                    
                    if (savedCount > 0) {
                        if (typeof showToast === 'function') {
                            showToast('success', `Saved ${savedCount} commodity(s)`);
                        }
                    }
                    if (errorCount > 0) {
                        if (typeof showToast === 'function') {
                            showToast('error', `Failed to save ${errorCount} commodity(s)`);
                        }
                    }
                    @else
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first');
                    }
                    @endif
                },
                toggleAllCommodities(checked) {
                    if (checked) {
                        this.selectedCommodities = this.commodities.map((_, idx) => idx);
                    } else {
                        this.selectedCommodities = [];
                    }
                },
                copyCommoditiesToDescription() {
                    this.form.description = this.commodities.map(c => c.description).filter(Boolean).join(', ');
                },
                createPierPassAP() {
                    this.charges.push({
                        id: null,
                        type: 'AP',
                        charge_code: 'PIERPASS',
                        charge_name: 'Pier Pass Charge',
                        amount: 50.00,
                        rate: 50.00,
                        qty: 1,
                        is_invoiced: false,
                        _selected: false,
                        party_name: 'Pier Pass Authority'
                    });
                    if (typeof showToast === 'function') {
                        showToast('success', 'Created Pier Pass A/P charge item in Accounting tab');
                    }
                },
                copyPoToDescription() {
                    const pos = [];
                    this.containers.forEach(c => { if(c.po_no) pos.push(c.po_no); });
                    this.commodities.forEach(c => { if(c.po_no) pos.push(c.po_no); });
                    if (this.poList && Array.isArray(this.poList)) {
                        this.poList.forEach(p => { if(p) pos.push(p); });
                    }
                    const uniquePos = [...new Set(pos)].filter(Boolean);
                    if (uniquePos.length > 0) {
                        this.form.description = (this.form.description ? this.form.description + '\nP.O. No: ' : 'P.O. No: ') + uniquePos.join(', ');
                        if (typeof showToast === 'function') {
                            showToast('success', 'P.O. numbers copied to Description field');
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('info', 'No P.O. numbers to copy');
                        }
                    }
                },
                copyCommodityAndHtsToDescription() {
                    const items = this.commodities.map(c => {
                        if (c.description && c.hts_code) return `${c.description} (HTS: ${c.hts_code})`;
                        return c.description || c.hts_code || '';
                    }).filter(Boolean);
                    if (items.length > 0) {
                        this.form.description = items.join(', ');
                        if (typeof showToast === 'function') {
                            showToast('success', 'Commodity & HTS copied to Description field');
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('info', 'No commodity/HTS data to copy');
                        }
                    }
                },
                
                // ===== Warehouse Load Modal Methods =====
                openWarehouseLoadModal() {
                    this.showWarehouseLoadModal = true;
                    this.searchWarehouseReceipts();
                },
                closeWarehouseLoadModal() {
                    this.showWarehouseLoadModal = false;
                    this.selectedWarehouseReceipts = [];
                },
                clearWarehouseFilters() {
                    this.warehouseFilters = {
                        warehouse_id: '',
                        receipt_no: '',
                        customer_id: '',
                        status: ''
                    };
                },
                async searchWarehouseReceipts() {
                    try {
                        const params = new URLSearchParams();
                        if (this.warehouseFilters.warehouse_id) params.append('warehouse_id', this.warehouseFilters.warehouse_id);
                        if (this.warehouseFilters.receipt_no) params.append('receipt_no', this.warehouseFilters.receipt_no);
                        if (this.warehouseFilters.customer_id) params.append('customer_id', this.warehouseFilters.customer_id);
                        if (this.warehouseFilters.status) params.append('status', this.warehouseFilters.status);
                        
                        // Only show unlinked or available receipts
                        params.append('available', 'true');
                        
                        const response = await fetch(`/api/warehouse-receipts?${params.toString()}`);
                        if (response.ok) {
                            const data = await response.json();
                            this.warehouseReceipts = Array.isArray(data) ? data : (data.data || []);
                        } else {
                            this.warehouseReceipts = [];
                        }
                    } catch (e) {
                        console.error('Failed to search warehouse receipts:', e);
                        this.warehouseReceipts = [];
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to load warehouse receipts');
                        }
                    }
                },
                toggleWarehouseReceipt(id) {
                    const index = this.selectedWarehouseReceipts.indexOf(id);
                    if (index >= 0) {
                        this.selectedWarehouseReceipts.splice(index, 1);
                    } else {
                        this.selectedWarehouseReceipts.push(id);
                    }
                },
                toggleAllWarehouseReceipts(checked) {
                    if (checked) {
                        this.selectedWarehouseReceipts = this.warehouseReceipts.map(r => r.id);
                    } else {
                        this.selectedWarehouseReceipts = [];
                    }
                },
                async loadSelectedWarehouseReceipts() {
                    if (this.selectedWarehouseReceipts.length === 0) {
                        if (typeof showToast === 'function') {
                            showToast('warning', 'Please select at least one receipt');
                        }
                        return;
                    }
                    
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    try {
                        const response = await fetch(`/api/truck-shipments/${shipmentId}/link-warehouse-receipts`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                receipt_ids: this.selectedWarehouseReceipts
                            })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('success', `Linked ${this.selectedWarehouseReceipts.length} warehouse receipt(s) successfully`);
                            }
                            
                            // Update totals if provided
                            if (data.totals) {
                                this.manualTotal.pkg = data.totals.pkg || 0;
                                this.manualTotal.weight = data.totals.weight || 0;
                                this.manualTotal.measurement = data.totals.cbm || 0;
                                this.totalSource = 'receiving';
                            }
                            
                            // Close modal
                            this.closeWarehouseLoadModal();
                            
                            // Reload page to show linked receipts
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            const error = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('error', error.message || 'Failed to link warehouse receipts');
                            }
                        }
                    } catch (e) {
                        console.error('Failed to link warehouse receipts:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to link warehouse receipts');
                        }
                    }
                    @else
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first');
                    }
                    @endif
                },
                
                // ===== Create Receipt Modal Methods =====
                openCreateReceiptModal() {
                    this.showCreateReceiptModal = true;
                    // Reset form
                    this.receiptForm = {
                        warehouse_id: '',
                        receipt_no: '',
                        customer_id: this.form.customer_id || '',
                        receive_date: new Date().toISOString().split('T')[0],
                        pkg: 0,
                        weight: 0,
                        cbm: 0,
                        status: 'received',
                        commodity: '',
                        remark: ''
                    };
                },
                closeCreateReceiptModal() {
                    this.showCreateReceiptModal = false;
                },
                async createAndLinkReceipt() {
                    // Validate required fields
                    if (!this.receiptForm.warehouse_id) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Please select a warehouse');
                        }
                        return;
                    }
                    if (!this.receiptForm.receive_date) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Please select receive date');
                        }
                        return;
                    }
                    
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    try {
                        const response = await fetch(`/api/truck-shipments/${shipmentId}/create-and-link-receipt`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.receiptForm)
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('success', 'Warehouse receipt created and linked successfully');
                            }
                            
                            // Update totals if provided
                            if (data.totals) {
                                this.manualTotal.pkg = data.totals.pkg || 0;
                                this.manualTotal.weight = data.totals.weight || 0;
                                this.manualTotal.measurement = data.totals.cbm || 0;
                                this.totalSource = 'receiving';
                            }
                            
                            // Close modal
                            this.closeCreateReceiptModal();
                            
                            // Reload page to show linked receipt
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            const error = await response.json();
                            if (typeof showToast === 'function') {
                                showToast('error', error.message || 'Failed to create warehouse receipt');
                            }
                        }
                    } catch (e) {
                        console.error('Failed to create warehouse receipt:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to create warehouse receipt');
                        }
                    }
                    @else
                    if (typeof showToast === 'function') {
                        showToast('warning', 'Please save the shipment first');
                    }
                    @endif
                },
                
                // ===== Accounting / Charges =====
                charges: [],
                get totals() {
                    const revenue = this.charges.filter(c => c.type === 'AR').reduce((s, c) => s + (parseFloat(c.amount) || 0), 0);
                    const cost = this.charges.filter(c => c.type === 'AP').reduce((s, c) => s + (parseFloat(c.amount) || 0), 0);
                    const balance = revenue - cost;
                    const profit = revenue - cost;
                    const profitPercentage = revenue > 0 ? ((profit / revenue) * 100).toFixed(2) + '%' : 'N/A';
                    const profitMargin = revenue > 0 ? ((profit / revenue) * 100).toFixed(2) + '%' : 'N/A';
                    return { revenue, cost, balance, profit, profitPercentage, profitMargin };
                },
                

                
                // ===== Status Logs =====
                statusLogs: [],
                
                // ===== Memo Management =====
                memos: [],
                selectedMemo: null,
                selectedMemoContent: '',
                memoModalOpen: false,
                memoEditIndex: -1,
                memoForm: { subject: '', content: '', has_alert: false },
                
                
                // ===== Charge Management =====
                chargeModalOpen: false,
                chargeEditIndex: -1,
                chargeForm: {
                    type: 'AR',
                    charge_code: '',
                    charge_name: '',
                    bill_to_id: '',
                    vendor_id: '',
                    currency_id: '',
                    qty: 1,
                    unit: '',
                    rate: 0,
                    amount: 0,
                    remark: '',
                    is_dc_note: false,
                },
                openChargeModal(type) {
                    this.chargeEditIndex = -1;
                    this.chargeForm = { type: type, charge_code: '', charge_name: '', bill_to_id: '', vendor_id: '', currency_id: '', qty: 1, unit: '', rate: 0, amount: 0, remark: '', is_dc_note: false };
                    this.chargeModalOpen = true;
                },
                openDCNoteModal() {
                    this.chargeEditIndex = -1;
                    this.chargeForm = { type: 'AR', charge_code: 'DC_NOTE', charge_name: 'Debit/Credit Note', bill_to_id: '', vendor_id: '', currency_id: '', qty: 1, unit: '', rate: 0, amount: 0, remark: '', is_dc_note: true };
                    this.chargeModalOpen = true;
                },
                editCharge(charge) {
                    this.chargeEditIndex = this.charges.indexOf(charge);
                    this.chargeForm = {
                        type: charge.type || 'AR',
                        charge_code: charge.charge_code || '',
                        charge_name: charge.charge_name || '',
                        bill_to_id: charge.bill_to_id || '',
                        vendor_id: charge.vendor_id || '',
                        currency_id: charge.currency_id || '',
                        qty: charge.qty || 1,
                        unit: charge.unit || '',
                        rate: charge.rate || 0,
                        amount: charge.amount || 0,
                        remark: charge.remark || '',
                        is_dc_note: false,
                    };
                    this.chargeModalOpen = true;
                },
                async saveCharge() {
                    if (!this.chargeForm.charge_name && !this.chargeForm.amount) return;
                    
                    @if(isset($truckShipment))
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const payload = { ...this.chargeForm };
                        delete payload.is_dc_note;
                        
                        if (this.chargeEditIndex >= 0) {
                            // Update existing
                            const charge = this.charges[this.chargeEditIndex];
                            const response = await fetch(`/api/truck-shipments/{{ $truckShipment->id }}/charges/${charge.id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            if (response.ok) {
                                const data = await response.json();
                                if (data.charge) Object.assign(charge, data.charge);
                            }
                        } else {
                            // Create new
                            const response = await fetch('/api/truck-shipments/{{ $truckShipment->id }}/charges', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            if (response.ok) {
                                const data = await response.json();
                                if (data.charge) this.charges.push(data.charge);
                            }
                        }
                    } catch (e) {
                        console.error('Charge save failed:', e);
                    }
                    @else
                    // Offline: push to local array
                    const chargeData = { ...this.chargeForm };
                    delete chargeData.is_dc_note;
                    chargeData.id = Date.now();
                    chargeData.is_invoiced = false;
                    chargeData._selected = false;
                    if (this.chargeEditIndex >= 0) {
                        Object.assign(this.charges[this.chargeEditIndex], chargeData);
                    } else {
                        this.charges.push(chargeData);
                    }
                    @endif
                    
                    this.chargeModalOpen = false;
                },
                deleteCharge(idx) {
                    const charge = this.charges[idx];
                    if (!charge) return;
                    if (!confirm('Delete this charge?')) return;
                    
                    @if(isset($truckShipment))
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    fetch(`/api/truck-shipments/{{ $truckShipment->id }}/charges/${charge.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' }
                    }).then(r => {
                        if (r.ok) this.charges.splice(idx, 1);
                    }).catch(e => console.error('Delete failed:', e));
                    @else
                    this.charges.splice(idx, 1);
                    @endif
                },
                toggleAllCharges(e) {
                    const checked = e.target.checked;
                    this.charges.forEach(c => {
                        c._selected = checked;
                    });
                },
                async createInvoiceFromCharges(type) {
                    @if(isset($truckShipment))
                    const selectedCharges = this.charges.filter(c => c._selected && (c.type === type || !c.type));
                    
                    if (selectedCharges.length === 0) {
                        if (typeof showToast === 'function') showToast('error', `Please select at least one ${type} charge.`);
                        else alert(`Please select at least one ${type} charge.`);
                        return;
                    }

                    const alreadyInvoiced = selectedCharges.filter(c => c.is_invoiced || (c.invoice_no && c.invoice_no !== 'Draft'));
                    if (alreadyInvoiced.length > 0) {
                        const invNos = alreadyInvoiced.map(c => c.invoice_no || 'Already Invoiced').filter((v, i, a) => a.indexOf(v) === i).join(', ');
                        if (typeof showToast === 'function') showToast('warning', `Invoice already created for selected charge(s): ${invNos}`);
                        else alert(`Invoice already created for selected charge(s): ${invNos}`);
                    }

                    const uninvoicedCharges = selectedCharges.filter(c => !c.is_invoiced && (!c.invoice_no || c.invoice_no === 'Draft'));
                    if (uninvoicedCharges.length === 0) {
                        return;
                    }

                    try {
                        const chargeIds = uninvoicedCharges.map(c => c.id).filter(id => id); // Only take saved charges
                        if (chargeIds.length !== uninvoicedCharges.length) {
                            if (typeof showToast === 'function') showToast('error', 'Some selected charges are not saved. Please save the shipment first.');
                            else alert('Some selected charges are not saved. Please save the shipment first.');
                            return;
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        // Add idempotency key
                        const idempotencyKey = 'inv_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
                        
                        // We use the new logic: pass charge_ids and type to the backend
                        const response = await fetch('/api/truck-shipments/{{ $truckShipment->id }}/create-invoice', {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': csrfToken || '', 
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                type: type,
                                charge_ids: chargeIds,
                                idempotency_key: idempotencyKey
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            if (typeof showToast === 'function') showToast('success', data.message || 'Invoice created successfully.');
                            
                            // Open invoice in new tab
                            if (data.redirect) {
                                window.open(data.redirect, '_blank');
                            }
                            
                            // Mark local charges as invoiced
                            selectedCharges.forEach(c => {
                                c.is_invoiced = true;
                                c._selected = false;
                                if (data.invoice) {
                                    c.invoice_no = data.invoice.invoice_no;
                                    c.invoice_date = data.invoice.invoice_date;
                                }
                            });
                        } else {
                            alert(data.message || 'Failed to create invoice.');
                        }
                    } catch (e) {
                        console.error('Invoice creation failed:', e);
                        alert('An error occurred while creating the invoice.');
                    }
                    @else
                    alert('Please save the shipment first before creating an invoice.');
                    @endif
                },
                
                // ===== Accounting Navigation Methods =====
                createInvoice(type) {
                    this.createInvoiceFromCharges(type);
                },
                
                // ===== Tools Dropdown Methods =====
                toggleBlock() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    const currentStatus = this.form.is_blocked || false;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    fetch(`/truck/${shipmentId}/toggle-block`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_blocked: !currentStatus })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.form.is_blocked = data.is_blocked;
                            if (typeof showToast === 'function') {
                                showToast('success', data.is_blocked ? 'Shipment blocked successfully' : 'Shipment unblocked successfully');
                            } else {
                                alert(data.is_blocked ? 'Shipment blocked' : 'Shipment unblocked');
                            }
                        }
                    })
                    .catch(e => {
                        console.error('Toggle block failed:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to update block status');
                        }
                    });
                    @else
                    alert('Please save the shipment first');
                    @endif
                },
                
                generatePickupDeliveryOrder() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    window.open(`/truck/${shipmentId}/pickup-delivery-order`, '_blank');
                    if (typeof showToast === 'function') {
                        showToast('info', 'Opening Pickup/Delivery Order...');
                    }
                    @else
                    alert('Please save the shipment first before generating documents');
                    @endif
                },
                
                printBOL() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    window.open(`/truck/${shipmentId}/bol-print`, '_blank');
                    if (typeof showToast === 'function') {
                        showToast('info', 'Opening BOL Print...');
                    }
                    @else
                    alert('Please save the shipment first before printing documents');
                    @endif
                },
                
                generateProfitReportSummary() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    window.open(`/truck/${shipmentId}/profit-report-summary`, '_blank');
                    if (typeof showToast === 'function') {
                        showToast('info', 'Generating Profit Report - Summary...');
                    }
                    @else
                    alert('Please save the shipment first before generating reports');
                    @endif
                },
                
                generateProfitReportDetail() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    window.open(`/truck/${shipmentId}/profit-report-detail`, '_blank');
                    if (typeof showToast === 'function') {
                        showToast('info', 'Generating Profit Report - Detail...');
                    }
                    @else
                    alert('Please save the shipment first before generating reports');
                    @endif
                },
                
                viewCargoManifestStatus() {
                    @if(isset($truckShipment))
                    const shipmentId = {{ $truckShipment->id }};
                    window.open(`/truck/${shipmentId}/cargo-manifest-status`, '_blank');
                    if (typeof showToast === 'function') {
                        showToast('info', 'Opening Cargo Manifest Status...');
                    }
                    @else
                    alert('Please save the shipment first');
                    @endif
                },
                
                openInTrackTrace() {
                    window.openTrackTrace({ type: 'container', number: '' });
                },
                
                openMemoModal() {
                    this.memoEditIndex = -1;
                    this.memoForm = { subject: '', content: '', has_alert: false };
                    this.memoModalOpen = true;
                },
                editMemo(memo) {
                    this.memoEditIndex = this.memos.indexOf(memo);
                    this.memoForm = { 
                        subject: memo.subject, 
                        content: memo.content || '', 
                        has_alert: memo.has_alert || false 
                    };
                    this.memoModalOpen = true;
                },
                viewMemo(memo) {
                    this.selectedMemo = memo;
                    this.selectedMemoContent = memo ? (memo.content || '') : '';
                },
                onMemoContentInput() {
                    if (this.selectedMemo) {
                        this.selectedMemo.content = this.selectedMemoContent;
                    }
                },
                async toggleMemoAlert(memo) {
                    @if(isset($truckShipment))
                    if (memo && memo.id) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        try {
                            await fetch(`/api/truck-shipments/{{ $truckShipment->id }}/memos/${memo.id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' },
                                body: JSON.stringify({ subject: memo.subject, content: memo.content || '', has_alert: memo.has_alert })
                            });
                        } catch (e) { console.error('Failed to update alert state', e); }
                    }
                    @endif
                },
                async saveMemo() {
                    if (!this.memoForm.subject.trim()) {
                        if (typeof showToast === 'function') showToast('warning', 'Please enter a subject for the memo');
                        return;
                    }
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    
                    @if(isset($truckShipment))
                    try {
                        if (this.memoEditIndex === -1) {
                            // Create new memo
                            const response = await fetch('/api/truck-shipments/{{ $truckShipment->id }}/memos', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' },
                                body: JSON.stringify(this.memoForm)
                            });
                            if (response.ok) {
                                const data = await response.json();
                                const created = data.data || data;
                                this.memos.push(created);
                                this.viewMemo(created);
                                if (typeof showToast === 'function') showToast('success', 'Memo added successfully');
                            } else {
                                if (typeof showToast === 'function') showToast('error', 'Failed to save memo');
                            }
                        } else {
                            // Update existing memo
                            const memo = this.memos[this.memoEditIndex];
                            const response = await fetch(`/api/truck-shipments/{{ $truckShipment->id }}/memos/${memo.id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' },
                                body: JSON.stringify(this.memoForm)
                            });
                            if (response.ok) {
                                Object.assign(memo, this.memoForm);
                                this.viewMemo(memo);
                                if (typeof showToast === 'function') showToast('success', 'Memo updated successfully');
                            } else {
                                if (typeof showToast === 'function') showToast('error', 'Failed to update memo');
                            }
                        }
                        this.memoModalOpen = false;
                    } catch (e) {
                        console.error('Memo save failed:', e);
                        if (typeof showToast === 'function') showToast('error', 'An error occurred while saving memo');
                    }
                    @else
                    // Local-only mode before save
                    if (this.memoEditIndex === -1) {
                        const newMemo = {
                            id: Date.now(),
                            subject: this.memoForm.subject,
                            content: this.memoForm.content,
                            has_alert: this.memoForm.has_alert,
                            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                            updated_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
                        };
                        this.memos.push(newMemo);
                        this.viewMemo(newMemo);
                    } else {
                        Object.assign(this.memos[this.memoEditIndex], this.memoForm);
                        this.viewMemo(this.memos[this.memoEditIndex]);
                    }
                    this.memoModalOpen = false;
                    if (typeof showToast === 'function') showToast('success', 'Memo saved');
                    @endif
                },
                deleteMemo(idx) {
                    if (!confirm('Are you sure you want to delete this memo?')) return;
                    
                    const memo = this.memos[idx];
                    @if(isset($truckShipment))
                    if (memo && memo.id) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        fetch(`/api/truck-shipments/{{ $truckShipment->id }}/memos/${memo.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken || '', 'Accept': 'application/json' }
                        }).then(() => {
                            if (typeof showToast === 'function') showToast('success', 'Memo deleted successfully');
                        }).catch(e => console.error('Delete failed:', e));
                    }
                    @endif
                    this.memos.splice(idx, 1);
                    if (this.selectedMemo === memo) {
                        this.selectedMemo = null;
                        this.selectedMemoContent = '';
                    }
                },
                
                init() {
                    if (this.showQuoteModal) {
                        this.searchQuotes();
                    }
                    
                    // Load dropdown options first
                    this.loadDropdownOptions();
                    
                    // Serialize containers & memos on form submit
                    this.$nextTick(() => {
                        const form = document.getElementById('truckShipmentForm');
                        if (form) {
                            form.addEventListener('submit', (e) => {
                                const cInput = document.getElementById('containers_input');
                                const mInput = document.getElementById('memos_input');
                                if (cInput) cInput.value = JSON.stringify(this.containers);
                                if (mInput) mInput.value = JSON.stringify(this.memos);
                            });
                        }
                    });
                    
                    // Load initial data
                    @if(isset($truckShipment))
                        this.memos = @json($truckShipment->memos ?? []);
                        this.statusLogs = @json($truckShipment->statusLogs ?? []);
                        this.instructionText = @json($truckShipment->instruction_text ?? '');
                        
                        // Load containers from database
                        this.loadContainers();
                        
                        // Load commodities from database
                        this.loadCommodities();
                        
                        // Load charges
                        this.charges = @json($truckShipment->charges ?? []);
                        

                    @elseif(isset($copyShipment))
                        this.memos = @json($copyShipment->memos ?? []);
                        this.containers = @json($copyShipment->containers ?? []);
                        this.commodities = @json($copyShipment->commodities ?? []);
                        this.charges = @json($copyShipment->charges ?? []);
                        
                        // Mark all as unsaved for copy mode
                        this.containers.forEach(c => { c.id = null; c._unsaved = true; });
                        this.commodities.forEach(c => { c.id = null; c._unsaved = true; });
                    @endif
                },
                
                // ===== Load Dropdown Options =====
                async loadDropdownOptions() {
                    try {
                        const [agents, ports, offices, quotations, truckers, locations, packageUnits, containerTypes, warehouses, currencies, vendors] = await Promise.all([
                            fetch('/api/dropdown-options/agents').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/ports').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/offices').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/quotations?module=Truck').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/truckers').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/locations').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/package-units').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/container-types').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/warehouses').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/currencies').then(r => r.json()).catch(() => ({ data: [] })),
                            fetch('/api/dropdown-options/vendors').then(r => r.json()).catch(() => ({ data: [] }))
                        ]);
                        
                        this.agents = agents.data || agents || [];
                        this.ports = ports.data || ports || [];
                        this.offices = offices.data || offices || [];
                        this.quotations = quotations.data || quotations || [];
                        this.quoteSearch.results = this.quotations;
                        this.truckers = truckers.data || truckers || [];
                        this.locations = locations.data || locations || [];
                        this.packageUnits = packageUnits.data || packageUnits || [];
                        this.containerTypes = containerTypes.data || containerTypes || [];
                        this.warehouses = warehouses.data || warehouses || [];
                        this.currencies = currencies.data || currencies || [];
                        this.vendors = vendors.data || vendors || [];
                    } catch (e) {
                        console.error('Failed to load dropdown options:', e);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Failed to load dropdown options');
                        }
                    }
                },
                
                async loadContainers() {
                    @if(isset($truckShipment))
                    try {
                        const response = await fetch('/api/truck-shipments/{{ $truckShipment->id }}/containers');
                        if (response.ok) {
                            const data = await response.json();
                            this.containers = Array.isArray(data) ? data : (data.data || []);
                            // Mark all as saved
                            this.containers.forEach(c => { c._unsaved = false; });
                        }
                    } catch (e) {
                        console.error('Failed to load containers:', e);
                    }
                    @endif
                },
                
                async loadCommodities() {
                    @if(isset($truckShipment))
                    try {
                        const response = await fetch('/api/truck-shipments/{{ $truckShipment->id }}/commodities');
                        if (response.ok) {
                            const data = await response.json();
                            this.commodities = Array.isArray(data) ? data : (data.data || []);
                            // Mark all as saved
                            this.commodities.forEach(c => { c._unsaved = false; });
                            
                            // Map container_id to container_idx for display
                            this.commodities.forEach(comm => {
                                if (comm.container_id) {
                                    const containerIdx = this.containers.findIndex(c => c.id === comm.container_id);
                                    comm.container_idx = containerIdx >= 0 ? containerIdx : '';
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Failed to load commodities:', e);
                    }
                    @endif
                },

                validateAndSubmit() {
                    this.errors = {};
                    let hasError = false;
                    const required = [
                        { name: "file_no", label: "File No." },
                        { name: "post_date", label: "Post Date" },
                        { name: "office_id", label: "Office" }
                    ];
                    for (let field of required) {
                        const el = document.querySelector(`[name="${field.name}"]`);
                        if (!el || !el.value.trim()) {
                            this.errors[field.name] = `${field.label} is required`;
                            hasError = true;
                        }
                    }
                    if (hasError) {
                        const firstError = Object.keys(this.errors)[0];
                        document.querySelector(`[name="${firstError}"]`)?.focus();
                        return;
                    }
                    // Warn if significant numeric fields are zero (user may have forgotten)
                    const importantNumeric = ['pkg_qty'];
                    importantNumeric.forEach(key => {
                        if (this.form[key] === 0 || this.form[key] === '0') {
                            // Silently set to 0 - user can enter value on edit
                        }
                    });
                    // Ensure numeric fields default to 0
                    ['pkg_qty', 'weight_kg', 'volume_cbm', 'measure_cft'].forEach(key => {
                        if (this.form[key] === null || this.form[key] === '' || this.form[key] === undefined) {
                            this.form[key] = 0;
                        }
                    });
                    document.getElementById("containers_input").value = JSON.stringify(this.containers);
                    document.getElementById("memos_input").value = JSON.stringify(this.memos);
                    document.getElementById('truckShipmentForm').submit();
                }
            }
        }
    </script>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Toast Notification System -->
    <script>
        function showToast(type, msg) {
            const icons = { 
                success: 'check-circle', 
                error: 'times-circle', 
                info: 'info-circle', 
                warning: 'exclamation-triangle' 
            };
            
            const container = document.getElementById('toast-container') || (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.className = 'toast-container';
                document.body.appendChild(c);
                return c;
            })();
            
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
            container.appendChild(t);
            setTimeout(() => t.remove(), 7000);
        }

        // Show Laravel session messages as toasts
        @if(session('success'))
            showToast('success', '{{ session('success') }}');
        @endif
        @if(session('error'))
            showToast('error', '{!! addslashes(session('error')) !!}');
        @endif
        @if(session('warning'))
            showToast('warning', '{{ session('warning') }}');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast('error', '{!! addslashes($error) !!}');
            @endforeach
        @endif
    </script>

    <!-- Toast Notification Styles -->
    <style>
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            min-width: 280px;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out, fadeOut 0.5s ease-in 6.5s forwards;
            pointer-events: all;
        }
        .toast i { font-size: 16px; }
        .toast.success { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
        .toast.error { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        .toast.warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .toast.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; transform: translateX(400px); }
        }
    </style>
</x-layout>
