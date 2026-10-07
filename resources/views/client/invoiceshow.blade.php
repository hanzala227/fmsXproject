@extends('layouts.userapp')

@section('content')

<div class="client-page">

    @php

        $bal =
            (float) $invoice->total_amount -
            (float) $invoice->paid_amount;

    @endphp


    {{-- Header --}}
    <div class="client-page-header">

        <div>

            <a
                href="{{ route('cp.invoices.index') }}"
                class="client-back-link"
            >
                ← Back to Invoices
            </a>

            <h1>
                Invoice {{ $invoice->invoice_no }}
            </h1>

            <p>
                Invoice details and payment information.
            </p>

        </div>


        <div>

            <span class="client-status">
                {{ $invoice->status }}
            </span>

        </div>

    </div>


    {{-- Invoice Information --}}
    <div class="client-card">

        <div class="client-card-header">

            <div>

                <h2>
                    Invoice Information
                </h2>

                <p>
                    Basic invoice details
                </p>

            </div>

        </div>


        <div class="client-card-body">

            <div class="client-details-grid">


                <div class="client-detail-item">

                    <span class="client-detail-label">
                        Invoice Number
                    </span>

                    <span class="client-detail-value">
                        {{ $invoice->invoice_no }}
                    </span>

                </div>


                <div class="client-detail-item">

                    <span class="client-detail-label">
                        Invoice Date
                    </span>

                    <span class="client-detail-value">
                        {{ $invoice->invoice_date ?? '-' }}
                    </span>

                </div>


                <div class="client-detail-item">

                    <span class="client-detail-label">
                        Due Date
                    </span>

                    <span class="client-detail-value">
                        {{ $invoice->due_date ?? '-' }}
                    </span>

                </div>


                <div class="client-detail-item">

                    <span class="client-detail-label">
                        Currency
                    </span>

                    <span class="client-detail-value">
                        {{ $invoice->currency ?? '-' }}
                    </span>

                </div>


                <div class="client-detail-item client-detail-full">

                    <span class="client-detail-label">
                        Bill To
                    </span>

                    <span class="client-detail-value">
                        {{ $invoice->billing_address ?? '-' }}
                    </span>

                </div>


            </div>

        </div>

    </div>


    {{-- Invoice Lines --}}
    <div class="client-table-card">

        <div class="client-card-header">

            <div>

                <h2>
                    Invoice Items
                </h2>

                <p>
                    Details of charges included in this invoice.
                </p>

            </div>

        </div>


        <div class="client-table-wrapper">

            <table class="client-table">

                <thead>

                    <tr>

                        <th>
                            Description
                        </th>

                        <th>
                            Qty
                        </th>

                        <th>
                            Rate
                        </th>

                        <th>
                            Amount
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($lines as $l)

                        <tr>

                            <td>

                                {{ $l->description ?? '-' }}

                            </td>


                            <td>

                                {{ $l->qty ?? '-' }}

                            </td>


                            <td>

                                {{ number_format(
                                    (float) ($l->rate ?? 0),
                                    2
                                ) }}

                            </td>


                            <td>

                                {{ number_format(
                                    (float) ($l->amount ?? 0),
                                    2
                                ) }}

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="4">

                                <div class="client-empty">

                                    <div class="client-empty-title">
                                        No Invoice Items
                                    </div>

                                    <div class="client-empty-text">
                                        No line items were found for this invoice.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Invoice Totals --}}
    <div class="client-total-box">

        <div class="client-total-row">

            <span>
                Subtotal
            </span>

            <strong>
                {{ number_format(
                    (float) $invoice->subtotal,
                    2
                ) }}
            </strong>

        </div>


        <div class="client-total-row">

            <span>
                Tax
            </span>

            <strong>
                {{ number_format(
                    (float) $invoice->tax_total,
                    2
                ) }}
            </strong>

        </div>


        <div class="client-total-row client-total-main">

            <span>
                Total
            </span>

            <strong>

                {{ $invoice->currency ?? '' }}

                {{ number_format(
                    (float) $invoice->total_amount,
                    2
                ) }}

            </strong>

        </div>


        <div class="client-total-row">

            <span>
                Paid
            </span>

            <strong>
                {{ number_format(
                    (float) $invoice->paid_amount,
                    2
                ) }}
            </strong>

        </div>


        <div class="client-total-row client-total-balance">

            <span>
                Balance Due
            </span>

            <strong>
                {{ number_format($bal, 2) }}
            </strong>

        </div>

    </div>


    {{-- Payments --}}
    <div class="client-table-card">

        <div class="client-card-header">

            <div>

                <h2>
                    Payments Received
                </h2>

                <p>
                    Payment history for this invoice.
                </p>

            </div>

        </div>


        <div class="client-table-wrapper">

            <table class="client-table">

                <thead>

                    <tr>

                        <th>
                            Payment #
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Method
                        </th>

                        <th>
                            Reference
                        </th>

                        <th>
                            Amount
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($payments as $p)

                        <tr>

                            <td>
                                {{ $p->payment_no ?? '-' }}
                            </td>

                            <td>
                                {{ $p->payment_date ?? '-' }}
                            </td>

                            <td>
                                {{ $p->payment_method ?? '-' }}
                            </td>

                            <td>
                                {{ $p->reference_no ?? '-' }}
                            </td>

                            <td>

                                {{ number_format(
                                    (float) ($p->amount ?? 0),
                                    2
                                ) }}

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="5">

                                <div class="client-empty">

                                    <div class="client-empty-title">
                                        No Payments Yet
                                    </div>

                                    <div class="client-empty-text">
                                        No payments have been recorded against this invoice.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection