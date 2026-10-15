{{-- resources/views/client/company.blade.php --}}
@extends('layouts.userapp')
@section('title', 'My Company')

@section('content')

{{-- ===== Quick View Details Modal: Same classes as Shipments page ===== --}}
<div class="overlay" id="details-overlay" onclick="if(event.target===this) closeDetails()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-title">
                <i class="fa fa-info-circle" style="color:#3b82f6;"></i>
                <span id="details-title">Details</span>
            </div>
            <button type="button" class="modal-close" onclick="closeDetails()">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="kv-grid" id="details-grid"></div>
        </div>
    </div>
</div>

<div class="page-content">

    {{-- ===== Breadcrumb ===== --}}
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="{{ route('client.index') }}">Home</a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                <span style="color:#333;font-weight:700;">My Company</span>
            </li>
        </ul>
    </div>

    {{-- ===== Company Information ===== --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-building-o" style="color:#3b82f6;"></i>
                <span class="caption-subject">MY COMPANY</span>
            </div>

            <span class="status-pill">{{ $myCompany->status }}</span>
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Name</span>
                <span class="detail-value">{{ $myCompany->name }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Code</span>
                <span class="detail-value">{{ $myCompany->code }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $myCompany->email ?? '-' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Phone</span>
                <span class="detail-value">{{ $myCompany->phone ?? '-' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">City</span>
                <span class="detail-value">{{ $myCompany->city ?? '-' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Credit Term</span>
                <span class="detail-value">
                    {{ $myCompany->credit_term_days ?? '-' }}
                    {{ $myCompany->credit_term_unit }}
                </span>
            </div>

            <div class="detail-item full">
                <span class="detail-label">Billing Address</span>
                <span class="detail-value">{{ $myCompany->billing_address ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ===== Shippers ===== --}}
    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-users" style="color:#3b82f6;"></i>
                <span class="caption-subject">MY SHIPPERS</span>
                <span class="count-badge">{{ count($shippers) }}</span>
            </div>

            <div class="portlet-search">
                <i class="fa fa-search"></i>
                <input
                    type="text"
                    class="input-inline"
                    placeholder="Quick search..."
                    oninput="quickSearch(this.value)"
                >
            </div>
        </div>

        <div class="portlet-body">
            <div class="grid-wrapper">
                <table class="grid-table" id="shippers-grid">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>City</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($shippers as $s)
                            @php
                                $shipperDetails = [
                                    'Name' => $s->name ?? '-',
                                    'Code' => $s->code ?? '-',
                                    'City' => $s->city ?? '-',
                                    'Phone' => $s->phone ?? '-',
                                    'Email' => $s->email ?? '-',
                                ];
                            @endphp

                            <tr>
                                <td style="text-align:center;color:#94a3b8;">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <a
                                        href="#"
                                        class="row-link"
                                        data-title="Shipper Details - {{ $s->name }}"
                                        data-details="{{ json_encode($shipperDetails, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
                                        onclick="openDetails(event, this)"
                                    >
                                        {{ $s->name }}
                                    </a>
                                </td>

                                <td>
                                    <a
                                        href="#"
                                        class="row-link"
                                        data-title="Shipper Details - {{ $s->name }}"
                                        data-details="{{ json_encode($shipperDetails, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
                                        onclick="openDetails(event, this)"
                                    >
                                        {{ $s->code ?? '-' }}
                                    </a>
                                </td>

                                <td>{{ $s->city ?? '-' }}</td>
                                <td>{{ $s->phone ?? '-' }}</td>
                                <td>{{ $s->email ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="6">No shippers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Existing shared CSS --}}
<link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">

<script>
function openDetails(e, el) {
    e.preventDefault();

    try {
        var data = JSON.parse(el.dataset.details);
        var grid = document.getElementById('details-grid');

        grid.innerHTML = '';

        Object.keys(data).forEach(function (label) {
            var k = document.createElement('div');
            k.className = 'k';
            k.textContent = label;

            var v = document.createElement('div');
            v.className = 'v';
            v.textContent = data[label] ?? '-';

            grid.appendChild(k);
            grid.appendChild(v);
        });

        document.getElementById('details-title').textContent =
            el.dataset.title || 'Details';

        document.getElementById('details-overlay').classList.add('open');
    } catch (error) {
        console.error('Unable to open Quick View:', error);
    }
}

function closeDetails() {
    document.getElementById('details-overlay').classList.remove('open');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeDetails();
    }
});

function quickSearch(q) {
    q = q.toLowerCase().trim();

    document.querySelectorAll(
        '#shippers-grid tbody tr:not(.empty-row)'
    ).forEach(function (tr) {
        tr.style.display =
            tr.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
}
</script>

@endsection
