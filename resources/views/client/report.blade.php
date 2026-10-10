@extends('layouts.userapp')
@section('title', 'Reports')

@section('content')

@php
    $rt        = $reportType ?? request('report_type');
    $showShip  = !$rt || $rt === 'shipments';
    $showInv   = !$rt || $rt === 'invoices';
    $showQuote = !$rt || $rt === 'quotations';
    $hasFilter = request()->hasAny(['report_type', 'from_date', 'to_date', 'status']);
    $total     = (int) ($totalShipments ?? 0);
    $pct       = fn ($n) => $total > 0 ? min(100, round((($n ?? 0) / $total) * 100)) : 0;
    $fmtDate   = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '-';
@endphp

{{-- Shipment details popup --}}
<div class="overlay" id="details-overlay" onclick="if(event.target===this) closeDetails()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-title"><i class="fa fa-info-circle" style="color:#3b82f6;"></i> <span id="details-title">Shipment Details</span></div>
            <button class="modal-close" onclick="closeDetails()"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body"><div class="kv-grid" id="details-grid"></div></div>
    </div>
</div>

<div class="page-content">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">Reports</span></li>
        </ul>
    </div>

    {{-- Report cards --}}
    <div class="report-cards">
        <a class="report-card" href="{{ route('client.reports.index', ['report_type' => 'shipments']) }}">
            <div class="report-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000"><path d="M152-80h-32v-80h32q48 0 91.5-10.5T341-204q38 19 66.5 31.5T480-160q44 0 72.5-12.5T619-204q53 23 97.5 33.5T809-160h31v80h-31q-49 0-95.5-9T622-116q-40 19-73 27t-69 8q-36 0-68.5-8T339-116q-45 18-91.5 27T152-80Zm223-200-45-40q-27 27-60.5 46T198-247l-85-273q-5-17 3-31t25-19l59-16v-134q0-33 23.5-56.5T280-800h100v-80h200v80h100q33 0 56.5 23.5T760-720v134l59 16q17 5 25 19t3 31l-85 273q-38-8-71.5-27T630-320l-45 40q-45 40-105 40t-105-40Zm107-40q31 0 55-20.5t44-43.5l46-53 41 42q11 11 22.5 20.5T713-355l46-149-279-73-278 73 46 149q11-10 22.5-19.5T293-395l41-42 46 53q20 24 45 44t57 20ZM280-607l200-53 200 53v-113H280v113Zm201 158Z"/></svg></div>
            <div><h4>Shipment Report</h4><p>Shipment activity, movement and delivery information.</p><span class="go">View Report <i class="fa fa-angle-right"></i></span></div>
        </a>
        <a class="report-card" href="{{ route('client.reports.index', ['report_type' => 'invoices']) }}">
            <div class="report-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000"><path d="M240-80q-50 0-85-35t-35-85v-120h120v-560l60 60 60-60 60 60 60-60 60 60 60-60 60 60 60-60 60 60 60-60v680q0 50-35 85t-85 35H240Zm480-80q17 0 28.5-11.5T760-200v-560H320v440h360v120q0 17 11.5 28.5T720-160ZM360-600v-80h240v80H360Zm0 120v-80h240v80H360Zm320-120q-17 0-28.5-11.5T640-640q0-17 11.5-28.5T680-680q17 0 28.5 11.5T720-640q0 17-11.5 28.5T680-600Zm0 120q-17 0-28.5-11.5T640-520q0-17 11.5-28.5T680-560q17 0 28.5 11.5T720-520q0 17-11.5 28.5T680-480ZM240-160h360v-80H200v40q0 17 11.5 28.5T240-160Zm-40 0v-80 80Z"/></svg></div>
            <div><h4>Invoice Report</h4><p>Invoices, outstanding balances and overdue amounts.</p><span class="go">View Report <i class="fa fa-angle-right"></i></span></div>
        </a>
        <a class="report-card" href="{{ route('client.reports.index', ['report_type' => 'quotations']) }}">
            <div class="report-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000"><path d="M440-200h80v-40h40q17 0 28.5-11.5T600-280v-120q0-17-11.5-28.5T560-440H440v-40h160v-80h-80v-40h-80v40h-40q-17 0-28.5 11.5T360-520v120q0 17 11.5 28.5T400-360h120v40H360v80h80v40ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-560v-160H240v640h480v-480H520ZM240-800v160-160 640-640Z"/></svg></div>
            <div><h4>Quotation Report</h4><p>Quotation history, status and pricing information.</p><span class="go">View Report <i class="fa fa-angle-right"></i></span></div>
        </a>
        <!-- <a class="report-card" href="{{ route('cp.invoices.index') }}">
            <div class="report-icon"><i class="fa fa-check-circle-o"></i></div>
            <div><h4>Payment Report</h4><p>Payment history is listed inside each invoice.</p><span class="go">Open Invoices <i class="fa fa-angle-right"></i></span></div>
        </a> -->
    </div>

    {{-- Summary --}}
    <div class="summary-grid four">
        <div class="summary-card"><div class="summary-title">Total Shipments</div><div class="summary-value">{{ $totalShipments ?? 0 }}</div><div class="summary-sub">All shipments</div></div>
        <div class="summary-card"><div class="summary-title">Total Invoices</div><div class="summary-value">{{ $totalInvoices ?? 0 }}</div><div class="summary-sub">Generated invoices</div></div>
        <div class="summary-card due"><div class="summary-title">Outstanding</div><div class="summary-value">{{ number_format($outstandingInvoiceAmount ?? 0, 2) }}</div><div class="summary-sub">Remaining balance</div></div>
        <div class="summary-card due"><div class="summary-title">Overdue</div><div class="summary-value">{{ number_format($overdueInvoiceAmount ?? 0, 2) }}</div><div class="summary-sub">Overdue amount</div></div>
    </div>

    {{-- Filters --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-filter" style="color:#3b82f6;"></i>
                <span class="caption-subject">REPORT FILTERS</span>
            </div>
            <div style="display:flex;gap:6px;">
                <button type="button" class="btn-tool" onclick="toggleFilters()"><i class="fa fa-filter"></i> Filters</button>
                <a class="btn-tool green" href="{{ route('client.reports.pdf', request()->query()) }}"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
            </div>
        </div>
        <div class="filter-box {{ $hasFilter ? 'open' : '' }}" id="filters-box">
            <form method="GET" action="{{ url()->current() }}">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label>Report Type</label>
                        <select name="report_type" class="input-inline">
                            <option value="">All Reports</option>
                            <option value="shipments" @selected(request('report_type') === 'shipments')>Shipments</option>
                            <option value="invoices" @selected(request('report_type') === 'invoices')>Invoices</option>
                            <option value="quotations" @selected(request('report_type') === 'quotations')>Quotations</option>
                        </select>
                    </div>
                    <div class="filter-field"><label>From Date</label><input type="date" name="from_date" class="input-inline" value="{{ request('from_date') }}"></div>
                    <div class="filter-field"><label>To Date</label><input type="date" name="to_date" class="input-inline" value="{{ request('to_date') }}"></div>
                    <div class="filter-field">
                        <label>Status</label>
                        <select name="status" class="input-inline">
                            <option value="">All Statuses</option>
                            @foreach(['pending' => 'Pending', 'in_transit' => 'In Transit', 'arrived' => 'Arrived', 'delivered' => 'Delivered', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-tool green">Apply</button>
                        <a href="{{ url()->current() }}" class="btn-tool">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($showShip)
    {{-- Shipments --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-ship" style="color:#3b82f6;"></i>
                <span class="caption-subject">SHIPMENTS</span>
                <span class="count-badge">{{ $totalShipments ?? 0 }}</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>File No.</th>
                            <th>Type</th>
                            <th>MAWB / MBL</th>
                            <th>Route</th>
                            <th>ETD</th>
                            <th>ETA</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments ?? [] as $s)
                            @php
                                $detail = [
                                    'File No'         => $s->file_no,
                                    'Type'            => $s->type,
                                    'MAWB / MBL No'   => $s->reference,
                                    'Flight / Vessel' => $s->carrier_ref,
                                    'Voyage'          => $s->voyage,
                                    'Origin'          => $s->origin,
                                    'Destination'     => $s->destination,
                                    'ETD'             => $s->etd,
                                    'ETA'             => $s->eta,
                                    'Status'          => $s->status,
                                ];
                                $detail = array_map(fn ($v) => ($v === null || $v === '') ? '-' : $v, $detail);
                            @endphp
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="#" class="row-link"
                                       data-title="SHIPMENT {{ $s->file_no }}"
                                       data-details="{{ json_encode($detail) }}"
                                       onclick="openDetails(event, this)">{{ $s->file_no }}</a>
                                </td>
                                <td>{{ $s->type }}</td>
                                <td>{{ $s->reference ?? '-' }}</td>
                                <td>{{ $s->origin ?? '-' }} <i class="fa fa-long-arrow-right" style="color:#94a3b8;"></i> {{ $s->destination ?? '-' }}</td>
                                <td>{{ $s->etd ?? '-' }}</td>
                                <td>{{ $s->eta ?? '-' }}</td>
                                <td><span class="status-pill">{{ $s->status ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="8"><div class="empty-title">No shipments found</div>There is no shipment data for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Shipment status --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-bar-chart" style="color:#3b82f6;"></i><span class="caption-subject">SHIPMENT STATUS</span></div>
        </div>
        <div class="status-report-grid">
            @foreach(['In Transit' => $inTransit ?? 0, 'Arrived' => $arrived ?? 0, 'Delivered' => $delivered ?? 0, 'Pending' => $pending ?? 0] as $label => $n)
                <div class="status-report-card">
                    <div class="status-top"><span>{{ $label }}</span><strong>{{ $n }}</strong></div>
                    <div class="progress"><div class="progress-bar" style="width: {{ $pct($n) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($showInv)
    {{-- Invoices --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-file-text-o" style="color:#3b82f6;"></i>
                <span class="caption-subject">INVOICES</span>
                <span class="count-badge">{{ count($invoices ?? []) }}</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices ?? [] as $invoice)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td><a class="row-link" href="{{ route('cp.invoices.index', ['search' => $invoice->invoice_no]) }}">{{ $invoice->invoice_no ?? 'N/A' }}</a></td>
                                <td>{{ $fmtDate($invoice->invoice_date) }}</td>
                                <td><span class="status-pill">{{ ucfirst(strtolower($invoice->status ?? 'Pending')) }}</span></td>
                                <td class="num">{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="5"><div class="empty-title">No invoices found</div>There is no invoice data for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($showQuote)
    {{-- Quotations --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-list-alt" style="color:#3b82f6;"></i>
                <span class="caption-subject">QUOTATIONS</span>
                <span class="count-badge">{{ count($quotations ?? []) }}</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Quote #</th>
                            <th>Date</th>
                            <th>Valid Till</th>
                            <th>Mode</th>
                            <th>Commodity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations ?? [] as $q)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td><a class="row-link" href="{{ route('cp.quotes.index', ['search' => $q->quote_no]) }}">{{ $q->quote_no }}</a></td>
                                <td>{{ $fmtDate($q->quote_date) }}</td>
                                <td>{{ $fmtDate($q->expiry_date) }}</td>
                                <td>{{ $q->transport_mode ?? '-' }}</td>
                                <td>{{ $q->commodity ?? '-' }}</td>
                                <td><span class="status-pill">{{ $q->status }}</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="7"><div class="empty-title">No quotations found</div>There is no quotation data for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Export banner --}}
    <div class="export-banner">
        <div>
            <h4>Need a detailed report?</h4>
            <p>Current filters ke hisaab se summary, shipments, invoices aur quotations ka PDF report download karein.</p>
        </div>
        <a class="btn-tool green" href="{{ route('client.reports.pdf', request()->query()) }}"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
    </div>

</div>

<link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">

<script>
function toggleFilters() { document.getElementById('filters-box').classList.toggle('open'); }

function openDetails(e, el) {
    e.preventDefault();
    var data = JSON.parse(el.dataset.details);
    var grid = document.getElementById('details-grid');
    grid.innerHTML = '';
    Object.keys(data).forEach(function (label) {
        var k = document.createElement('div'); k.className = 'k'; k.textContent = label;
        var v = document.createElement('div'); v.className = 'v'; v.textContent = data[label];
        grid.appendChild(k); grid.appendChild(v);
    });
    document.getElementById('details-title').textContent = el.dataset.title;
    document.getElementById('details-overlay').classList.add('open');
}
function closeDetails() { document.getElementById('details-overlay').classList.remove('open'); }
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDetails(); });
</script>

@endsection