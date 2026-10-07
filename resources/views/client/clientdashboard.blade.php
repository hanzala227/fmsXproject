
@extends('layouts.userapp')

@section('title', 'Client Dashboard')

@section('content')

{{-- =========================================================
     HERO
========================================================= --}}

<section class="hero"
    style="background-image:
        linear-gradient(90deg, rgba(10,22,45,.95) 30%, rgba(10,22,45,.2)),
        url('{{ asset('images/banner.jpg') }}')">

    <h1>
        Welcome back, {{ $loggedInUser->name ?? 'Customer' }}!
    </h1>

    <p>
        Track your shipments, manage documents, and stay updated<br>
        with your freight activities.
    </p>

    <div class="hero-btns">

        <a href="#" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            Request Booking
        </a>

        <a href="#" class="btn btn-outline">
            <i class="bi bi-search"></i>
            Track Shipment
        </a>

    </div>

</section>


{{-- =========================================================
     STAT CARDS
========================================================= --}}

<section class="stats">

    {{-- Total Shipments --}}
    <div class="stat">

        <span class="ico blue">
            <i class="bi bi-box-seam"></i>
        </span>

        <div>
            <small>Total Shipments</small>

            <div class="num">
                {{ $totalShipments }}
            </div>

            <small class="muted">
                All Shipments
            </small>
        </div>

    </div>


    {{-- In Transit --}}
    <div class="stat">

        <span class="ico blue">
            <i class="bi bi-truck"></i>
        </span>

        <div>
            <small>In Transit</small>

            <div class="num">
                {{ $inTransit }}
            </div>

            <small class="muted">
                Active Shipments
            </small>
        </div>

    </div>


    {{-- Arrived --}}
    <div class="stat">

        <span class="ico blue">
            <i class="bi bi-browser-safari"></i>
        </span>

        <div>
            <small>Arrived</small>

            <div class="num">
                {{ $arrived }}
            </div>

            <small class="muted">
                At Destination
            </small>
        </div>

    </div>


    {{-- Delivered --}}
    <div class="stat">

        <span class="ico green">
            <i class="bi bi-check-circle"></i>
        </span>

        <div>
            <small>Delivered</small>

            <div class="num">
                {{ $delivered }}
            </div>

            <small class="muted">
                Completed
            </small>
        </div>

    </div>


    {{-- Pending --}}
    <div class="stat">

        <span class="ico red">
            <i class="bi bi-file-earmark-text"></i>
        </span>

        <div>
            <small>Pending Shipments</small>

            <div class="num">
                {{ $pending }}
            </div>

            <small class="muted">
                Action Required
            </small>
        </div>

    </div>


    {{-- Outstanding Invoices --}}
    <div class="stat">

        <span class="ico orange">
            <i class="bi bi-receipt"></i>
        </span>

        <div>
            <small>Outstanding Invoices</small>

            <div class="num">
                {{ number_format($outstandingInvoiceAmount ?? 0, 2) }}
            </div>

            <small class="muted">

                {{ $invoices->where('balance_amount', '>', 0)->count() }}

                Invoices

            </small>
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

                <h3>
                    Recent Shipments
                </h3>

                <a href="{{ route('client.shipments') }}">
                    View All
                </a>

            </div>


            <div class="table-wrap">

                <table class="tbl">

                    <thead>

                        <tr>

                            <th>
                                Shipment No
                            </th>

                            <th>
                                Mode
                            </th>

                            <th>
                                Origin
                            </th>

                            <th>
                                Destination
                            </th>

                            <th>
                                ETD
                            </th>

                            <th>
                                ETA
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($recentShipments as $shipment)

                            @php

                                $shipmentStatus =
                                    strtolower($shipment->status ?? 'pending');

                                $statusClass = match ($shipmentStatus) {

                                    'delivered',
                                    'completed',
                                    'complete'
                                        => 'green',

                                    'in transit',
                                    'in_transit',
                                    'transit'
                                        => 'blue',

                                    'arrived'
                                        => 'green',

                                    default
                                        => 'orange',

                                };

                                $mode =
                                    strtolower($shipment->shipment_mode ?? '');

                            @endphp


                            <tr>

                                {{-- Shipment/File Number --}}
                                <td>

                                    <a href="#">

                                        {{ $shipment->file_no ?? 'N/A' }}

                                    </a>

                                </td>


                                {{-- Mode --}}
                                <td>

                                    @if(str_contains($mode, 'air'))

                                        <i class="bi bi-airplane-fill"></i>

                                    @else

                                        <i class="bi bi-water"></i>

                                    @endif

                                </td>


                                {{-- Origin --}}
                                <td>

                                    {{ $shipment->depPort->name ?? '--' }}

                                </td>


                                {{-- Destination --}}
                                <td>

                                    {{ $shipment->dstPort->name ?? '--' }}

                                </td>


                                {{-- ETD --}}
                                <td>

                                    @if(!empty($shipment->etd))

                                        {{ \Carbon\Carbon::parse($shipment->etd)->format('d M Y') }}

                                    @else

                                        --

                                    @endif

                                </td>


                                {{-- ETA --}}
                                <td>

                                    @if(!empty($shipment->eta))

                                        {{ \Carbon\Carbon::parse($shipment->eta)->format('d M Y') }}

                                    @else

                                        --

                                    @endif

                                </td>


                                {{-- Status --}}
                                <td>

                                    <span class="pill {{ $statusClass }}">

                                        {{ $shipment->status ?? 'Pending' }}

                                    </span>

                                </td>


                                {{-- Action --}}
                                <td>

                                    <i class="bi bi-three-dots"></i>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="8"
                                    style="text-align:center; padding:30px;">

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

                <h3>
                    Quick Actions
                </h3>

            </div>


            <div class="quick">

                <a href="#" class="q">

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Request Booking
                    </span>

                </a>


                <a href="#" class="q">

                    <i class="bi bi-file-earmark-text"></i>

                    <span>
                        Request Quote
                    </span>

                </a>


                <a href="#" class="q">

                    <i class="bi bi-geo-alt-fill"></i>

                    <span>
                        Track Shipment
                    </span>

                </a>


                <a href="#" class="q">

                    <i class="bi bi-upload"></i>

                    <span>
                        Upload Document
                    </span>

                </a>


                <a href="#" class="q">

                    <i class="bi bi-credit-card-2-front"></i>

                    <span>
                        Pay Invoice
                    </span>

                </a>


                <a href="#" class="q">

                    <i class="bi bi-headset"></i>

                    <span>
                        Contact Support
                    </span>

                </a>

            </div>

        </section>

    </div>



    {{-- =====================================================
         RIGHT COLUMN
    ====================================================== --}}

    <div class="col">


        {{-- =================================================
             SHIPMENT STATUS OVERVIEW
        ================================================== --}}

        <section class="card">

            <div class="card-head">

                <h3>
                    Shipment Status Overview
                </h3>

            </div>


            <div class="donut-wrap">


                <div
                    class="donut"

                    data-values="
                        {{ $inTransit }},
                        {{ $arrived }},
                        {{ $delivered }},
                        {{ $pending }}
                    "

                    data-colors="
                        #1d6bf3,
                        #16a34a,
                        #1f9d55,
                        #f97316
                    "
                >

                    <div class="donut-center">

                        <strong>
                            {{ $totalShipments }}
                        </strong>

                        <small>
                            Total
                        </small>

                    </div>

                </div>



                <ul class="legend">

                    <li>

                        <i style="background:#1d6bf3"></i>

                        In Transit

                        <b>
                            {{ $inTransit }}
                        </b>

                    </li>


                    <li>

                        <i style="background:#16a34a"></i>

                        Arrived

                        <b>
                            {{ $arrived }}
                        </b>

                    </li>


                    <li>

                        <i style="background:#1f9d55"></i>

                        Delivered

                        <b>
                            {{ $delivered }}
                        </b>

                    </li>


                    <li>

                        <i style="background:#f97316"></i>

                        Pending

                        <b>
                            {{ $pending }}
                        </b>

                    </li>

                </ul>

            </div>

        </section>



        {{-- =================================================
             UPCOMING SHIPMENTS
        ================================================== --}}

        <section class="card">

            <div class="card-head">

                <h3>
                    Upcoming Shipments
                </h3>

                <a href="{{ route('client.shipments') }}">
                    View All
                </a>

            </div>


            @forelse($upcomingShipments as $shipment)

                @php

                    $mode =
                        strtolower($shipment->shipment_mode ?? '');

                @endphp


                <div class="upcoming">


                    {{-- Icon --}}
                    @if(str_contains($mode, 'air'))

                        <i class="bi bi-airplane-fill big"></i>

                    @else

                        <i class="bi bi-water big"></i>

                    @endif



                    {{-- Shipment Information --}}
                    <div>

                        <strong>

                            {{ $shipment->file_no ?? 'N/A' }}

                        </strong>


                        <small>

                            {{ $shipment->depPort->name ?? '--' }}

                            →

                            {{ $shipment->dstPort->name ?? '--' }}

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



                    {{-- Status --}}
                    <span class="pill blue">

                        {{ $shipment->status ?? 'Pending' }}

                    </span>

                </div>


            @empty

                <div style="padding:25px; text-align:center;">

                    No upcoming shipments.

                </div>

            @endforelse

        </section>

    </div>

</div>



{{-- =========================================================
     BOTTOM GRID
========================================================= --}}

<div class="grid-3">


    {{-- =====================================================
         RECENT NOTIFICATIONS
    ====================================================== --}}

    <section class="card">

        <div class="card-head">

            <h3>
                Recent Notifications
            </h3>

            <a href="#">
                View All
            </a>

        </div>


        {{-- Temporary empty state --}}
        {{-- Notifications ke model/table ka exact structure milne ke baad
             is section ko bhi database se connect kar sakte hain. --}}

        <div class="row-item">

            <span class="dot blue">

                <i class="bi bi-info-circle"></i>

            </span>

            <div class="grow">

                <strong>
                    Dashboard Updated
                </strong>

                <small>
                    Your shipment information is shown below.
                </small>

            </div>

            <small class="time">
                Now
            </small>

        </div>

    </section>



    {{-- =====================================================
         INVOICE SUMMARY
    ====================================================== --}}

    <section class="card">

        <div class="card-head">

            <h3>
                Invoice Summary
            </h3>

            <a href="#">
                View All
            </a>

        </div>


        <ul class="inv">


            {{-- Total --}}
            <li>

                <span>

                    <i class="bi bi-receipt"></i>

                    Total Invoices

                </span>

                <b>

                    {{ number_format($totalInvoiceAmount ?? 0, 2) }}

                </b>

            </li>


            {{-- Paid --}}
            <li>

                <span>

                    <i class="bi bi-check-circle green"></i>

                    Paid

                </span>

                <b class="green">

                    {{ number_format($paidInvoiceAmount ?? 0, 2) }}

                </b>

            </li>


            {{-- Outstanding --}}
            <li>

                <span>

                    <i class="bi bi-hourglass-split red"></i>

                    Outstanding

                </span>

                <b class="red">

                    {{ number_format($outstandingInvoiceAmount ?? 0, 2) }}

                </b>

            </li>


            {{-- Overdue --}}
            <li>

                <span>

                    <i class="bi bi-exclamation-circle red"></i>

                    Overdue

                </span>

                <b class="red">

                    {{ number_format($overdueInvoiceAmount ?? 0, 2) }}

                </b>

            </li>


        </ul>


        <a href="#"
           class="btn btn-primary block">

            View Invoices

        </a>

    </section>



    {{-- =====================================================
         MESSAGES
    ====================================================== --}}

    <section class="card">

        <div class="card-head">

            <h3>
                Messages
            </h3>

            <a href="#">
                View All
            </a>

        </div>


        {{-- Messages table/model ka exact structure milne ke baad
             yahan real messages connect kar denge. --}}


        <div class="row-item">

            <span class="initials">
                FO
            </span>

            <div class="grow">

                <strong>
                    Freight Operations
                </strong>

                <small>
                    No new messages.
                </small>

            </div>

            <small class="time">
                --
            </small>

        </div>


        <div class="row-item">

            <span class="initials">
                DS
            </span>

            <div class="grow">

                <strong>
                    Documentation
                </strong>

                <small>
                    No new messages.
                </small>

            </div>

            <small class="time">
                --
            </small>

        </div>


    </section>


</div>

@endsection

