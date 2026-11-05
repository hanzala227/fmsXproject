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

        .badge-gf { padding: 2px 8px; border-radius: 2px; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #fff; }
        .bg-new { background: #4b77be; }
        .bg-won { background: #36c6d3; }
        .bg-lost { background: #ed6b75; }
    </style>
    @endpush

    <div class="page-content" x-data="{ searchQuery: '' }">
        
        <!-- Breadcrumbs -->
        <div style="font-size: 11px; color: #8e9eae; margin-bottom: 15px;">
            <i class="fa fa-home"></i> Home <i class="fa fa-angle-right" style="margin: 0 5px;"></i> Sales <i class="fa fa-angle-right" style="margin: 0 5px;"></i> 
            <span style="color: #333; font-weight: 700;">Lead Management</span>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px;">Lead Pipeline</h1>
            <div style="display: flex; gap: 8px;">
                <button onclick="document.getElementById('new-lead-modal').style.display='flex'" class="btn-freightx"><i class="fa fa-plus"></i> NEW LEAD</button>
                <button class="btn-default-gf"><i class="fa fa-filter"></i> FILTER</button>
            </div>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="position: relative; width: 300px;">
                        <input type="text" x-model="searchQuery" placeholder="Search Lead, Company..." class="form-control-gf" style="padding-left: 25px;">
                        <i class="fa fa-search" style="position: absolute; left: 8px; top: 8px; color: #ccc; font-size: 10px;"></i>
                    </div>
                    <div style="display: flex; gap: 2px; background: #eee; padding: 2px; border-radius: 3px;">
                        <button class="btn-default-gf" style="border:none; background:#fff; font-weight:700;">ALL</button>
                        <button class="btn-default-gf" style="border:none; background:transparent;">WON</button>
                        <button class="btn-default-gf" style="border:none; background:transparent;">LOST</button>
                    </div>
                </div>
            </div>
            <div class="portlet-body" style="padding: 0;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Status</th>
                            <th>Service Required</th>
                            <th>Potential Volume</th>
                            <th style="width: 80px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads ?? [] as $lead)
                        <tr>
                            <td style="font-weight: 700; color: #4b77be;">{{ $lead->company_name }}</td>
                            <td>
                                <div style="font-weight: 700;">{{ $lead->contact_person }}</div>
                                <div style="font-size: 9px; color: #888;">{{ $lead->email }}</div>
                            </td>
                            <td>
                                <span class="badge-gf {{ $lead->status == 'won' ? 'bg-won' : ($lead->status == 'lost' ? 'bg-lost' : 'bg-new') }}">
                                    {{ $lead->status ?? 'Active' }}
                                </span>
                            </td>
                            <td>{{ $lead->service_required }}</td>
                            <td>{{ $lead->potential_volume }}</td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                    <a href="{{ route('sales.quotations.create', ['lead_id' => $lead->id]) }}" title="Create Freight Quotation" style="color: #26c281; font-size: 10px; font-weight: 700; text-decoration: none; padding: 2px 6px; border: 1px solid #26c281; border-radius: 3px; display: inline-flex; align-items: center; gap: 3px;">
                                        <i class="fa fa-file-text-o"></i> Quote
                                    </a>
                                    <i class="fa fa-edit" style="color: #4b77be; cursor: pointer;"></i>
                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" style="display:inline;">
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
                            <td colspan="6" style="text-align: center; padding: 40px; color: #8e9eae; font-style: italic;">No leads found in pipeline.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Metronic Style Modal -->
    <div id="new-lead-modal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000;" x-cloak>
        <div class="portlet light" style="width: 600px; margin: 0; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="portlet-title" style="background: #2b3643;">
                <span class="caption-subject" style="color: #fff;">Initialize New Prospect</span>
                <i onclick="document.getElementById('new-lead-modal').style.display='none'" class="fa fa-times" style="color: #fff; cursor: pointer;"></i>
            </div>
            <div class="portlet-body">
                <form method="POST" action="{{ route('leads.store') }}">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div style="grid-column: span 2;">
                            <label class="form-label-gf">Company Name <span style="color:red;">*</span></label>
                            <input type="text" name="company_name" required class="form-control-gf">
                        </div>
                        <div>
                            <label class="form-label-gf">Contact Person <span style="color:red;">*</span></label>
                            <input type="text" name="contact_person" required class="form-control-gf">
                        </div>
                        <div>
                            <label class="form-label-gf">Email Address</label>
                            <input type="email" name="email" class="form-control-gf">
                        </div>
                        <div>
                            <label class="form-label-gf">Phone Number</label>
                            <input type="text" name="phone" class="form-control-gf">
                        </div>
                        <div>
                            <label class="form-label-gf">Service Requirement</label>
                            <select name="service_required" class="form-control-gf">
                                <option>Ocean Import</option>
                                <option>Ocean Export</option>
                                <option>Air Import</option>
                                <option>Air Export</option>
                                <option>Trucking</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label class="form-label-gf">Additional Notes</label>
                        <textarea name="notes" class="form-control-gf" style="height: 60px;"></textarea>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #eef1f5; pt-15; margin-top: 15px; padding-top: 15px;">
                        <button type="button" onclick="document.getElementById('new-lead-modal').style.display='none'" class="btn-default-gf">CANCEL</button>
                        <button type="submit" class="btn-freightx">SAVE PROSPECT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>