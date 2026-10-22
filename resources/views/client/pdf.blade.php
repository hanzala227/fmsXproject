<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Report</title>
<style>
    @page { margin: 28px 30px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #334155; }
    h1 { font-size: 16px; margin: 0 0 2px; color: #1e293b; }
    h2 { font-size: 11px; margin: 16px 0 6px; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
    .muted { color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; }
    th { background: #f1f5f9; color: #475569; }
    .num { text-align: right; }
    .box td { border: 1px solid #e2e8f0; width: 25%; padding: 8px; }
    .box .l { font-size: 8px; color: #64748b; }
    .box .v { font-size: 13px; font-weight: bold; color: #1e293b; }
    .empty { text-align: center; color: #94a3b8; padding: 10px; }
    .foot { margin-top: 14px; font-size: 8px; color: #94a3b8; text-align: right; }
</style>
</head>
<body>

   @php
    $rt = $filters['report_type'] ?? null;
    if (in_array($rt, ['', 'all'], true)) $rt = null;

    $showShip  = !$rt || in_array($rt, ['shipments', 'shipment']);
    $showInv   = !$rt || in_array($rt, ['invoices', 'invoice']);
    $showQuote = !$rt || in_array($rt, ['quotations', 'quotation']);
@endphp

<h1>Account Report</h1>
<div class="muted">
    {{ $userName ?? '' }} &nbsp;|&nbsp; Generated: {{ $generatedAt->format('d M Y, H:i') }}
    @if(!empty($filters['from_date']) || !empty($filters['to_date']))
        &nbsp;|&nbsp; Period: {{ $filters['from_date'] ?? '...' }} to {{ $filters['to_date'] ?? '...' }}
    @endif
    @if(!empty($filters['status']))
        &nbsp;|&nbsp; Status: {{ $filters['status'] }}
    @endif
</div>

<h2>Summary</h2>
<table class="box">
    <tr>
        <td><div class="l">Total Shipments</div><div class="v">{{ $totalShipments ?? 0 }}</div></td>
        <td><div class="l">Total Invoices</div><div class="v">{{ $totalInvoices ?? 0 }}</div></td>
        <td><div class="l">Outstanding</div><div class="v">{{ number_format($outstandingInvoiceAmount ?? 0, 2) }}</div></td>
        <td><div class="l">Overdue</div><div class="v">{{ number_format($overdueInvoiceAmount ?? 0, 2) }}</div></td>
    </tr>
</table>

<h2>Shipment Status</h2>
<table class="box">
    <tr>
        <td><div class="l">In Transit</div><div class="v">{{ $inTransit ?? 0 }}</div></td>
        <td><div class="l">Arrived</div><div class="v">{{ $arrived ?? 0 }}</div></td>
        <td><div class="l">Delivered</div><div class="v">{{ $delivered ?? 0 }}</div></td>
        <td><div class="l">Pending</div><div class="v">{{ $pending ?? 0 }}</div></td>
    </tr>
</table>
@if($showShip)
<h2>Shipments ({{ count($shipments ?? []) }})</h2>
<table>
    <thead>
        <tr>
            <th>#</th><th>Booking No.</th><th>Type</th><th>MAWB / MBL</th><th>Flight / Vessel</th>
            <th>Voyage</th><th>Route</th><th>ETD</th><th>ETA</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($shipments ?? [] as $s)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $s->file_no }}</td>
                <td>{{ $s->type }}</td>
                <td>{{ $s->reference ?? '-' }}</td>
                <td>{{ $s->carrier_ref ?? '-' }}</td>
                <td>{{ $s->voyage ?? '-' }}</td>
                <td>{{ $s->origin ?? '-' }} &rarr; {{ $s->destination ?? '-' }}</td>
                <td>{{ $s->etd ?? '-' }}</td>
                <td>{{ $s->eta ?? '-' }}</td>
                <td>{{ $s->status ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty">No shipments found.</td></tr>
        @endforelse
    </tbody>
</table>
@endif

@if($showInv)
<h2>Invoices ({{ count($invoices ?? []) }})</h2>
<table>
    <thead>
        <tr><th>#</th><th>Invoice #</th><th>Date</th><th>Due</th><th>Status</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th></tr>
    </thead>
    <tbody>
        @forelse($invoices ?? [] as $i)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $i->invoice_no }}</td>
                <td>{{ $i->invoice_date ?? '-' }}</td>
                <td>{{ $i->due_date ?? '-' }}</td>
                <td>{{ $i->status }}</td>
                <td class="num">{{ number_format((float) $i->total_amount, 2) }}</td>
                <td class="num">{{ number_format((float) $i->paid_amount, 2) }}</td>
                <td class="num">{{ number_format((float) $i->total_amount - (float) $i->paid_amount, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No invoices found.</td></tr>
        @endforelse
    </tbody>
</table>
@endif


@if($showQuote)
<h2>Quotations ({{ count($quotations ?? []) }})</h2>
<table>
    <thead>
        <tr><th>#</th><th>Quote #</th><th>Date</th><th>Valid Till</th><th>Mode</th><th>Commodity</th><th>Status</th></tr>
    </thead>
    <tbody>
        @forelse($quotations ?? [] as $q)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $q->quote_no }}</td>
                <td>{{ $q->quote_date ? \Carbon\Carbon::parse($q->quote_date)->format('d M Y') : '-' }}</td>
                <td>{{ $q->expiry_date ? \Carbon\Carbon::parse($q->expiry_date)->format('d M Y') : '-' }}</td>
                <td>{{ $q->transport_mode ?? '-' }}</td>
                <td>{{ $q->commodity ?? '-' }}</td>
                <td>{{ $q->status }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No quotations found.</td></tr>
        @endforelse
    </tbody>
</table>
@endif

<div class="foot">This report was generated from your account data.</div>

</body>
</html>