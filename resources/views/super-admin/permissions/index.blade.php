@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; display: grid; grid-template-columns: 1fr 300px; gap: 16px;">
    
    {{-- Main Content: Permissions List --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 14px; font-weight: 600; color: #1e293b;"><i class="fa fa-key" style="color: #405189; margin-right: 6px;"></i> Permissions List</div>
            
            <form method="GET" action="{{ route('super-admin.permissions.index') }}" style="position: relative; margin: 0;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search permissions..."
                    style="height: 28px; width: 220px; padding: 0 8px 0 26px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                    onfocus="this.style.borderColor='#405189'" onblur="this.style.borderColor='#cbd5e1'">
                <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #94a3b8;"></i>
            </form>
        </div>

        <div style="padding: 16px;">
            @if($permissions->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                @foreach($permissions as $perm)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: #1e293b;">{{ $perm->name }}</div>
                        <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Module: {{ $perm->module_name ?? 'General' }}</div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editPermission({{ $perm->id }}, '{{ $perm->name }}', '{{ $perm->module_name }}')" style="background: none; border: none; color: #3b82f6; font-size: 12px; cursor: pointer; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button onclick="deletePermission({{ $perm->id }})" style="background: none; border: none; color: #ef4444; font-size: 12px; cursor: pointer; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="margin-top: 16px;">
                {{ $permissions->links('pagination::simple-tailwind') }}
            </div>
            @else
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fa fa-ban" style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;"></i>
                <div style="font-size: 12px;">No permissions found.</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar: Add/Edit Permission --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px; align-self: start;">
        <div id="formTitle" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-plus-circle" style="color: #0ab39c; margin-right: 6px;"></i> Add New Permission
        </div>
        
        <form id="permissionForm" action="{{ route('super-admin.permissions.store') }}" method="POST" style="padding: 16px;">
            @csrf
            <input type="hidden" name="_method" id="methodInput" value="POST">
            
            @if($errors->any())
            <div style="margin-bottom: 12px; padding: 8px 10px; background: #fef2f2; border: 1px solid #f87171; border-radius: 4px; color: #991b1b; font-size: 10px;">
                {{ $errors->first() }}
            </div>
            @endif

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Permission Name *</label>
                <input type="text" name="name" required placeholder="e.g. users.create"
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                    onfocus="this.style.borderColor='#405189'" onblur="this.style.borderColor='#cbd5e1'">
                <div style="font-size: 9px; color: #64748b; margin-top: 4px;">Use dot notation like 'module.action'.</div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 8px;">Module Names *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc;">
                    @php
                        $modules = [
                            'Ocean Import', 'Ocean Export', 'Air Import', 'Air Export',
                            'Truck Shipment', 'Custom Brokerage', 'Warehouse & Distribution',
                            'Accounting', 'CRM', 'Customers', 'Master Data', 'Reports',
                            'Settings', 'General'
                        ];
                    @endphp
                    @foreach($modules as $mod)
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="module_name[]" value="{{ $mod }}" style="cursor: pointer;">
                        {{ $mod }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" id="submitBtn" style="flex: 1; height: 32px; font-size: 11px; font-weight: 600; border: none; border-radius: 3px; background: #0ab39c; color: #fff; cursor: pointer;">
                    Add Permission
                </button>
                <button type="button" id="cancelBtn" onclick="resetForm()" style="display: none; flex: 1; height: 32px; font-size: 11px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 3px; background: #f8fafc; color: #475569; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editPermission(id, name, modules) {
    const form = document.getElementById('permissionForm');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const methodInput = document.getElementById('methodInput');
    const cancelBtn = document.getElementById('cancelBtn');

    formTitle.innerHTML = '<i class="fa fa-edit" style="color: #3b82f6; margin-right: 6px;"></i> Edit Permission';
    form.action = '/super-admin/permissions/' + id;
    methodInput.value = 'PUT';
    
    document.querySelector('input[name="name"]').value = name;
    
    document.querySelectorAll('input[name="module_name[]"]').forEach(cb => cb.checked = false);
    
    const moduleArray = modules ? modules.split(',').map(m => m.trim()) : [];
    document.querySelectorAll('input[name="module_name[]"]').forEach(cb => {
        if(moduleArray.includes(cb.value)) {
            cb.checked = true;
        }
    });

    submitBtn.innerHTML = 'Update';
    submitBtn.style.background = '#3b82f6';
    cancelBtn.style.display = 'block';
}

function resetForm() {
    const form = document.getElementById('permissionForm');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const methodInput = document.getElementById('methodInput');
    const cancelBtn = document.getElementById('cancelBtn');

    formTitle.innerHTML = '<i class="fa fa-plus-circle" style="color: #0ab39c; margin-right: 6px;"></i> Add New Permission';
    form.action = '{{ route('super-admin.permissions.store') }}';
    methodInput.value = 'POST';
    
    form.reset();

    submitBtn.innerHTML = 'Add Permission';
    submitBtn.style.background = '#0ab39c';
    cancelBtn.style.display = 'none';
}

function deletePermission(id) {
    if (!confirm('Delete this permission? This may break role access.')) return;
    fetch('/super-admin/permissions/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('error', 'Failed to delete permission'));
}
</script>
@endpush
@endsection
