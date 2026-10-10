@php use Illuminate\Support\Str; @endphp
<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .table-custom th, .table-custom td {
            font-size: 11px !important;
            padding: 6px 8px !important;
        }
        .table-custom th {
            font-weight: 600 !important;
        }
        .btn-tool {
            font-size: 11px !important;
        }
        .status-badge {
            font-size: 11px !important;
            font-weight: 600 !important;
        }
    </style>
    @endpush

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- REJECT CONFIRM MODAL --}}
    <div class="overlay" id="reject-overlay" style="display:none;" onclick="if(event.target===this) closeRejectModal()">
        <div class="confirm-box" style="max-width:400px; padding: 20px;">
            <div class="confirm-icon"><i class="fa fa-times-circle" style="color:#ef4444;"></i></div>
            <h4>Reject Document?</h4>
            <p style="margin-bottom: 10px;">Please provide a reason for rejecting this document:</p>
            <input type="hidden" id="reject-invoice-id">
            <textarea id="reject-reason" style="width: 100%; height: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="Reason for rejection..."></textarea>
            <div class="confirm-actions" style="margin-top: 15px;">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeRejectModal()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeReject()">
                    <i class="fa fa-times"></i> Reject
                </button>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">INV/DN/CN Verification</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">DOCUMENT VERIFICATION QUEUE</span>
                </div>
                <div class="actions">
                    <button class="btn-tool green" onclick="verifySelected()" style="padding: 4px 12px;">
                        <i class="fa fa-check-square-o"></i> Verify Selected
                    </button>
                    <button class="btn-tool danger" onclick="openRejectBulkModal()" style="padding: 4px 12px;">
                        <i class="fa fa-times-circle"></i> Reject Selected
                    </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-container">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;"><input type="checkbox" id="selectAll"></th>
                                <th style="width:60px;">Type</th>
                                <th style="width:120px;">INV/DN/CN No.</th>
                                <th style="width:180px;">Customer/Agent</th>
                                <th style="width:180px;">Shipper/Consignee</th>
                                <th style="width:120px;">MBL No.</th>
                                <th style="width:100px;text-align:right;">Amount</th>
                                <th style="width:120px;">Created By</th>
                                <th style="width:100px;">Created Date</th>
                                <th style="width:100px;">Status</th>
                                <th style="width:200px;text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $row)
                                <tr id="row-{{ $row->id }}">
                                    <td style="text-align:center;"><input type="checkbox" class="doc-select" value="{{ $row->id }}"></td>
                                    <td>
                                        <span class="label label-sm" style="background-color: {{ $row->document_type === 'INV' ? '#3b82f6' : ($row->document_type === 'CN' ? '#ef4444' : '#f59e0b') }}; color: white; padding: 2px 6px; border-radius: 4px;">
                                            {{ $row->document_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('accounting.invoices.show', $row->id) }}" style="color:#2563eb;font-weight:600;text-decoration:none;">
                                            {{ $row->invoice_no }}
                                        </a>
                                    </td>
                                    <td>{{ $row->billTo->name ?? '' }}</td>
                                    <td>
                                        @php 
                                            $inv = $row->invoiceable;
                                            $shipper = $inv->shipper->name ?? $inv->dmShipper->name ?? '';
                                            $consignee = $inv->consignee->name ?? $inv->dmConsignee->name ?? '';
                                        @endphp
                                        {{ $shipper ?: ($consignee ?: '-') }}
                                    </td>
                                    <td>{{ $row->mbl_no ?? optional($row->invoiceable)->mbl_no ?? optional($row->invoiceable)->file_no ?? '-' }}</td>
                                    <td style="text-align:right;">
                                        {{ $row->currency->code ?? '' }} {{ number_format($row->total_amount, 2) }}
                                    </td>
                                    <td>{{ $row->issuer->name ?? 'System' }}</td>
                                    <td>{{ $row->created_at ? $row->created_at->format('Y-m-d') : '' }}</td>
                                    <td id="status-{{ $row->id }}">
                                        @if($row->verification_status === 'Pending Verification')
                                            <span class="status-badge" style="color: #f59e0b; font-weight: bold;"><i class="fa fa-clock-o"></i> Pending</span>
                                        @elseif($row->verification_status === 'Verified')
                                            <span class="status-badge" style="color: #22c55e; font-weight: bold;"><i class="fa fa-check-circle"></i> Verified</span>
                                        @elseif($row->verification_status === 'Rejected')
                                            <span class="status-badge" style="color: #ef4444; font-weight: bold;"><i class="fa fa-times-circle"></i> Rejected</span>
                                        @else
                                            <span class="status-badge">{{ $row->verification_status }}</span>
                                        @endif
                                    </td>
                                    <td id="actions-{{ $row->id }}" style="text-align:center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                            <a href="{{ route('accounting.invoices.show', $row->id) }}" class="btn-tool" style="padding: 2px 8px; text-decoration: none; color: #333; white-space: nowrap;" title="View">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @if($row->verification_status === 'Pending Verification')
                                                <button class="btn-tool green" onclick="verifyDocument({{ $row->id }})" title="Verify" style="padding: 2px 8px; white-space: nowrap;">
                                                    <i class="fa fa-check"></i> Verify
                                                </button>
                                                <button class="btn-tool danger" onclick="openRejectModal({{ $row->id }})" title="Reject" style="padding: 2px 8px; white-space: nowrap;">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            @else
                                                <span style="color: #64748b; font-size: 11px;">Processed</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;">
                                        <i class="fa fa-check-circle" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                                        No pending documents to verify.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:15px;">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showToast(type, msg) {
            const c = document.getElementById('toast-container');
            if(!c) return;
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${msg}`;
            c.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
        }

        function verifyDocument(id) {
            if(!confirm('Are you sure you want to verify this document?')) return;
            
            fetch(`/accounting/invoice/${id}/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    showToast('success', data.message);
                    const statusCell = document.getElementById(`status-${id}`);
                    if(statusCell) {
                        statusCell.innerHTML = '<span class="status-badge" style="color: #22c55e; font-weight: bold;"><i class="fa fa-check-circle"></i> Verified</span>';
                    }
                    const actionCell = document.getElementById(`actions-${id}`);
                    if(actionCell) {
                        actionCell.innerHTML = `
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                <a href="/accounting/invoices/${id}" class="btn-tool" style="padding: 2px 8px; text-decoration: none; color: #333; white-space: nowrap;" title="View">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <span style="color: #64748b; font-size: 11px;">Processed</span>
                            </div>
                        `;
                    }
                } else {
                    showToast('error', data.message || 'Failed to verify document.');
                }
            })
            .catch(() => showToast('error', 'Network error.'));
        }

        function openRejectModal(id) {
            document.getElementById('reject-invoice-id').value = id;
            document.getElementById('reject-reason').value = '';
            document.getElementById('reject-overlay').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('reject-overlay').style.display = 'none';
        }

        function executeReject() {
            const idVal = document.getElementById('reject-invoice-id').value;
            const reason = document.getElementById('reject-reason').value.trim();
            
            if(!reason) {
                alert('Please provide a reason for rejection.');
                return;
            }
            
            const ids = idVal.split(',');
            if (ids.length === 1) {
                // Single reject
                const id = ids[0];
                fetch(`/accounting/invoice/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ reject_reason: reason })
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        showToast('success', data.message);
                        closeRejectModal();
                        const statusCell = document.getElementById(`status-${id}`);
                        if(statusCell) statusCell.innerHTML = '<span class="status-badge" style="color: #ef4444; font-weight: bold;"><i class="fa fa-times-circle"></i> Rejected</span>';
                        const actionCell = document.getElementById(`actions-${id}`);
                        if(actionCell) {
                            actionCell.innerHTML = `
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                    <a href="/accounting/invoices/${id}" class="btn-tool" style="padding: 2px 8px; text-decoration: none; color: #333; white-space: nowrap;" title="View">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <span style="color: #64748b; font-size: 11px;">Processed</span>
                                </div>
                            `;
                        }
                    } else {
                        showToast('error', data.message || 'Failed to reject document.');
                    }
                })
                .catch(() => showToast('error', 'Network error.'));
            } else {
                // Bulk reject
                showToast('info', `Rejecting ${ids.length} document(s)...`);
                Promise.all(ids.map(id => 
                    fetch(`/accounting/invoice/${id}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ reject_reason: reason })
                    }).then(r => r.json().then(data => ({id, data})))
                ))
                .then(results => {
                    let successCount = 0;
                    results.forEach(res => {
                        if(res.data.success) {
                            successCount++;
                            const statusCell = document.getElementById(`status-${res.id}`);
                            if(statusCell) statusCell.innerHTML = '<span class="status-badge" style="color: #ef4444; font-weight: bold;"><i class="fa fa-times-circle"></i> Rejected</span>';
                            const actionCell = document.getElementById(`actions-${res.id}`);
                            if(actionCell) {
                                actionCell.innerHTML = `
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                        <a href="/accounting/invoices/${res.id}" class="btn-tool" style="padding: 2px 8px; text-decoration: none; color: #333; white-space: nowrap;" title="View">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <span style="color: #64748b; font-size: 11px;">Processed</span>
                                    </div>
                                `;
                            }
                        }
                    });
                    showToast('success', `Successfully rejected ${successCount} out of ${ids.length} document(s).`);
                    closeRejectModal();
                    document.querySelectorAll('.doc-select:checked').forEach(cb => cb.checked = false);
                    const selectAll = document.getElementById('selectAll');
                    if (selectAll) selectAll.checked = false;
                })
                .catch(() => {
                    showToast('error', 'Network error during bulk rejection.');
                    closeRejectModal();
                });
            }
        }

        function verifySelected() {
            const checked = Array.from(document.querySelectorAll('.doc-select:checked')).map(cb => cb.value);
            if(checked.length === 0) {
                alert('Please select at least one document to verify.');
                return;
            }
            if(!confirm(`Are you sure you want to verify ${checked.length} document(s)?`)) return;
            
            showToast('info', `Verifying ${checked.length} document(s)...`);
            
            Promise.all(checked.map(id => 
                fetch(`/accounting/invoice/${id}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(r => r.json().then(data => ({id, data})))
            ))
            .then(results => {
                let successCount = 0;
                results.forEach(res => {
                    if(res.data.success) {
                        successCount++;
                        const statusCell = document.getElementById(`status-${res.id}`);
                        if(statusCell) statusCell.innerHTML = '<span class="status-badge" style="color: #22c55e; font-weight: bold;"><i class="fa fa-check-circle"></i> Verified</span>';
                        const actionCell = document.getElementById(`actions-${res.id}`);
                        if(actionCell) actionCell.innerHTML = `
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                <a href="/accounting/invoices/${res.id}" class="btn-tool" style="padding: 2px 8px; text-decoration: none; color: #333; white-space: nowrap;" title="View">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <span style="color: #64748b; font-size: 11px;">Processed</span>
                            </div>
                        `;
                    }
                });
                showToast('success', `Successfully verified ${successCount} out of ${checked.length} document(s).`);
                document.querySelectorAll('.doc-select:checked').forEach(cb => cb.checked = false);
                const selectAll = document.getElementById('selectAll');
                if (selectAll) selectAll.checked = false;
            })
            .catch(() => showToast('error', 'Network error during bulk verification.'));
        }

        function openRejectBulkModal() {
            const checked = Array.from(document.querySelectorAll('.doc-select:checked')).map(cb => cb.value);
            if(checked.length === 0) {
                alert('Please select at least one document to reject.');
                return;
            }
            document.getElementById('reject-invoice-id').value = checked.join(',');
            document.getElementById('reject-reason').value = '';
            document.getElementById('reject-overlay').style.display = 'flex';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.doc-select');
                    checkboxes.forEach(cb => {
                        if(!cb.disabled) cb.checked = selectAll.checked;
                    });
                });
            }
        });
    </script>
    @endpush
</x-layout>
