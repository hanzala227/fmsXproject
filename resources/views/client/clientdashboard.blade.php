@extends('layouts.userapp')

@section('title', 'Client Dashboard')

@section('content')

{{-- =========================================================
     Recent Shipments ke liye local CSS (sirf is page ke liye)
     Naam "rs-" / "sd-" se shuru hote hain taake baqi theme se na takraein.
========================================================= --}}
<style>
    /* ---- Recent shipments table cleanup ---- */
    .rs-table { width: 100%; border-collapse: collapse; }
    .rs-table thead th {
        font-size: 12px; font-weight: 600; color: #64748b; text-align: left;
        padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .rs-table tbody td {
        padding: 12px 14px; font-size: 13px; color: #334155;
        border-bottom: 1px solid #f1f5f9; vertical-align: middle; white-space: nowrap;
    }
    .rs-table tbody tr:last-child td { border-bottom: 0; }
    .rs-table tbody tr:hover { background: #f8fafc; }
    .rs-muted { color: #94a3b8; }

    .rs-file {
        font-weight: 600; color: #1d6bf3; text-decoration: none;
        border-bottom: 1px dashed rgba(29, 107, 243, .45); cursor: pointer;
    }
    .rs-file:hover { color: #1553c0; border-bottom-style: solid; }
    .rs-file:focus-visible, .rs-view:focus-visible, .sd-close:focus-visible {
        outline: 2px solid #1d6bf3; outline-offset: 2px; border-radius: 4px;
    }

    .rs-mode { display: inline-flex; align-items: center; gap: 6px; }
    .rs-mode i { color: #1d6bf3; font-size: 15px; }

    .rs-route { display: inline-flex; align-items: center; gap: 6px; }

    .rs-view {
        width: 30px; height: 30px; border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; color: #475569; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .rs-view:hover { background: #eff6ff; color: #1d6bf3; border-color: #bfdbfe; }

    /* ---- Details modal ---- */
    .sd-overlay {
        position: fixed; inset: 0; z-index: 9999; display: none;
        align-items: center; justify-content: center; padding: 16px;
        background: rgba(15, 23, 42, .55);
    }
    .sd-overlay.open { display: flex; }
    .sd-box {
        width: 100%; max-width: 560px; max-height: 90vh; overflow: auto;
        background: #fff; border-radius: 14px; box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
        animation: sdIn .18s ease-out;
    }
    @keyframes sdIn { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .sd-box { animation: none; } }

    .sd-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid #e2e8f0;
    }
    .sd-title { font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .sd-title i { color: #1d6bf3; }
    .sd-close {
        border: 0; background: transparent; font-size: 18px; color: #64748b;
        cursor: pointer; width: 32px; height: 32px; border-radius: 8px;
    }
    .sd-close:hover { background: #f1f5f9; color: #0f172a; }

    .sd-body { padding: 8px 20px 20px; }
    .sd-grid { display: grid; grid-template-columns: 140px 1fr; }
    .sd-grid .k, .sd-grid .v { padding: 11px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .sd-grid .k { color: #64748b; font-weight: 600; }
    .sd-grid .v { color: #0f172a; word-break: break-word; }
    .sd-grid .k:nth-last-child(2), .sd-grid .v:last-child { border-bottom: 0; }

    @media (max-width: 480px) {
        .sd-grid { grid-template-columns: 110px 1fr; }
    }
</style>


{{-- =========================================================
     DETAILS MODAL (File number click par yahi khulta hai)
========================================================= --}}
<div class="sd-overlay" id="sd-overlay" onclick="if(event.target===this) closeShipmentDetails()">
    <div class="sd-box" role="dialog" aria-modal="true" aria-labelledby="sd-title">
        <div class="sd-head">
            <div class="sd-title">
                <i class="bi bi-box-seam"></i>
                <span id="sd-title">Shipment Details</span>
            </div>
            <button type="button" class="sd-close" onclick="closeShipmentDetails()" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="sd-body">
            <div class="sd-grid" id="sd-grid"></div>
        </div>
    </div>
</div>


{{-- =========================================================
     HERO
========================================================= --}}
<section class="hero"
    style="margin-bottom: 10px;">

    <h1>Welcome back, {{ $loggedInUser->name ?? 'Customer' }}!</h1>

    <p>
        Track your shipments, manage documents, and stay updated<br>
        with your freight activities.
    </p>

    <div class="hero-btns">
        <a href="{{ route('client.shipments') }}" class=""><i class=""></i> Request Booking</a>
        <a href="{{ route('client.tracking') }}" class=""><i class=""></i> Track Shipment</a>
    </div>
</section>


{{-- =========================================================
     STAT CARDS
========================================================= --}}
<section class="stats">

    <div class="stat">
        <span class="ico blue"><i class="bi bi-box-seam"></i></span>
        <div>
            <small>Total Shipments</small>
            <div class="num">{{ $totalShipments }}</div>
            <small class="muted">All Shipments</small>
        </div>
    </div>

    <div class="stat">
        <span class="ico blue"><i class="bi bi-truck"></i></span>
        <div>
            <small>In Transit</small>
            <div class="num">{{ $inTransit }}</div>
            <small class="muted">Active Shipments</small>
        </div>
    </div>

    <div class="stat">
        <span class="ico blue"><i class="bi bi-browser-safari"></i></span>
        <div>
            <small>Arrived</small>
            <div class="num">{{ $arrived }}</div>
            <small class="muted">At Destination</small>
        </div>
    </div>

    <div class="stat">
        <span class="ico green"><i class="bi bi-check-circle"></i></span>
        <div>
            <small>Delivered</small>
            <div class="num">{{ $delivered }}</div>
            <small class="muted">Completed</small>
        </div>
    </div>

    <div class="stat">
        <span class="ico red"><i class="bi bi-file-earmark-text"></i></span>
        <div>
            <small>Pending Shipments</small>
            <div class="num">{{ $pending }}</div>
            <small class="muted">Action Required</small>
        </div>
    </div>

    <div class="stat">
        <span class="ico orange"><i class="bi bi-receipt"></i></span>
        <div>
            <small>Outstanding Invoices</small>
            <div class="num">{{ number_format($outstandingInvoiceAmount ?? 0, 2) }}</div>
            <small class="muted">{{ $invoices->where('balance_amount', '>', 0)->count() }} Invoices</small>
        </div>
    </div>

</section>


{{-- =========================================================
     MAIN GRID
========================================================= --}}
<div class="grid-2">

    <div class="col">

        {{-- =================================================
             RECENT SHIPMENTS
        ================================================== --}}
        <section class="card">

            <div class="card-head">
                <h3>Recent Shipments</h3>
                <a href="{{ route('client.shipments') }}">View All</a>
            </div>

            <div class="table-wrap">
                <table class="rs-table">
                    <thead>
                        <tr>
                            <th>Booking No</th>
                            <th>Mode</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>ETD</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th style="width:50px;text-align:center;"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentShipments as $shipment)

                            @php
                                $shipmentStatus = strtolower($shipment->status ?? 'pending');

                                $statusClass = match ($shipmentStatus) {
                                    'delivered', 'completed', 'complete' => 'green',
                                    'in transit', 'in_transit', 'transit' => 'blue',
                                    'arrived' => 'green',
                                    default => 'orange',
                                };

                                $mode    = strtolower($shipment->shipment_mode ?? '');
                                $isAir   = str_contains($mode, 'air');
                                $modeTxt = $isAir ? 'Air' : 'Ocean';

                                $fmt = fn ($d) => !empty($d) ? \Carbon\Carbon::parse($d)->format('d M Y') : '--';

                                $originName = $shipment->depPort->name ?? '--';
                                $destName   = $shipment->dstPort->name ?? '--';
                                $statusText = $shipment->status ?? 'Pending';

                                /*
                                 | Popup mein jo dikhana hai. Naya field chahiye to bas ek line add karo.
                                 | NOTE: client ko cost / balance / margin jaisi cheezein yahan mat daalna.
                                 */
                                $detail = [
                                    'Booking No'     => $shipment->file_no ?? '-',
                                    'Mode'        => $modeTxt,
                                    'Origin'      => $originName,
                                    'Destination' => $destName,
                                    'ETD'         => $fmt($shipment->etd ?? null),
                                    'ETA'         => $fmt($shipment->eta ?? null),
                                    'Status'      => $statusText,
                                ];

                                // Optional fields: jo shipment mein maujood hon sirf wohi popup mein aayengi
                                $optional = [
                                    'MAWB No'    => 'mawb_no',
                                    'MBL No'     => 'mbl_no',
                                    'Booking No' => 'booking_no',
                                    'Flight No'  => 'flight_no',
                                    'Vessel'     => 'vessel_id',
                                    'Voyage'     => 'voyage',
                                ];
                                foreach ($optional as $label => $col) {
                                    $val = data_get($shipment, $col);
                                    if (!empty($val)) {
                                        $detail[$label] = $val;
                                    }
                                }
                            @endphp

                            <tr>
                                {{-- File number: click par popup --}}
                                <td>
                                    <a href="#" class="rs-file"
                                       data-title="Shipment {{ $shipment->file_no ?? '' }}"
                                       data-details="{{ json_encode($detail) }}"
                                       onclick="openShipmentDetails(event, this)">{{ $shipment->file_no ?? 'N/A' }}</a>
                                </td>

                                <td>
                                    <span class="rs-mode" title="{{ $modeTxt }}">
                                        <i class="bi {{ $isAir ? 'bi-airplane-fill' : 'bi-water' }}"></i>
                                        {{ $modeTxt }}
                                    </span>
                                </td>

                                <td>{{ $originName }}</td>
                                <td>{{ $destName }}</td>

                                <td class="{{ empty($shipment->etd) ? 'rs-muted' : '' }}">{{ $fmt($shipment->etd ?? null) }}</td>
                                <td class="{{ empty($shipment->eta) ? 'rs-muted' : '' }}">{{ $fmt($shipment->eta ?? null) }}</td>

                                <td><span class="pill {{ $statusClass }}">{{ $statusText }}</span></td>

                                {{-- Action: same popup --}}
                                <td style="text-align:center;">
                                    <button type="button" class="rs-view" title="View details"
                                            data-title="Shipment {{ $shipment->file_no ?? '' }}"
                                            data-details="{{ json_encode($detail) }}"
                                            onclick="openShipmentDetails(event, this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:30px;" class="rs-muted">
                                    No shipments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </section>


        {{-- =================================================
             QUICK ACTIONS
        ================================================== --}}
        <section class="card">

            <div class="card-head">
                <h3>Quick Actions</h3>
            </div>

            <div class="quick">
                <a href="{{ route('client.shipments') }}" class="q"><i class="bi bi-box-seam"></i><span>Request Booking</span></a>
                <a href="{{ route('cp.quotes.index') }}" class="q"><i class="bi bi-file-earmark-text"></i><span>Request Quote</span></a>
                <a href="{{ route('client.tracking') }}" class="q"><i class="bi bi-geo-alt-fill"></i><span>Track Shipment</span></a>
                <!-- <a href="#" class="q"><i class="bi bi-upload"></i><span>Upload Document</span></a> -->
                <a href="{{ route('cp.invoices.index') }}" class="q"><i class="bi bi-credit-card-2-front"></i><span>Pay Invoice</span></a>
                <!-- <a href="#" class="q"><i class="bi bi-headset"></i><span>Contact Support</span></a> -->
            </div>

        </section>

    </div>


    {{-- =====================================================
         RIGHT COLUMN
    ====================================================== --}}
    <div class="col">

        {{-- Shipment Status Overview --}}
        <section class="card">

            <div class="card-head">
                <h3>Shipment Status Overview</h3>
            </div>

            <div class="donut-wrap">

                <div class="donut"
                     data-values="{{ $inTransit }},{{ $arrived }},{{ $delivered }},{{ $pending }}"
                     data-colors="#1d6bf3,#16a34a,#1f9d55,#f97316">

                    <div class="donut-center">
                        <strong>{{ $totalShipments }}</strong>
                        <small>Total</small>
                    </div>

                </div>

                <ul class="legend">
                    <li><i style="background:#1d6bf3"></i> In Transit <b>{{ $inTransit }}</b></li>
                    <li><i style="background:#16a34a"></i> Arrived <b>{{ $arrived }}</b></li>
                    <li><i style="background:#1f9d55"></i> Delivered <b>{{ $delivered }}</b></li>
                    <li><i style="background:#f97316"></i> Pending <b>{{ $pending }}</b></li>
                </ul>

            </div>

        </section>


        {{-- Upcoming Shipments --}}
        <section class="card">

            <div class="card-head">
                <h3>Upcoming Shipments</h3>
                <a href="{{ route('client.shipments') }}">View All</a>
            </div>

            @forelse($upcomingShipments as $shipment)

                @php $mode = strtolower($shipment->shipment_mode ?? ''); @endphp

                <div class="upcoming">

                    @if(str_contains($mode, 'air'))
                        <i class="bi bi-airplane-fill big"></i>
                    @else
                        <i class="bi bi-water big"></i>
                    @endif

                    <div>
                        <strong>{{ $shipment->file_no ?? 'N/A' }}</strong>

                        <small>
                            {{ $shipment->depPort->name ?? '--' }} → {{ $shipment->dstPort->name ?? '--' }}
                        </small>

                        <small>
                            ETD:
                            @if(!empty($shipment->etd))
                                {{ \Carbon\Carbon::parse($shipment->etd)->format('d M Y') }}
                            @else
                                --
                            @endif
                        </small>
                    </div>

                    <span class="pill blue">{{ $shipment->status ?? 'Pending' }}</span>

                </div>

            @empty

                <div style="padding:25px; text-align:center;">No upcoming shipments.</div>

            @endforelse

        </section>

    </div>

</div>


{{-- =========================================================
     BOTTOM GRID
========================================================= --}}
<div class="grid-3">

    {{-- Recent Notifications --}}
    <section class="card">

        <div class="card-head">
            <h3>Recent Notifications</h3>
            <a href="#">View All</a>
        </div>

        {{-- Notifications ke model/table ka structure milne ke baad database se connect karenge. --}}
        <div class="row-item">
            <span class="dot blue"><i class="bi bi-info-circle"></i></span>
            <div class="grow">
                <strong>Dashboard Updated</strong>
                <small>Your shipment information is shown below.</small>
            </div>
            <small class="time">Now</small>
        </div>

    </section>


    {{-- Invoice Summary --}}
    <section class="card">

        <div class="card-head">
            <h3>Invoice Summary</h3>
            <a href="#">View All</a>
        </div>

        <ul class="inv">
            <li>
                <span><i class="bi bi-receipt"></i> Total Invoices</span>
                <b>{{ number_format($totalInvoiceAmount ?? 0, 2) }}</b>
            </li>
            <li>
                <span><i class="bi bi-check-circle green"></i> Paid</span>
                <b class="green">{{ number_format($paidInvoiceAmount ?? 0, 2) }}</b>
            </li>
            <li>
                <span><i class="bi bi-hourglass-split red"></i> Outstanding</span>
                <b class="red">{{ number_format($outstandingInvoiceAmount ?? 0, 2) }}</b>
            </li>
            <li>
                <span><i class="bi bi-exclamation-circle red"></i> Overdue</span>
                <b class="red">{{ number_format($overdueInvoiceAmount ?? 0, 2) }}</b>
            </li>
        </ul>

        <a href="#" class="btn btn-primary block">View Invoices</a>

    </section>


    {{-- Messages --}}
    <section class="card">

        <div class="card-head">
            <h3>Messages</h3>
            <a href="#">View All</a>
        </div>

        {{-- Messages table/model ka structure milne ke baad real messages connect kar denge. --}}
        <div class="row-item">
            <span class="initials">FO</span>
            <div class="grow">
                <strong>Freight Operations</strong>
                <small>No new messages.</small>
            </div>
            <small class="time">--</small>
        </div>

        <div class="row-item">
            <span class="initials">DS</span>
            <div class="grow">
                <strong>Documentation</strong>
                <small>No new messages.</small>
            </div>
            <small class="time">--</small>
        </div>

    </section>

</div>


{{-- =========================================================
     MODAL SCRIPT
========================================================= --}}
<script>
function openShipmentDetails(e, el) {
    e.preventDefault();

    var data = JSON.parse(el.dataset.details);
    var grid = document.getElementById('sd-grid');
    grid.innerHTML = '';

    Object.keys(data).forEach(function (label) {
        var k = document.createElement('div');
        k.className = 'k';
        k.textContent = label;

        var v = document.createElement('div');
        v.className = 'v';
        v.textContent = data[label];

        grid.appendChild(k);
        grid.appendChild(v);
    });

    document.getElementById('sd-title').textContent = el.dataset.title || 'Shipment Details';
    document.getElementById('sd-overlay').classList.add('open');
}

function closeShipmentDetails() {
    document.getElementById('sd-overlay').classList.remove('open');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeShipmentDetails();
});
</script>

@endsection