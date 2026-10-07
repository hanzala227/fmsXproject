@extends('layouts.userapp')

@section('content')

<div class="reports-page">

```
{{-- Page Header --}}
<div class="reports-header">
    <div>
        <h1>Reports</h1>
        <p>View and manage your business reports</p>
    </div>

    <div class="header-actions">
        <button type="button" class="filter-btn" onclick="toggleFilters()">
            ☰ Filters
        </button>
    </div>
</div>


{{-- Filters --}}
<div class="filters-box" id="filtersBox">

    <form method="GET" action="{{ url()->current() }}">

        <div class="filter-grid">

            <div class="filter-field">
                <label>Report Type</label>

                <select name="report_type">
                    <option value="">All Reports</option>
                    <option value="shipments"
                        {{ request('report_type') == 'shipments' ? 'selected' : '' }}>
                        Shipments
                    </option>

                    <option value="invoices"
                        {{ request('report_type') == 'invoices' ? 'selected' : '' }}>
                        Invoices
                    </option>

                    <option value="quotations"
                        {{ request('report_type') == 'quotations' ? 'selected' : '' }}>
                        Quotations
                    </option>

                    <option value="payments"
                        {{ request('report_type') == 'payments' ? 'selected' : '' }}>
                        Payments
                    </option>
                </select>
            </div>


            <div class="filter-field">
                <label>From Date</label>

                <input
                    type="date"
                    name="from_date"
                    value="{{ request('from_date') }}"
                >
            </div>


            <div class="filter-field">
                <label>To Date</label>

                <input
                    type="date"
                    name="to_date"
                    value="{{ request('to_date') }}"
                >
            </div>


            <div class="filter-field">
                <label>Status</label>

                <select name="status">

                    <option value="">All Statuses</option>

                    <option value="pending"
                        {{ request('status') == 'pending' ? 'selected' : '' }}>
                        Pending
                    </option>

                    <option value="in_transit"
                        {{ request('status') == 'in_transit' ? 'selected' : '' }}>
                        In Transit
                    </option>

                    <option value="arrived"
                        {{ request('status') == 'arrived' ? 'selected' : '' }}>
                        Arrived
                    </option>

                    <option value="delivered"
                        {{ request('status') == 'delivered' ? 'selected' : '' }}>
                        Delivered
                    </option>

                    <option value="paid"
                        {{ request('status') == 'paid' ? 'selected' : '' }}>
                        Paid
                    </option>

                    <option value="overdue"
                        {{ request('status') == 'overdue' ? 'selected' : '' }}>
                        Overdue
                    </option>

                </select>
            </div>


            <div class="filter-actions">

                <button type="submit" class="apply-btn">
                    Apply Filters
                </button>

                <a href="{{ url()->current() }}" class="reset-btn">
                    Reset
                </a>

            </div>

        </div>

    </form>

</div>


{{-- Report Cards --}}
<div class="report-cards">

    {{-- Shipment Report --}}
    <div class="report-card">

        <div class="report-icon shipment-icon">
            🚢
        </div>

        <div class="report-card-content">

            <h3>Shipment Report</h3>

            <p>
                View your shipment activity, status,
                movement and delivery information.
            </p>

            <a href="#" class="report-link">
                View Report →
            </a>

        </div>

    </div>


    {{-- Invoice Report --}}
    <div class="report-card">

        <div class="report-icon invoice-icon">
            $
        </div>

        <div class="report-card-content">

            <h3>Invoice Report</h3>

            <p>
                Review invoices, payments, outstanding
                balances and overdue amounts.
            </p>

            <a href="#" class="report-link">
                View Report →
            </a>

        </div>

    </div>


    {{-- Quotation Report --}}
    <div class="report-card">

        <div class="report-icon quotation-icon">
            #
        </div>

        <div class="report-card-content">

            <h3>Quotation Report</h3>

            <p>
                Check your quotation history, quotation
                status and pricing information.
            </p>

            <a href="#" class="report-link">
                View Report →
            </a>

        </div>

    </div>


    {{-- Payment Report --}}
    <div class="report-card">

        <div class="report-icon payment-icon">
            ✓
        </div>

        <div class="report-card-content">

            <h3>Payment Report</h3>

            <p>
                View your payment history and
                outstanding payment information.
            </p>

            <a href="#" class="report-link">
                View Report →
            </a>

        </div>

    </div>

</div>


{{-- Summary --}}
<div class="section-heading">

    <div>
        <h2>Report Summary</h2>
        <p>Overview of your account activity</p>
    </div>

</div>


<div class="summary-grid">

    <div class="summary-card">

        <span class="summary-label">
            Total Shipments
        </span>

        <strong class="summary-number">
            {{ $totalShipments ?? 0 }}
        </strong>

        <span class="summary-description">
            All shipments
        </span>

    </div>


    <div class="summary-card">

        <span class="summary-label">
            Total Invoices
        </span>

        <strong class="summary-number">
            {{ $totalInvoices ?? 0 }}
        </strong>

        <span class="summary-description">
            Generated invoices
        </span>

    </div>


    <div class="summary-card">

        <span class="summary-label">
            Outstanding
        </span>

        <strong class="summary-number">
            {{ number_format($outstandingInvoiceAmount ?? 0, 2) }}
        </strong>

        <span class="summary-description">
            Remaining balance
        </span>

    </div>


    <div class="summary-card">

        <span class="summary-label">
            Overdue
        </span>

        <strong class="summary-number">
            {{ number_format($overdueInvoiceAmount ?? 0, 2) }}
        </strong>

        <span class="summary-description">
            Overdue amount
        </span>

    </div>

</div>


{{-- Report Activity --}}
<div class="report-section">

    <div class="section-heading">

        <div>
            <h2>Recent Report Activity</h2>
            <p>Your latest shipment and financial activity</p>
        </div>

    </div>


    <div class="activity-table-wrapper">

        <table class="activity-table">

            <thead>

                <tr>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                {{-- Dynamic invoices --}}
                @forelse($invoices ?? [] as $invoice)

                    <tr>

                        <td>
                            <strong>
                                {{ $invoice->invoice_no ?? 'N/A' }}
                            </strong>
                        </td>

                        <td>
                            Invoice
                        </td>

                        <td>
                            {{ $invoice->invoice_date
                                ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')
                                : 'N/A'
                            }}
                        </td>

                        <td>

                            <span class="status-badge
                                {{ strtolower($invoice->status ?? '') == 'paid'
                                    ? 'status-paid'
                                    : 'status-pending'
                                }}">

                                {{ ucfirst($invoice->status ?? 'Pending') }}

                            </span>

                        </td>

                        <td>
                            {{ number_format($invoice->total_amount ?? 0, 2) }}
                        </td>

                        <td>

                            <a href="#" class="view-btn">
                                View
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="empty-state">

                            <div class="empty-icon">
                                —
                            </div>

                            <strong>No report activity</strong>

                            <p>
                                There is no report data available yet.
                            </p>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- Shipment Status --}}
<div class="report-section">

    <div class="section-heading">

        <div>
            <h2>Shipment Status</h2>
            <p>Current status of your shipments</p>
        </div>

    </div>


    <div class="status-report-grid">

        <div class="status-report-card">

            <div class="status-top">
                <span>In Transit</span>

                <strong>
                    {{ $inTransit ?? 0 }}
                </strong>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    style="width:
                    {{
                        ($totalShipments ?? 0) > 0
                        ? (($inTransit ?? 0) / $totalShipments) * 100
                        : 0
                    }}%">
                </div>
            </div>

        </div>


        <div class="status-report-card">

            <div class="status-top">
                <span>Arrived</span>

                <strong>
                    {{ $arrived ?? 0 }}
                </strong>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    style="width:
                    {{
                        ($totalShipments ?? 0) > 0
                        ? (($arrived ?? 0) / $totalShipments) * 100
                        : 0
                    }}%">
                </div>
            </div>

        </div>


        <div class="status-report-card">

            <div class="status-top">
                <span>Delivered</span>

                <strong>
                    {{ $delivered ?? 0 }}
                </strong>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    style="width:
                    {{
                        ($totalShipments ?? 0) > 0
                        ? (($delivered ?? 0) / $totalShipments) * 100
                        : 0
                    }}%">
                </div>
            </div>

        </div>


        <div class="status-report-card">

            <div class="status-top">
                <span>Pending</span>

                <strong>
                    {{ $pending ?? 0 }}
                </strong>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    style="width:
                    {{
                        ($totalShipments ?? 0) > 0
                        ? (($pending ?? 0) / $totalShipments) * 100
                        : 0
                    }}%">
                </div>
            </div>

        </div>

    </div>

</div>


{{-- Export Section --}}
<div class="export-section">

    <div>

        <h3>Need a detailed report?</h3>

        <p>
            Select your report type and date range,
            then generate your report.
        </p>

    </div>

    <div class="export-actions">

        <button type="button" class="export-btn">
            Export PDF
        </button>

        <button type="button" class="export-btn secondary">
            Export Excel
        </button>

    </div>

</div>
```

</div>

<style>

/* ==============================
   REPORTS PAGE
============================== */

.reports-page {
    padding: 25px;
    background: #f7f8f6;
    min-height: calc(100vh - 80px);
}


/* ==============================
   HEADER
============================== */

.reports-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.reports-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #1d4533;
}

.reports-header p {
    margin: 6px 0 0;
    color: #777;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.filter-btn {
    border: 1px solid #1d4533;
    background: white;
    color: #1d4533;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}


/* ==============================
   FILTERS
============================== */

.filters-box {
    background: white;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr) auto;
    gap: 15px;
    align-items: end;
}

.filter-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 7px;
    color: #444;
}

.filter-field input,
.filter-field select {
    width: 100%;
    height: 42px;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 0 12px;
    background: white;
    outline: none;
}

.filter-field input:focus,
.filter-field select:focus {
    border-color: #1d4533;
}

.filter-actions {
    display: flex;
    gap: 8px;
}

.apply-btn,
.reset-btn {
    height: 42px;
    padding: 0 16px;
    border-radius: 5px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 600;
}

.apply-btn {
    border: none;
    background: #1d4533;
    color: white;
}

.reset-btn {
    border: 1px solid #ddd;
    color: #555;
    background: white;
}


/* ==============================
   REPORT CARDS
============================== */

.report-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 35px;
}

.report-card {
    background: white;
    border: 1px solid #e4e4e4;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    gap: 15px;
    transition: 0.2s;
}

.report-card:hover {
    transform: translateY(-2px);
    border-color: #1d4533;
}

.report-icon {
    width: 45px;
    height: 45px;
    min-width: 45px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.shipment-icon {
    background: #eaf2ed;
}

.invoice-icon {
    background: #f7eee8;
}

.quotation-icon {
    background: #f0eee8;
}

.payment-icon {
    background: #e8f1ee;
}

.report-card-content h3 {
    margin: 0 0 8px;
    font-size: 17px;
    color: #222;
}

.report-card-content p {
    margin: 0 0 12px;
    color: #777;
    font-size: 13px;
    line-height: 1.5;
}

.report-link {
    color: #1d4533;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}


/* ==============================
   SECTION HEADING
============================== */

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.section-heading h2 {
    margin: 0;
    font-size: 21px;
    color: #222;
}

.section-heading p {
    margin: 5px 0 0;
    color: #777;
    font-size: 13px;
}


/* ==============================
   SUMMARY
============================== */

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 35px;
}

.summary-card {
    background: white;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
}

.summary-label {
    display: block;
    color: #777;
    font-size: 13px;
    margin-bottom: 8px;
}

.summary-number {
    display: block;
    color: #1d4533;
    font-size: 25px;
    margin-bottom: 5px;
}

.summary-description {
    color: #999;
    font-size: 12px;
}


/* ==============================
   REPORT SECTION
============================== */

.report-section {
    background: white;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 22px;
    margin-bottom: 30px;
}


/* ==============================
   TABLE
============================== */

.activity-table-wrapper {
    overflow-x: auto;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table th {
    text-align: left;
    padding: 13px;
    font-size: 12px;
    color: #777;
    background: #fafafa;
    border-bottom: 1px solid #eee;
}

.activity-table td {
    padding: 15px 13px;
    border-bottom: 1px solid #eee;
    font-size: 13px;
    color: #444;
}

.activity-table tbody tr:hover {
    background: #fafcfb;
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.status-paid {
    background: #e5f2e9;
    color: #1d4533;
}

.status-pending {
    background: #fff1df;
    color: #8a5b20;
}

.view-btn {
    text-decoration: none;
    color: #1d4533;
    font-weight: 700;
}


/* ==============================
   EMPTY
============================== */

.empty-state {
    text-align: center !important;
    padding: 50px 20px !important;
}

.empty-icon {
    font-size: 28px;
    color: #aaa;
    margin-bottom: 10px;
}

.empty-state strong {
    display: block;
    color: #444;
}

.empty-state p {
    color: #999;
    margin-top: 5px;
}


/* ==============================
   STATUS REPORT
============================== */

.status-report-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.status-report-card {
    border: 1px solid #eee;
    padding: 18px;
    border-radius: 7px;
}

.status-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.status-top span {
    color: #555;
    font-size: 13px;
}

.status-top strong {
    color: #1d4533;
    font-size: 18px;
}

.progress {
    height: 7px;
    background: #eee;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #1d4533;
    border-radius: 10px;
}


/* ==============================
   EXPORT
============================== */

.export-section {
    background: #1d4533;
    color: white;
    border-radius: 8px;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.export-section h3 {
    margin: 0 0 7px;
    font-size: 19px;
}

.export-section p {
    margin: 0;
    font-size: 13px;
    opacity: 0.8;
}

.export-actions {
    display: flex;
    gap: 10px;
}

.export-btn {
    border: none;
    background: white;
    color: #1d4533;
    padding: 11px 18px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 700;
}

.export-btn.secondary {
    background: transparent;
    border: 1px solid white;
    color: white;
}


/* ==============================
   RESPONSIVE
============================== */

@media (max-width: 1100px) {

    .filter-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .report-cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .status-report-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media (max-width: 650px) {

    .reports-page {
        padding: 15px;
    }

    .reports-header {
        align-items: flex-start;
        gap: 15px;
    }

    .reports-header h1 {
        font-size: 23px;
    }

    .filter-grid {
        grid-template-columns: 1fr;
    }

    .report-cards {
        grid-template-columns: 1fr;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .status-report-grid {
        grid-template-columns: 1fr;
    }

    .export-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .export-actions {
        width: 100%;
        flex-direction: column;
    }

    .export-btn {
        width: 100%;
    }

}

</style>

<script>

function toggleFilters()
{
    const filtersBox = document.getElementById('filtersBox');

    if (filtersBox.style.display === 'none') {
        filtersBox.style.display = 'block';
    } else {
        filtersBox.style.display = 'none';
    }
}

</script>

@endsection
