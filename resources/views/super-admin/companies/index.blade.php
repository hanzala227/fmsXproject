@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;" x-data="companyList()">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-building" style="color: #405189; margin-right: 4px;"></i> Company Management</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                {{-- Search --}}
                <div style="position: relative;">
                    <input type="text" x-model="search" @input.debounce.400ms="applyFilters()" placeholder="Search companies..."
                        style="height: 26px; width: 200px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('super-admin.companies.create') }}"
                   style="height: 26px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 3px; text-decoration: none; cursor: pointer;">
                    <i class="fa fa-plus"></i> New Company
                </a>
                <button onclick="exportExcel()"
                    style="height: 26px; padding: 0 8px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer; color: #475569;">
                    <i class="fa fa-file-excel-o" style="color: #22c55e;"></i> Excel
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="padding: 0 10px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 16px;">
            @php
                $currentStatus = request('status', '');
                $tabs = [
                    '' => 'All Companies',
                    'active' => 'Active',
                    'trial' => 'Trial',
                    'suspended' => 'Suspended',
                    'expired' => 'Expired',
                    'inactive' => 'Inactive'
                ];
            @endphp
            @foreach($tabs as $val => $label)
            <button @click="statusFilter = '{{ $val }}'; applyFilters()"
                style="padding: 8px 4px; font-size: 11px; font-weight: 600; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid {{ $currentStatus === $val ? '#0ab39c' : 'transparent' }}; color: {{ $currentStatus === $val ? '#0ab39c' : '#64748b' }};">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 40px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">#</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Company</th>
                        <th style="width: 80px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Code</th>
                        <th style="width: 140px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Admin User</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Email</th>
                        <th style="width: 80px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Status</th>
                        <th style="width: 200px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Modules</th>
                        <th style="width: 90px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created</th>
                        <th style="width: 90px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Last Login</th>
                        <th style="width: 140px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @include('super-admin.companies.partials.table-rows')
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($companies->hasPages())
        <div style="padding: 8px 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: #64748b;">
            <span>Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ $companies->total() }}</span>
            <div style="display: flex; gap: 2px;">
                @if($companies->onFirstPage())
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">‹</span>
                @else
                    <a href="{{ $companies->previousPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">‹</a>
                @endif
                @foreach($companies->getUrlRange(max(1, $companies->currentPage()-2), min($companies->lastPage(), $companies->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid {{ $page == $companies->currentPage() ? '#405189' : '#cbd5e1' }}; border-radius: 3px; font-size: 10px; text-decoration: none;
                        {{ $page == $companies->currentPage() ? 'background: #405189; color: #fff; font-weight: 600;' : 'color: #475569;' }}">{{ $page }}</a>
                @endforeach
                @if($companies->hasMorePages())
                    <a href="{{ $companies->nextPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">›</a>
                @else
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Reset Password Modal --}}
<div id="reset-password-modal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 6px; width: 400px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 14px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-key" style="color: #ef4444; margin-right: 6px;"></i> Reset Password</span>
            <button onclick="closeResetPasswordModal()" style="background: none; border: none; color: #94a3b8; font-size: 14px; cursor: pointer;">×</button>
        </div>
        <form id="reset-password-form" method="POST" style="padding: 16px;">
            @csrf
            <p id="reset-company-name" style="font-size: 11px; color: #64748b; margin: 0 0 12px;"></p>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 10px; font-weight: 600; color: #475569; margin-bottom: 4px;">New Password</label>
                <input type="password" name="new_password" required minlength="8"
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 10px; font-weight: 600; color: #475569; margin-bottom: 4px;">Confirm Password</label>
                <input type="password" name="new_password_confirmation" required minlength="8"
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="closeResetPasswordModal()" style="height: 30px; padding: 0 14px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; background: #fff; color: #475569; cursor: pointer;">Cancel</button>
                <button type="submit" style="height: 30px; padding: 0 14px; font-size: 10px; border: none; border-radius: 3px; background: #ef4444; color: #fff; font-weight: 600; cursor: pointer;">Reset Password</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function companyList() {
    return {
        search: new URLSearchParams(window.location.search).get('search') || '',
        statusFilter: new URLSearchParams(window.location.search).get('status') || '',
        applyFilters() {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.statusFilter) params.set('status', this.statusFilter);
            window.location.href = '{{ route('super-admin.companies.index') }}?' + params.toString();
        }
    };
}

function toggleStatus(companyId) {
    if (!confirm('Are you sure you want to change this company\'s status?')) return;
    fetch('/super-admin/companies/' + companyId + '/toggle-status', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('error', 'Failed to update status'));
}

function openResetPasswordModal(companyId, companyName) {
    document.getElementById('reset-password-form').action = '/super-admin/companies/' + companyId + '/reset-password';
    document.getElementById('reset-company-name').textContent = 'Reset password for: ' + companyName;
    document.getElementById('reset-password-modal').style.display = 'flex';
}

function closeResetPasswordModal() {
    document.getElementById('reset-password-modal').style.display = 'none';
}

function exportExcel() {
    showToast('info', 'Preparing export...');
    fetch('{{ route('super-admin.companies.export-csv') }}')
        .then(r => r.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'companies-' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
            showToast('success', 'Export complete');
        })
        .catch(() => showToast('error', 'Export failed'));
}

// Close modal on backdrop click
document.getElementById('reset-password-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeResetPasswordModal();
});
</script>
@endpush
@endsection
