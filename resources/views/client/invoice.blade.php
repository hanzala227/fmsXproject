@extends('layouts.userapp')
@section('title', 'My Invoices')

@section('content')

<div class="page-content">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">My Invoices</span></li>
        </ul>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-title">Total Billed</div>
            <div class="summary-value">{{ number_format((float) $totals->billed, 2) }}</div>
        </div>
        <div class="summary-card paid">
            <div class="summary-title">Paid</div>
            <div class="summary-value">{{ number_format((float) $totals->paid, 2) }}</div>
        </div>
        <div class="summary-card due">
            <div class="summary-title">Outstanding</div>
            <div class="summary-value">{{ number_format((float) $totals->billed - (float) $totals->paid, 2) }}</div>
        </div>
    </div>

    {{-- Detail panel: Invoice # par click karne par yahan load hota hai --}}
    <div class="portlet" id="detail-panel">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-file-text-o" style="color:#3b82f6;"></i>
                <span class="caption-subject" id="detail-title">INVOICE DETAILS</span>
            </div>
            <button type="button" class="close-btn" onclick="closeDetail()" title="Close"><i class="fa fa-times"></i></button>
        </div>
        <div id="detail-body"></div>
    </div>

    {{-- Invoices list --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-list-alt" style="color:#3b82f6;"></i>
                <span class="caption-subject">MY INVOICES</span>
                <span class="count-badge">{{ $invoices->total() }}</span>
            </div>
        </div>

        <div class="portlet-tool">
            <form method="GET" class="tool-form">
                <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                <input type="text" name="search" class="input-inline" style="width:160px;"
                       placeholder="Invoice no" value="{{ request('search') }}">

                <select name="status" class="input-inline">
                    <option value="">All Status</option>
                    @foreach(['POSTED', 'PARTIAL', 'PAID'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>

                <label class="check-label">
                    <input type="checkbox" name="overdue" value="1" @checked(request('overdue'))>
                    Overdue Only
                </label>

                <button type="submit" class="btn-tool green"><i class="fa fa-filter"></i> Filter</button>

                @if(request()->hasAny(['search', 'status', 'overdue']))
                    <a href="{{ route('cp.invoices.index') }}" class="btn-tool"><i class="fa fa-times"></i> Clear</a>
                @endif
            </form>
        </div>

        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table" id="invoices-grid">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th class="num">Total</th>
                            <th class="num">Paid</th>
                            <th class="num">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $i)
                            @php
                                $bal  = (float) $i->total_amount - (float) $i->paid_amount;
                                $late = $i->status !== 'PAID' && $i->due_date && $i->due_date < now()->toDateString();
                            @endphp
                            <tr class="{{ $late ? 'overdue-row' : '' }}">
                                <td style="text-align:center;color:#94a3b8;">{{ $invoices->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('cp.invoices.show', $i->id) }}"
                                       class="row-link invoice-link"
                                       data-title="INVOICE {{ $i->invoice_no }}">{{ $i->invoice_no }}</a>
                                </td>
                                <td>{{ $i->invoice_date ?? '-' }}</td>
                                <td>
                                    {{ $i->due_date ?? '-' }}
                                    @if($late)<span class="overdue-pill">Overdue</span>@endif
                                </td>
                                <td class="num">{{ $i->currency ?? '' }} {{ number_format((float) $i->total_amount, 2) }}</td>
                                <td class="num">{{ number_format((float) $i->paid_amount, 2) }}</td>
                                <td class="num">{{ number_format($bal, 2) }}</td>
                                <td><span class="status-pill">{{ $i->status }}</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="8">
                                    <div class="empty-title">No Invoices Found</div>
                                    There are no invoices available for your company.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoices->hasPages())
            <div class="pagination-wrap">{{ $invoices->links() }}</div>
        @endif
    </div>

</div>

<link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">

<script>
var panel = document.getElementById('detail-panel');
var body  = document.getElementById('detail-body');

document.querySelectorAll('.invoice-link').forEach(function (a) {
    a.addEventListener('click', function (e) {
        e.preventDefault();
        openDetail(a);
    });
});

function openDetail(a) {
    document.querySelectorAll('#invoices-grid tr.active-row').forEach(function (r) { r.classList.remove('active-row'); });
    a.closest('tr').classList.add('active-row');

    document.getElementById('detail-title').textContent = a.dataset.title;
    body.innerHTML = '<div class="notes-box">Loading...</div>';
    panel.classList.add('open');
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

    fetch(a.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (res) {
            if (!res.ok) throw new Error(res.status);
            return res.text();
        })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var content = doc.getElementById('invoice-detail-content');
            if (!content) throw new Error('missing');
            body.innerHTML = content.innerHTML;
        })
        .catch(function () {
            body.innerHTML = '<div class="notes-box" style="color:#ef4444;">Details load nahi ho saki. Dobara try karein.</div>';
        });
}

function closeDetail() {
    panel.classList.remove('open');
    document.querySelectorAll('#invoices-grid tr.active-row').forEach(function (r) { r.classList.remove('active-row'); });
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDetail(); });
</script>

@endsection