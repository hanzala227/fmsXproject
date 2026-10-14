@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-building" style="color: #405189; margin-right: 6px;"></i> Company Modules
        </div>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 60px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Company Name</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Status</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569;">Active Modules</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; width: 180px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                        {{ $company->id }}
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">{{ $company->name }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $company->domain ?? 'No domain' }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        @if($company->status == 'active')
                            <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">Active</span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">{{ ucfirst($company->status) }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        @php
                            $activeKeys = $company->modules->pluck('module_key')->toArray();
                            $validCount = count(array_intersect($activeKeys, $validModuleKeys));
                        @endphp
                        <span style="background: #405189; color: #fff; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;">
                            {{ $validCount }} Modules
                        </span>
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <button onclick="manageModules({{ $company->id }}, '{{ addslashes($company->name) }}', {{ $company->modules->pluck('module_key')->toJson() }})" style="height: 28px; padding: 0 10px; font-size: 11px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer;">
                                Custom Modules
                            </button>
                            <button onclick="applyPackage({{ $company->id }}, '{{ addslashes($company->name) }}')" style="height: 28px; padding: 0 10px; font-size: 11px; font-weight: 500; color: #405189; background: #e0e7ff; border: none; border-radius: 4px; cursor: pointer;">
                                Apply Package
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">
                        No companies found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 16px;">
        {{ $companies->links() }}
    </div>
</div>

<!-- Manage Custom Modules Modal -->
<div id="customModulesModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 600px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-shrink: 0;">
            <h3 style="margin: 0; font-size: 15px; color: #1e293b; font-weight: 600;">Custom Modules: <span id="cm_company_name" style="color: #405189;"></span></h3>
            <button onclick="document.getElementById('customModulesModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        
        <form id="cmForm" method="POST" action="" style="display: flex; flex-direction: column; overflow: hidden; max-height: calc(90vh - 60px);">
            @csrf
            
            <div style="padding: 20px; overflow-y: auto; flex: 1;" class="custom-scrollbar">
                <div style="margin-bottom: 12px; font-size: 12px; color: #64748b; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px dashed #cbd5e1;">
                    <i class="fa fa-info-circle"></i> Check the modules you want to grant to this company. This will override any existing packages.
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;" id="cmCheckboxes">
                    @foreach($modules as $mod)
                        <label style="display: flex; align-items: center; font-size: 13px; color: #334155; cursor: pointer; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                            <input type="checkbox" name="module_keys[]" value="{{ $mod->key }}" style="margin-right: 10px;"> 
                            <div>
                                <div style="font-weight: 600;">{{ $mod->name }}</div>
                                <div style="font-size: 10px; color: #64748b;">{{ $mod->category }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px; flex-shrink: 0;">
                <button type="button" onclick="document.getElementById('customModulesModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 16px; border: none; background: #405189; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Save Modules</button>
            </div>
        </form>
    </div>
</div>

<!-- Apply Package Modal -->
<div id="applyPackageModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 400px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 15px; color: #1e293b; font-weight: 600;">Apply Package: <span id="ap_company_name" style="color: #405189;"></span></h3>
            <button onclick="document.getElementById('applyPackageModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        
        <form id="apForm" method="POST" action="" style="padding: 20px;">
            @csrf
            
            <div style="margin-bottom: 16px; font-size: 12px; color: #991b1b; background: #fee2e2; padding: 12px; border-radius: 4px; border: 1px solid #fca5a5;">
                <i class="fa fa-warning"></i> Applying a package will overwrite the company's current modules.
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Select Package</label>
                <select name="package_id" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="">-- Choose a Package --</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}">{{ $pkg->name }} (${{ number_format($pkg->price, 2) }})</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('applyPackageModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 16px; border: none; background: #405189; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Apply Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function manageModules(companyId, companyName, activeKeys) {
    document.getElementById('cm_company_name').innerText = companyName;
    document.getElementById('cmForm').action = '/super-admin/modules/company/' + companyId + '/assign';
    
    const checkboxes = document.querySelectorAll('#cmCheckboxes input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = activeKeys.includes(cb.value);
    });
    
    document.getElementById('customModulesModal').style.display = 'flex';
}

function applyPackage(companyId, companyName) {
    document.getElementById('ap_company_name').innerText = companyName;
    document.getElementById('apForm').action = '/super-admin/modules/company/' + companyId + '/apply-package';
    document.getElementById('applyPackageModal').style.display = 'flex';
}
</script>
@endsection
