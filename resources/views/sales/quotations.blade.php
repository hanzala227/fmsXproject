<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 20px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #fff; border: 1px solid #e7ecf1; border-radius: 4px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .portlet-title { padding: 10px 15px; border-bottom: 1px solid #eef1f5; display: flex; align-items: center; justify-content: space-between; min-height: 40px; }
        .portlet-body { padding: 15px; }
        .caption-subject { color: #4b77be; font-size: 13px; font-weight: 700; text-transform: uppercase; }
        
        .btn-freightx { background: #4b77be; color: #fff !important; border: none; padding: 5px 12px; border-radius: 3px; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-freightx:hover { background: #3a62a4; }
        .btn-default-gf { background: #fff; border: 1px solid #ccc; color: #333; padding: 4px 10px; font-size: 11px; border-radius: 3px; cursor: pointer; }

        .form-control-gf { width: 100%; height: 26px; border: 1px solid #c2cad8; padding: 2px 8px; font-size: 11px; border-radius: 2px; background: #fff; }
        .form-label-gf { font-size: 10.5px; font-weight: 600; color: #666; display: block; margin-bottom: 3px; text-transform: uppercase; }
        
        .table-custom { width: 100%; border-collapse: collapse; font-size: 11px; background: #fff; }
        .table-custom thead th { text-align: left; padding: 8px 12px; background: #f1f3f6; color: #5b6e84; font-weight: 700; text-transform: uppercase; border: 1px solid #e7ecf1; }
        .table-custom tbody td { padding: 8px 12px; border: 1px solid #e7ecf1; vertical-align: middle; }
        .table-custom tr:hover { background: #f9fafb; cursor: pointer; }

        .gf-tabs { display: flex; border-bottom: 1px solid #ddd; list-style: none; padding: 0; margin: 0 0 15px 0; background: #fff; border-radius: 4px 4px 0 0; }
        .gf-tabs li { margin-bottom: -1px; }
        .gf-tabs li a { padding: 10px 20px; display: block; color: #555; text-decoration: none; border: 1px solid transparent; cursor: pointer; font-size: 12px; font-weight: 600; }
        .gf-tabs li.active a { background: #fff; border: 1px solid #ddd; border-bottom-color: #fff; border-top: 3px solid #32c5d2; color: #333; }
    </style>
    @endpush

    <div class="page-content" x-data="{ activeTab: 'active', searchQuery: '' }">
        
        <!-- Breadcrumbs -->
        <div style="font-size: 11px; color: #8e9eae; margin-bottom: 15px;">
            <i class="fa fa-home"></i> Home <i class="fa fa-angle-right" style="margin: 0 5px;"></i> Sales <i class="fa fa-angle-right" style="margin: 0 5px;"></i> 
            <span style="color: #333; font-weight: 700;">Rate Management / Quotations</span>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px;">Quotations</h1>
            <div style="display: flex; gap: 8px;">
                <button onclick="document.getElementById('new-quote-modal').style.display='flex'" class="btn-freightx"><i class="fa fa-plus"></i> NEW QUOTE</button>
                <button class="btn-default-gf"><i class="fa fa-download"></i> EXPORT</button>
            </div>
        </div>

        <!-- Main Tabs -->
        <ul class="gf-tabs">
            <li :class="activeTab === 'active' ? 'active' : ''" @click="activeTab = 'active'"><a>Active</a></li>
            <li :class="activeTab === 'expired' ? 'active' : ''" @click="activeTab = 'expired'"><a>Expired</a></li>
            <li :class="activeTab === 'global' ? 'active' : ''" @click="activeTab = 'global'"><a>Global Rates</a></li>
        </ul>

        <div class="portlet light">
            <div class="portlet-title">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="position: relative; width: 300px;">
                        <input type="text" x-model="searchQuery" placeholder="Search ID, Client, Route..." class="form-control-gf" style="padding-left: 25px;">
                        <i class="fa fa-search" style="position: absolute; left: 8px; top: 8px; color: #ccc; font-size: 10px;"></i>
                    </div>
                </div>
            </div>
            <div class="portlet-body" style="padding: 0;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Quote No.</th>
                            <th>Customer / Client</th>
                            <th>Origin → Destination</th>
                            <th style="width: 120px; text-align: center;">Validity</th>
                            <th style="width: 120px; text-align: right;">Total (USD)</th>
                            <th style="width: 80px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations ?? [] as $quote)
                        <tr>
                            <td style="color: #4b77be; font-weight: 700;">{{ $quote->quote_number }}</td>
                            <td>
                                <div style="font-weight: 700; color: #333;">{{ $quote->tradePartner->name ?? 'N/A' }}</div>
                                <div style="font-size: 9px; color: #888; text-transform: uppercase;">{{ $quote->service_type }}</div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                    <span>{{ $quote->origin ?? 'ANY' }}</span>
                                    <i class="fa fa-arrow-right" style="color: #ccc; font-size: 10px;"></i>
                                    <span>{{ $quote->destination ?? 'ANY' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: #f1f3f6; color: #4b77be; padding: 2px 8px; font-size: 9px; font-weight: 700; border-radius: 2px; border: 1px solid #e7ecf1;">
                                    {{ $quote->valid_until }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #36c6d3;">
                                ${{ number_format($quote->total_amount, 2) }}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <i class="fa fa-file-pdf-o" style="color: #4b77be; cursor: pointer;"></i>
                                    <form action="{{ route('quotations.destroy', $quote->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;">
                                            <i class="fa fa-trash" style="color: #ed6b75;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #8e9eae; font-style: italic;">No quotations generated yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Metronic Modal -->
    <div id="new-quote-modal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000;" x-cloak>
        <div class="portlet light" style="width: 900px; margin: 0; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="portlet-title" style="background: #2b3643;">
                <span class="caption-subject" style="color: #fff;">Create New Quotation</span>
                <i onclick="document.getElementById('new-quote-modal').style.display='none'" class="fa fa-times" style="color: #fff; cursor: pointer;"></i>
            </div>
            <div class="portlet-body">
                <form method="POST" action="{{ route('quotations.store') }}">
                    @csrf
                    
                    <div style="font-weight: 700; color: #4b77be; margin-bottom: 15px; border-bottom: 1px solid #eef1f5; padding-bottom: 5px;">1. GENERAL INFORMATION</div>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                        <div style="grid-column: span 2;">
                            <label class="form-label-gf">Customer / Client <span style="color:red;">*</span></label>
                            <select name="trade_partner_id" required class="form-control-gf">
                                <option value="">Select Customer...</option>
                                @foreach($tradePartners ?? [] as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="form-label-gf">Service Type <span style="color:red;">*</span></label>
                            <select name="service_type" required class="form-control-gf">
                                <option>Ocean Import FCL</option>
                                <option>Ocean Export FCL</option>
                                <option>Air Import</option>
                                <option>Air Export</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-gf">Valid Until <span style="color:red;">*</span></label>
                            <input type="date" name="valid_until" required class="form-control-gf">
                        </div>
                        <div>
                            <label class="form-label-gf">Incoterm</label>
                            <select name="incoterm" class="form-control-gf">
                                <option>FOB</option><option>EXW</option><option>CIF</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-gf">Origin (POL)</label>
                            <input type="text" name="origin" class="form-control-gf" placeholder="CNSHA">
                        </div>
                        <div>
                            <label class="form-label-gf">Destination (POD)</label>
                            <input type="text" name="destination" class="form-control-gf" placeholder="PKBQM">
                        </div>
                    </div>

                    <div style="font-weight: 700; color: #4b77be; margin-bottom: 15px; border-bottom: 1px solid #eef1f5; padding-bottom: 5px;">2. BASE FREIGHT RATES</div>

                    <div style="border: 1px solid #e7ecf1; border-radius: 4px; overflow: hidden; margin-bottom: 20px;">
                        <table class="table-custom" style="width: 100%;">
                            <thead>
                                <tr style="background: #f1f3f6;">
                                    <th style="width: 80px;">Code</th>
                                    <th>Description</th>
                                    <th style="width: 100px;">Unit</th>
                                    <th style="width: 100px; text-align: right;">Rate</th>
                                    <th style="width: 60px; text-align: center;">Qty</th>
                                    <th style="width: 120px; text-align: right;">Total (USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" value="OFR" class="form-control-gf" readonly style="background:#f9fafb;"></td>
                                    <td><input type="text" value="Ocean Freight" class="form-control-gf"></td>
                                    <td>
                                        <select class="form-control-gf">
                                            <option>20'</option><option selected>40'</option><option>40'HC</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="total_amount" value="1800.00" class="form-control-gf" style="text-align: right;"></td>
                                    <td><input type="number" value="1" class="form-control-gf" style="text-align: center;"></td>
                                    <td style="text-align: right; font-weight: 700; color: #333;">$1,800.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; border-top: 1px solid #eef1f5; padding-top: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 11px; font-weight: 700; color: #8e9eae;">GRAND TOTAL:</span>
                            <span style="font-size: 20px; font-weight: 700; color: #36c6d3;">$ 1,800.00</span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" onclick="document.getElementById('new-quote-modal').style.display='none'" class="btn-default-gf">CANCEL</button>
                            <button type="submit" class="btn-freightx">SAVE QUOTATION</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
