@extends('layouts.userapp')
@section('title', 'Reports & Analytics')

@push('styles')
<style>
    /* ==========================================================================
       INTERNATIONAL FREIGHT STANDARD CLIENT PORTAL — REPORTS & ANALYTICS
       Theme: Enterprise Freight Blue / Slate / Clean Neutral (Zero tacky emojis)
       ========================================================================== */

    :root {
        --fms-navy: #0f172a;
        --fms-blue: #1d4ed8;
        --fms-blue-hover: #1e40af;
        --fms-blue-light: #eff6ff;
        --fms-slate-50: #f8fafc;
        --fms-slate-100: #f1f5f9;
        --fms-slate-200: #e2e8f0;
        --fms-slate-300: #cbd5e1;
        --fms-slate-400: #94a3b8;
        --fms-slate-600: #475569;
        --fms-slate-700: #334155;
        --fms-slate-900: #0f172a;
        --fms-emerald: #059669;
        --fms-emerald-bg: #ecfdf5;
        --fms-amber: #d97706;
        --fms-amber-bg: #fffbeb;
    }

    .fms-reports-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--fms-slate-900);
        background: transparent;
        padding-bottom: 40px;
    }

    /* Page Header Bar */
    .fms-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--fms-slate-200);
    }
    .fms-title-group h1 {
        font-size: 20px;
        font-weight: 700;
        color: var(--fms-navy);
        margin: 0 0 4px 0;
        letter-spacing: -0.01em;
    }
    .fms-title-group p {
        font-size: 13px;
        color: var(--fms-slate-600);
        margin: 0;
    }

    /* Top 3 Quick Report Navigation Cards */
    .fms-report-nav-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) {
        .fms-report-nav-grid { grid-template-columns: 1fr; }
    }
    .fms-report-nav-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .fms-report-nav-card:hover {
        border-color: var(--fms-blue);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
    }
    .fms-report-nav-card.active {
        border-color: var(--fms-blue);
        background: #fcfdfe;
        box-shadow: 0 0 0 1px var(--fms-blue);
    }
    .fms-report-nav-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .fms-report-nav-icon.ocean { background: #eff6ff; color: #1d4ed8; }
    .fms-report-nav-icon.invoice { background: #fffbeb; color: #b45309; }
    .fms-report-nav-icon.quote { background: #eef2ff; color: #4338ca; }
    .fms-report-nav-info h4 {
        font-size: 14px;
        font-weight: 700;
        color: var(--fms-navy);
        margin: 0 0 4px 0;
    }
    .fms-report-nav-info p {
        font-size: 12px;
        color: var(--fms-slate-600);
        margin: 0 0 8px 0;
        line-height: 1.4;
    }
    .fms-report-nav-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--fms-blue);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Top 4 KPI Metrics */
    .fms-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .fms-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .fms-kpi-grid { grid-template-columns: 1fr; }
    }
    .fms-kpi-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .fms-kpi-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
    }
    .fms-kpi-info h3 {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fms-slate-600);
        margin: 0 0 6px 0;
    }
    .fms-kpi-info .fms-kpi-val {
        font-size: 22px;
        font-weight: 700;
        color: var(--fms-navy);
        line-height: 1;
        margin: 0 0 4px 0;
    }
    .fms-kpi-info .fms-kpi-sub {
        font-size: 11px;
        color: var(--fms-slate-400);
    }
    .fms-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--fms-slate-100);
        color: var(--fms-slate-700);
    }
    .fms-kpi-icon.ocean { background: #eff6ff; color: #1d4ed8; }
    .fms-kpi-icon.amber { background: #fffbeb; color: #b45309; }
    .fms-kpi-icon.red { background: #fef2f2; color: #dc2626; }

    /* Filter & Controls Panel */
    .fms-controls-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .fms-filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-bottom: 1px solid var(--fms-slate-200);
        background: #f8fafc;
    }
    .fms-filter-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fms-slate-700);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fms-filter-body {
        padding: 16px 18px;
    }
    .fms-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }
    @media (max-width: 900px) {
        .fms-filter-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 550px) {
        .fms-filter-grid { grid-template-columns: 1fr; }
    }
    .fms-filter-field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--fms-slate-600);
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .fms-filter-input {
        width: 100%;
        height: 36px;
        padding: 6px 10px;
        font-size: 13px;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        background: #ffffff;
        color: var(--fms-slate-900);
        outline: none;
    }
    .fms-filter-input:focus {
        border-color: var(--fms-blue);
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }
    .fms-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
    }
    .fms-btn-action {
        height: 36px;
        padding: 0 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .fms-btn-action.primary {
        background: var(--fms-blue);
        color: #ffffff;
        border: 1px solid var(--fms-blue);
    }
    .fms-btn-action.primary:hover {
        background: var(--fms-blue-hover);
    }
    .fms-btn-action.secondary {
        background: #ffffff;
        color: var(--fms-slate-700);
        border: 1px solid var(--fms-slate-200);
    }
    .fms-btn-action.secondary:hover {
        background: var(--fms-slate-100);
    }
    .fms-btn-action.emerald {
        background: #059669;
        color: #ffffff;
        border: 1px solid #059669;
    }
    .fms-btn-action.emerald:hover {
        background: #047857;
    }

    /* Section Data Tables */
    .fms-section-card {
        background: #ffffff;
        border: 1px solid var(--fms-slate-200);
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .fms-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-bottom: 1px solid var(--fms-slate-200);
        background: #f8fafc;
    }
    .fms-section-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--fms-navy);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fms-badge-count {
        background: var(--fms-slate-100);
        color: var(--fms-slate-700);
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
    }
    .fms-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }
    .fms-table thead th {
        background: #f8fafc;
        padding: 12px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fms-slate-600);
        border-bottom: 1px solid var(--fms-slate-200);
        white-space: nowrap;
    }
    .fms-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: var(--fms-slate-700);
    }
    .fms-table tbody tr:hover {
        background: #f8fafc;
    }
    .fms-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges & Pills */
    .fms-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    .fms-badge-status.in-transit { background: var(--fms-blue-light); color: var(--fms-blue); border: 1px solid #bfdbfe; }
    .fms-badge-status.arrived    { background: var(--fms-emerald-bg); color: var(--fms-emerald); border: 1px solid #a7f3d0; }
    .fms-badge-status.delivered  { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .fms-badge-status.pending    { background: var(--fms-amber-bg); color: var(--fms-amber); border: 1px solid #fde68a; }

    /* Shipment Status Progress Matrix */
    .fms-status-matrix {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        padding: 18px;
        background: #ffffff;
    }
    @media (max-width: 900px) {
        .fms-status-matrix { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 550px) {
        .fms-status-matrix { grid-template-columns: 1fr; }
    }
    .fms-matrix-card {
        background: #f8fafc;
        border: 1px solid var(--fms-slate-200);
        border-radius: 6px;
        padding: 12px 14px;
    }
    .fms-matrix-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        color: var(--fms-slate-700);
        margin-bottom: 8px;
    }
    .fms-matrix-top strong {
        font-size: 16px;
        color: var(--fms-navy);
    }
    .fms-progress-bar-bg {
        width: 100%;
        height: 6px;
        background: var(--fms-slate-200);
        border-radius: 3px;
        overflow: hidden;
    }
    .fms-progress-bar-fill {
        height: 100%;
        background: var(--fms-blue);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
</style>
@endpush

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

<div class="fms-reports-page">

    <!-- Header Bar -->
    <div class="fms-header-bar">
        <div class="fms-title-group">
            <h1>Reports & Freight Analytics</h1>
            <p>Comprehensive activity, financial ledger, and operational summaries for your company account.</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <button type="button" class="fms-btn-action emerald" onclick="generatePdfReport()">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF Report
            </button>
            <div class="fms-meta-tag" style="background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:6px 12px; font-size:12px;">
                <i class="bi bi-building"></i>
                <span>Account: <strong>{{ auth()->user()->company->name ?? 'Customer Account' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Top 3 Quick Navigation Cards -->
    <div class="fms-report-nav-grid">
        <a class="fms-report-nav-card {{ $rt === 'shipments' ? 'active' : '' }}" href="{{ route('client.reports.index', ['report_type' => 'shipments']) }}">
            <div class="fms-report-nav-icon ocean">
                <i class="bi bi-water"></i>
            </div>
            <div class="fms-report-nav-info">
                <h4>Shipments Activity Report</h4>
                <p>Complete multi-modal corridor movement, routing, vessel schedule, and delivery milestones.</p>
                <span class="fms-report-nav-link">View Section <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>

        <a class="fms-report-nav-card {{ $rt === 'invoices' ? 'active' : '' }}" href="{{ route('client.reports.index', ['report_type' => 'invoices']) }}">
            <div class="fms-report-nav-icon invoice">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="fms-report-nav-info">
                <h4>Invoices & Financial Report</h4>
                <p>Accounts receivable billing statements, paid collections, and outstanding ledger balance.</p>
                <span class="fms-report-nav-link">View Section <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>

        <a class="fms-report-nav-card {{ $rt === 'quotations' ? 'active' : '' }}" href="{{ route('client.reports.index', ['report_type' => 'quotations']) }}">
            <div class="fms-report-nav-icon quote">
                <i class="bi bi-file-earmark-check"></i>
            </div>
            <div class="fms-report-nav-info">
                <h4>Quotations & Rates Report</h4>
                <p>Historical commercial rates, active freight quotes, port validity periods, and terms.</p>
                <span class="fms-report-nav-link">View Section <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>

    <!-- 4 Executive KPI Metric Cards -->
    <div class="fms-kpi-grid">
        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Total Tracked Shipments</h3>
                <div class="fms-kpi-val">{{ $totalShipments ?? 0 }}</div>
                <div class="fms-kpi-sub">Active & historical movements</div>
            </div>
            <div class="fms-kpi-icon ocean">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>

        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Total Invoices</h3>
                <div class="fms-kpi-val">{{ $totalInvoices ?? 0 }}</div>
                <div class="fms-kpi-sub">Issued AR invoices</div>
            </div>
            <div class="fms-kpi-icon">
                <i class="bi bi-receipt"></i>
            </div>
        </div>

        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Outstanding Balance</h3>
                <div class="fms-kpi-val">{{ number_format($outstandingInvoiceAmount ?? 0, 2) }}</div>
                <div class="fms-kpi-sub">Remaining balance due</div>
            </div>
            <div class="fms-kpi-icon amber">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <div class="fms-kpi-card">
            <div class="fms-kpi-info">
                <h3>Overdue Invoices</h3>
                <div class="fms-kpi-val">{{ number_format($overdueInvoiceAmount ?? 0, 2) }}</div>
                <div class="fms-kpi-sub">Past payment due date</div>
            </div>
            <div class="fms-kpi-icon red">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Filters Control Panel -->
    <div class="fms-controls-card">
        <div class="fms-filter-header">
            <div class="fms-filter-title">
                <i class="bi bi-funnel"></i> Report Filter Criteria
            </div>
            <div style="font-size:12px; color:var(--fms-slate-600);">
                Select parameters to refine list or export
            </div>
        </div>
        <div class="fms-filter-body">
            <form method="GET" action="{{ url()->current() }}">
                <div class="fms-filter-grid">
                    <div class="fms-filter-field">
                        <label>Report Focus</label>
                        <select name="report_type" class="fms-filter-input">
                            <option value="">All Sections (Shipments, Invoices, Quotes)</option>
                            <option value="shipments" @selected(request('report_type') === 'shipments')>Shipments Only</option>
                            <option value="invoices" @selected(request('report_type') === 'invoices')>Invoices Only</option>
                            <option value="quotations" @selected(request('report_type') === 'quotations')>Quotations Only</option>
                        </select>
                    </div>

                    <div class="fms-filter-field">
                        <label>Date From</label>
                        <input type="date" name="from_date" class="fms-filter-input" value="{{ request('from_date') }}">
                    </div>

                    <div class="fms-filter-field">
                        <label>Date To</label>
                        <input type="date" name="to_date" class="fms-filter-input" value="{{ request('to_date') }}">
                    </div>

                    <div class="fms-filter-field">
                        <label>Status Filter</label>
                        <select name="status" class="fms-filter-input">
                            <option value="">All Statuses</option>
                            @foreach(['in_transit' => 'In Transit', 'arrived' => 'Arrived at Port', 'delivered' => 'Delivered', 'pending' => 'Pending / Booking', 'paid' => 'Invoices Paid', 'overdue' => 'Invoices Overdue'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="fms-filter-actions">
                    <a href="{{ route('client.reports.index') }}" class="fms-btn-action secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                    <button type="submit" class="fms-btn-action primary">
                        <i class="bi bi-check2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section 1: Shipments Report -->
    @if($showShip)
    <div class="fms-section-card">
        <div class="fms-section-header">
            <div class="fms-section-title">
                <i class="bi bi-ship" style="color:var(--fms-blue);"></i>
                Shipments Activity List
                <span class="fms-badge-count">{{ $totalShipments ?? 0 }}</span>
            </div>
            <label style="font-size:12px; font-weight:600; color:var(--fms-slate-700); cursor:pointer; display:flex; align-items:center; gap:6px;">
                <input type="radio" name="pdf_report_type" value="shipments" checked> Select for PDF export
            </label>
        </div>

        <div style="overflow-x: auto;">
            <table class="fms-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th style="width: 170px;">Booking / File No</th>
                        <th style="width: 130px;">Mode</th>
                        <th style="min-width: 160px;">Master Reference</th>
                        <th style="min-width: 200px;">Route (POL &rarr; POD)</th>
                        <th style="width: 110px;">ETD</th>
                        <th style="width: 110px;">ETA</th>
                        <th style="width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments ?? [] as $s)
                        <tr>
                            <td style="text-align: center; color: var(--fms-slate-400);">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('client.shipments') }}" style="font-weight: 700; color: var(--fms-blue); text-decoration: none;">
                                    {{ $s->file_no }}
                                </a>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--fms-slate-800);">{{ $s->type }}</span>
                            </td>
                            <td>
                                <span style="font-family: ui-monospace, monospace; color: var(--fms-slate-600);">{{ $s->reference ?? '-' }}</span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:6px; font-weight:700; color:var(--fms-slate-900);">
                                    <span>{{ $s->origin ?? '-' }}</span>
                                    <i class="bi bi-arrow-right" style="color:var(--fms-slate-400); font-size:12px;"></i>
                                    <span>{{ $s->destination ?? '-' }}</span>
                                </div>
                            </td>
                            <td>{{ $s->etd ?? '-' }}</td>
                            <td><strong style="color: var(--fms-navy);">{{ $s->eta ?? '-' }}</strong></td>
                            <td>
                                @php
                                    $st = strtolower($s->status ?? 'in transit');
                                    $badgeClass = 'in-transit';
                                    if(str_contains($st, 'arrived')) $badgeClass = 'arrived';
                                    elseif(str_contains($st, 'delivered')) $badgeClass = 'delivered';
                                    elseif(str_contains($st, 'pending') || str_contains($st, 'booking')) $badgeClass = 'pending';
                                @endphp
                                <span class="fms-badge-status {{ $badgeClass }}">
                                    <i class="bi bi-dot" style="font-size: 16px; margin: -4px;"></i> {{ $s->status ?? 'In Transit' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px; color: var(--fms-slate-500);">
                                No shipments found matching the specified report criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Shipment Status Breakdown Progress Matrix -->
        <div style="border-top: 1px solid var(--fms-slate-200); background: #f8fafc; padding: 12px 18px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--fms-slate-600);">
            <i class="bi bi-bar-chart"></i> Operational Movement Status Breakdown
        </div>
        <div class="fms-status-matrix">
            @foreach(['In Transit' => $inTransit ?? 0, 'Arrived at Port' => $arrived ?? 0, 'Delivered' => $delivered ?? 0, 'Pending / Booking' => $pending ?? 0] as $label => $n)
                <div class="fms-matrix-card">
                    <div class="fms-matrix-top">
                        <span>{{ $label }}</span>
                        <strong>{{ $n }}</strong>
                    </div>
                    <div class="fms-progress-bar-bg">
                        <div class="fms-progress-bar-fill" style="width: {{ $pct($n) }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Section 2: Invoices Report -->
    @if($showInv)
    <div class="fms-section-card">
        <div class="fms-section-header">
            <div class="fms-section-title">
                <i class="bi bi-receipt-cutoff" style="color:#d97706;"></i>
                Invoices & Billing Statement
                <span class="fms-badge-count">{{ count($invoices ?? []) }}</span>
            </div>
            <label style="font-size:12px; font-weight:600; color:var(--fms-slate-700); cursor:pointer; display:flex; align-items:center; gap:6px;">
                <input type="radio" name="pdf_report_type" value="invoices"> Select for PDF export
            </label>
        </div>

        <div style="overflow-x: auto;">
            <table class="fms-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>Invoice Number</th>
                        <th>Invoice Date</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total Amount</th>
                        <th style="text-align: right;">Paid Amount</th>
                        <th style="text-align: right;">Balance Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices ?? [] as $invoice)
                        <tr>
                            <td style="text-align: center; color: var(--fms-slate-400);">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('cp.invoices.show', $invoice->id) }}" style="font-weight: 700; color: var(--fms-blue); text-decoration: none;">
                                    {{ $invoice->invoice_no }}
                                </a>
                            </td>
                            <td>{{ $fmtDate($invoice->invoice_date) }}</td>
                            <td>
                                <span class="fms-badge-status {{ strtolower($invoice->status) === 'paid' ? 'arrived' : 'pending' }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                            <td style="text-align: right; color: #059669; font-weight: 600;">{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--fms-navy);">{{ number_format($invoice->balance_amount ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: var(--fms-slate-500);">
                                No invoices found matching the specified report criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Section 3: Quotations Report -->
    @if($showQuote)
    <div class="fms-section-card">
        <div class="fms-section-header">
            <div class="fms-section-title">
                <i class="bi bi-file-earmark-check" style="color:#4f46e5;"></i>
                Quotations & Rate History
                <span class="fms-badge-count">{{ count($quotations ?? []) }}</span>
            </div>
            <label style="font-size:12px; font-weight:600; color:var(--fms-slate-700); cursor:pointer; display:flex; align-items:center; gap:6px;">
                <input type="radio" name="pdf_report_type" value="quotations"> Select for PDF export
            </label>
        </div>

        <div style="overflow-x: auto;">
            <table class="fms-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>Quotation #</th>
                        <th>Quote Date</th>
                        <th>Valid Until</th>
                        <th>Transport Mode</th>
                        <th>Commodity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations ?? [] as $q)
                        <tr>
                            <td style="text-align: center; color: var(--fms-slate-400);">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('cp.quotes.show', $q->id) }}" style="font-weight: 700; color: var(--fms-blue); text-decoration: none;">
                                    {{ $q->quote_no }}
                                </a>
                            </td>
                            <td>{{ $fmtDate($q->quote_date) }}</td>
                            <td>{{ $fmtDate($q->expiry_date) }}</td>
                            <td>{{ $q->transport_mode ?? '-' }}</td>
                            <td>{{ $q->commodity ?? '-' }}</td>
                            <td>
                                <span class="fms-badge-status in-transit">
                                    {{ $q->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: var(--fms-slate-500);">
                                No quotations found matching the specified report criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    const pdfBaseUrl = @json(route('client.reports.pdf'));
    const currentFilters = @json(request()->only(['from_date', 'to_date', 'status']));

    function generatePdfReport() {
        const sel = document.querySelector('input[name="pdf_report_type"]:checked');
        const reportVal = sel ? sel.value : 'all';
        const params = new URLSearchParams(currentFilters);
        params.set('report_type', reportVal);
        window.location.href = pdfBaseUrl + '?' + params.toString();
    }
</script>
@endpush

@endsection
