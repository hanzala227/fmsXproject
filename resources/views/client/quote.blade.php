@extends('layouts.userapp')
@section('title', 'My Quotes')

@section('content')

<div class="page-content">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><i class="fa fa-home"></i> <a href="{{ route('client.index') }}">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">My Quotes</span></li>
        </ul>
    </div>

    {{-- Detail panel: Quote # par click karne par yahan load hota hai --}}
    <div class="portlet" id="detail-panel">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-file-text-o" style="color:#3b82f6;"></i>
                <span class="caption-subject" id="detail-title">QUOTE DETAILS</span>
            </div>
            <button type="button" class="close-btn" onclick="closeDetail()" title="Close"><i class="fa fa-times"></i></button>
        </div>
        <div id="detail-body"></div>
    </div>

    {{-- Quotes list --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-list-alt" style="color:#3b82f6;"></i>
                <span class="caption-subject">MY QUOTES</span>
                <span class="count-badge">{{ $quotes->total() }}</span>
            </div>
        </div>

        <div class="portlet-tool">
            <form method="GET" class="tool-form">
                <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                <input type="text" name="search" class="input-inline" style="width:180px;"
                       placeholder="Quote no / commodity" value="{{ request('search') }}">

                <select name="status" class="input-inline">
                    <option value="">All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-tool green"><i class="fa fa-filter"></i> Filter</button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('cp.quotes.index') }}" class="btn-tool"><i class="fa fa-times"></i> Clear</a>
                @endif
            </form>
        </div>

        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table" id="quotes-grid">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Quote #</th>
                            <th>Date</th>
                            <th>Valid Till</th>
                            <th>Mode</th>
                            <th>Route</th>
                            <th>Commodity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $q)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $quotes->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('cp.quotes.show', $q->id) }}"
                                       class="row-link quote-link"
                                       data-title="QUOTE {{ $q->quote_no }}">{{ $q->quote_no }}</a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($q->quote_date)->format('d M Y') }}</td>
                                <td>{{ $q->expiry_date ? \Carbon\Carbon::parse($q->expiry_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $q->transport_mode ?? '-' }}</td>
                                <td>{{ $q->pol_code ?? '-' }} <i class="fa fa-long-arrow-right" style="color:#94a3b8;"></i> {{ $q->pod_code ?? '-' }}</td>
                                <td>{{ $q->commodity ?? '-' }}</td>
                                <td><span class="status-pill">{{ $q->status }}</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="8">
                                    <div class="empty-title">No Quotes Found</div>
                                    There are no quotations available for your company.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($quotes->hasPages())
            <div class="pagination-wrap">{{ $quotes->links() }}</div>
        @endif
    </div>

</div>


<script>
var panel = document.getElementById('detail-panel');
var body  = document.getElementById('detail-body');

document.querySelectorAll('.quote-link').forEach(function (a) {
    a.addEventListener('click', function (e) {
        e.preventDefault();
        openDetail(a);
    });
});

function openDetail(a) {
    document.querySelectorAll('#quotes-grid tr.active-row').forEach(function (r) { r.classList.remove('active-row'); });
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
            var content = doc.getElementById('quote-detail-content');
            if (!content) throw new Error('missing');
            body.innerHTML = content.innerHTML;
        })
        .catch(function () {
            body.innerHTML = '<div class="notes-box" style="color:#ef4444;">Details load nahi ho saki. Dobara try karein.</div>';
        });
}

function closeDetail() {
    panel.classList.remove('open');
    document.querySelectorAll('#quotes-grid tr.active-row').forEach(function (r) { r.classList.remove('active-row'); });
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDetail(); });
</script>

@endsection