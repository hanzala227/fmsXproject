@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-cubes" style="color: #405189; margin-right: 6px;"></i> Module Packages
        </div>
        
        <button onclick="document.getElementById('createPackageModal').style.display='flex'" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center;">
            <i class="fa fa-plus" style="margin-right: 6px;"></i> Add Package
        </button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @forelse($packages as $package)
        <div style="background: #fff; border: 1px solid {{ $package->is_active ? '#e2e8f0' : '#f1f5f9' }}; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
            <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: {{ $package->is_active ? '#f8fafc' : '#f8fafc' }}; display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: {{ $package->is_active ? '#1e293b' : '#94a3b8' }};">{{ $package->name }}</h3>
                    <div style="font-size: 12px; color: #64748b;">{{ $package->key }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 18px; font-weight: 700; color: #0f172a;">${{ number_format($package->price, 2) }}</div>
                    <div style="font-size: 10px; color: #64748b; text-transform: uppercase;">Per Month</div>
                </div>
            </div>
            
            <div style="padding: 20px; flex: 1;">
                <p style="font-size: 13px; color: #475569; margin: 0 0 16px 0; line-height: 1.5;">{{ $package->description ?? 'No description provided.' }}</p>
                
                <h4 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 600; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">Included Modules</h4>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @php $pkgKeys = $package->module_keys ?? []; @endphp
                    @foreach($modules as $mod)
                        @if(in_array($mod->key, $pkgKeys))
                            <div style="font-size: 13px; color: #334155; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-check-circle" style="color: #22c55e;"></i> {{ $mod->name }}
                            </div>
                        @endif
                    @endforeach
                    @if(count($pkgKeys) === 0)
                        <div style="font-size: 13px; color: #94a3b8; font-style: italic;">No modules assigned.</div>
                    @endif
                </div>
            </div>
            
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: right;">
                <button onclick="editPackage({{ $package->toJson() }})" style="height: 30px; padding: 0 12px; font-size: 12px; font-weight: 500; color: #405189; background: #e0e7ff; border: none; border-radius: 4px; cursor: pointer;">
                    Edit Package
                </button>
            </div>
        </div>
        @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #64748b; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
            <i class="fa fa-cubes" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
            No packages found. Add a new package to group modules together.
        </div>
        @endforelse
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="createPackageModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 500px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-shrink: 0;">
            <h3 id="modalTitle" style="margin: 0; font-size: 15px; color: #1e293b; font-weight: 600;">Add New Package</h3>
            <button onclick="document.getElementById('createPackageModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        
        <form id="packageForm" method="POST" action="{{ route('super-admin.modules.packages.store') }}" style="display: flex; flex-direction: column; overflow: hidden; max-height: calc(90vh - 60px);">
            @csrf
            <div id="methodOverride"></div>
            
            <div style="padding: 20px; overflow-y: auto; flex: 1;" class="custom-scrollbar">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Package Name *</label>
                        <input type="text" name="name" id="pkg_name" required placeholder="e.g. Standard" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Unique Key *</label>
                        <input type="text" name="key" id="pkg_key" required placeholder="e.g. standard" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Price per Month *</label>
                        <input type="number" step="0.01" name="price" id="pkg_price" required placeholder="0.00" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                    <div style="display: flex; align-items: center; padding-top: 24px;">
                        <label style="display: flex; align-items: center; font-size: 13px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="pkg_active" value="1" checked style="margin-right: 8px;"> Active Package
                        </label>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Description</label>
                    <textarea name="description" id="pkg_desc" rows="2" style="width: 100%; padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; resize: vertical;"></textarea>
                </div>

                <div style="margin-bottom: 8px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Select Included Modules</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;" id="modulesCheckboxes">
                        @foreach($modules as $mod)
                            <label style="display: flex; align-items: center; font-size: 13px; color: #334155; cursor: pointer;">
                                <input type="checkbox" name="module_keys[]" value="{{ $mod->key }}" style="margin-right: 8px;"> {{ $mod->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px; flex-shrink: 0;">
                <button type="button" onclick="document.getElementById('createPackageModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 16px; border: none; background: #405189; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPackage(pkg) {
    document.getElementById('modalTitle').innerText = 'Edit Package';
    document.getElementById('packageForm').action = '/super-admin/modules/packages/' + pkg.id;
    document.getElementById('methodOverride').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    document.getElementById('pkg_name').value = pkg.name;
    document.getElementById('pkg_key').value = pkg.key;
    document.getElementById('pkg_key').readOnly = true;
    document.getElementById('pkg_key').style.background = '#f1f5f9';
    document.getElementById('pkg_price').value = pkg.price;
    document.getElementById('pkg_desc').value = pkg.description || '';
    document.getElementById('pkg_active').checked = pkg.is_active;
    
    // Check modules
    const checkboxes = document.querySelectorAll('#modulesCheckboxes input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = pkg.module_keys && pkg.module_keys.includes(cb.value);
    });
    
    document.getElementById('createPackageModal').style.display = 'flex';
}
</script>
@endsection
