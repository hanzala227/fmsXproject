@extends('layouts.userapp')
@section('title', 'Client Shipments')

@section('content')

@php
    /*
     | Har section ke liye: title, data variable, table columns, aur details (modal) fields.
     | 'columns' = table mein jo dikhna hai, 'details' = anchor click par jo poori detail khulni hai.
     | Details mein aur fields chahiye to bas yahan label => column_name add kar do.
     | NOTE: client ko sirf wohi fields dikhao jo uske dekhne ke liye theek hain
     | (balance, cost, margin jaisi cheezein yahan mat daalna).
     */
    $sections = [
        [
            'id' => 'air-import', 'title' => 'AIR IMPORT SHIPMENTS', 'icon' => 'fa-plane', 'rows' => $airImports,
            'columns' => ['MAWB No' => 'mawb_no', 'Flight No' => 'flight_no', 'Departure' => 'dep_port_id', 'Destination' => 'dst_port_id', 'ETD' => 'etd', 'ETA' => 'eta'],
            'details' => ['Booking No' => 'file_no', 'MAWB No' => 'mawb_no', 'Flight No' => 'flight_no', 'Departure' => 'dep_port_id', 'Destination' => 'dst_port_id', 'ETD' => 'etd', 'ETA' => 'eta'],
        ],
        [
            'id' => 'air-export', 'title' => 'AIR EXPORT SHIPMENTS', 'icon' => 'fa-plane', 'rows' => $airExports,
            'columns' => ['MAWB No' => 'mawb_no', 'Flight No' => 'flight_no', 'Departure' => 'dep_port_id', 'Destination' => 'dst_port_id', 'ETD' => 'etd', 'ETA' => 'eta'],
            'details' => ['Booking No' => 'file_no', 'MAWB No' => 'mawb_no', 'Flight No' => 'flight_no', 'Departure' => 'dep_port_id', 'Destination' => 'dst_port_id', 'ETD' => 'etd', 'ETA' => 'eta'],
        ],
        [
            'id' => 'ocean-import', 'title' => 'OCEAN IMPORT SHIPMENTS', 'icon' => 'fa-ship', 'rows' => $oceanImports,
            'columns' => ['MBL No' => 'mbl_no', 'Vessel' => 'vessel_id', 'Voyage' => 'voyage', 'POL' => 'pol_id', 'POD' => 'pod_id', 'ETA' => 'eta'],
            'details' => ['Booking No' => 'file_no', 'MBL No' => 'mbl_no', 'Vessel' => 'vessel_id', 'Voyage' => 'voyage', 'POL' => 'pol_id', 'POD' => 'pod_id', 'ETA' => 'eta'],
        ],
        [
            'id' => 'ocean-export', 'title' => 'OCEAN EXPORT SHIPMENTS', 'icon' => 'fa-ship', 'rows' => $oceanExports,
            'columns' => ['MBL No' => 'mbl_no', 'Booking No' => 'booking_no', 'Vessel' => 'vessel_id', 'Voyage' => 'voyage', 'ETD' => 'etd', 'ETA' => 'eta'],
            'details' => ['Booking No' => 'file_no', 'MBL No' => 'mbl_no', 'Booking No' => 'booking_no', 'Vessel' => 'vessel_id', 'Voyage' => 'voyage', 'ETD' => 'etd', 'ETA' => 'eta'],
        ],
    ];
@endphp

{{-- ===== Details modal ===== --}}
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
            <li><i class="fa fa-home"></i> <a href="{{ route('client.index') }}">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">My Shipments</span></li>
        </ul>
        <div class="company-tag">Company ID: <strong>{{ auth()->user()->company->name }}</strong></div>
    </div>

    @foreach($sections as $s)
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa {{ $s['icon'] }}" style="color:#3b82f6;"></i>
                    <span class="caption-subject">{{ $s['title'] }}</span>
                    <span class="count-badge">{{ count($s['rows']) }}</span>
                </div>
                <div class="portlet-search">
                    <i class="fa fa-search"></i>
                    <input type="text" class="input-inline" placeholder="Quick search..." oninput="quickSearch('{{ $s['id'] }}', this.value)">
                </div>
            </div>

            <div class="portlet-body">
                <div class="grid-wrapper">
                    <table class="grid-table" id="grid-{{ $s['id'] }}">
                        <thead>
                            <tr>
                                <th style="width:30px;text-align:center;">#</th>
                                <th style="width:130px;">Booking No.</th>
                                @foreach($s['columns'] as $label => $col)
                                    <th>{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($s['rows'] as $shipment)
                                @php
                                    $detail = [];
                                    foreach ($s['details'] as $label => $col) {
                                        $detail[$label] = $shipment->{$col} ?? '-';
                                    }
                                @endphp
                                <tr>
                                    <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="#" class="row-link"
                                           data-title="{{ $s['title'] }} - {{ $shipment->file_no }}"
                                           data-details="{{ json_encode($detail) }}"
                                           onclick="openDetails(event, this)">{{ $shipment->file_no }}</a>
                                    </td>
                                    @foreach($s['columns'] as $label => $col)
                                        <td>{{ $shipment->{$col} ?? '-' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="{{ count($s['columns']) + 2 }}">No shipments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>

<link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">

<script>
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

function quickSearch(id, q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('#grid-' + id + ' tbody tr:not(.empty-row)').forEach(function (tr) {
        tr.style.display = tr.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
}
</script>

@endsection