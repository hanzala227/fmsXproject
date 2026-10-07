@extends('layouts.userapp')
@section('title', 'Client Dashboard')


@section('content')

<div class="container">

    <div class="mb-4">
        <h1>My Shipments</h1>

        <p>
            Company ID:
            <strong>{{ auth()->user()->company_id }}</strong>
        </p>
    </div>


    {{-- ========================= --}}
    {{-- AIR IMPORT --}}
    {{-- ========================= --}}

    <div class="shipment-section">

        <h2>Air Import Shipments</h2>

        <div class="table-responsive">

            <table class="shipment-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>File No</th>
                        <th>MAWB No</th>
                        <th>Flight No</th>
                        <th>Departure</th>
                        <th>Destination</th>
                        <th>ETD</th>
                        <th>ETA</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($airImports as $shipment)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $shipment->file_no }}
                            </td>

                            <td>
                                {{ $shipment->mawb_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->flight_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->dep_port_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->dst_port_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->etd ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->eta ?? '-' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                No Air Import shipments found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ========================= --}}
    {{-- AIR EXPORT --}}
    {{-- ========================= --}}

    <div class="shipment-section">

        <h2>Air Export Shipments</h2>

        <div class="table-responsive">

            <table class="shipment-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>File No</th>
                        <th>MAWB No</th>
                        <th>Flight No</th>
                        <th>Departure</th>
                        <th>Destination</th>
                        <th>ETD</th>
                        <th>ETA</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($airExports as $shipment)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $shipment->file_no }}
                            </td>

                            <td>
                                {{ $shipment->mawb_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->flight_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->dep_port_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->dst_port_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->etd ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->eta ?? '-' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                No Air Export shipments found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ========================= --}}
    {{-- OCEAN IMPORT --}}
    {{-- ========================= --}}

    <div class="shipment-section">

        <h2>Ocean Import Shipments</h2>

        <div class="table-responsive">

            <table class="shipment-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>File No</th>
                        <th>MBL No</th>
                        <th>Vessel</th>
                        <th>Voyage</th>
                        <th>POL</th>
                        <th>POD</th>
                        <th>ETA</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($oceanImports as $shipment)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $shipment->file_no }}
                            </td>

                            <td>
                                {{ $shipment->mbl_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->vessel_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->voyage ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->pol_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->pod_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->eta ?? '-' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                No Ocean Import shipments found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ========================= --}}
    {{-- OCEAN EXPORT --}}
    {{-- ========================= --}}

    <div class="shipment-section">

        <h2>Ocean Export Shipments</h2>

        <div class="table-responsive">

            <table class="shipment-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>File No</th>
                        <th>MBL No</th>
                        <th>Booking No</th>
                        <th>Vessel</th>
                        <th>Voyage</th>
                        <th>ETD</th>
                        <th>ETA</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($oceanExports as $shipment)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $shipment->file_no }}
                            </td>

                            <td>
                                {{ $shipment->mbl_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->booking_no ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->vessel_id ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->voyage ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->etd ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->eta ?? '-' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                No Ocean Export shipments found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


<style>

.container {
    width: 95%;
    margin: 30px auto;
}

.shipment-section {
    margin-bottom: 40px;
}

.shipment-section h2 {
    margin-bottom: 15px;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.shipment-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.shipment-table th,
.shipment-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
    white-space: nowrap;
}

.shipment-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.shipment-table tbody tr:hover {
    background: #fafafa;
}

</style>

@endsection