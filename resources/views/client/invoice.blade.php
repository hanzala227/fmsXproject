@extends('layouts.userapp')

@section('content')

<div class="client-page">

    {{-- Page Header --}}
    <div class="client-page-header">

        <div>

            <h1>
                My Invoices
            </h1>

            <p>
                View your company invoices and payment status.
            </p>

        </div>

    </div>


    {{-- Summary Cards --}}
    <div class="client-summary-grid">

        <div class="client-summary-card">

            <div class="client-summary-title">
                Total Billed
            </div>

            <div class="client-summary-value">
                {{ number_format((float) $totals->billed, 2) }}
            </div>

        </div>


        <div class="client-summary-card">

            <div class="client-summary-title">
                Paid
            </div>

            <div class="client-summary-value">
                {{ number_format((float) $totals->paid, 2) }}
            </div>

        </div>


        <div class="client-summary-card">

            <div class="client-summary-title">
                Outstanding
            </div>

            <div class="client-summary-value">

                {{ number_format(
                    (float) $totals->billed - (float) $totals->paid,
                    2
                ) }}

            </div>

        </div>

    </div>


    {{-- Filters --}}
    <div class="client-filter-box">

        <form method="GET" class="client-filter-form">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Invoice no"
                class="client-input"
            >

            <select name="status" class="client-select">

                <option value="">
                    All Status
                </option>

                @foreach(['POSTED', 'PARTIAL', 'PAID'] as $s)

                    <option
                        value="{{ $s }}"
                        @selected(request('status') === $s)
                    >
                        {{ $s }}
                    </option>

                @endforeach

            </select>


            <label class="client-checkbox-label">

                <input
                    type="checkbox"
                    name="overdue"
                    value="1"
                    @checked(request('overdue'))
                >

                <span>
                    Overdue Only
                </span>

            </label>


            <button
                type="submit"
                class="client-btn"
            >
                Filter
            </button>


            @if(request()->hasAny(['search', 'status', 'overdue']))

                <a
                    href="{{ route('cp.invoices.index') }}"
                    class="client-clear-btn"
                >
                    Clear
                </a>

            @endif

        </form>

    </div>


    {{-- Invoice Table --}}
    <div class="client-table-card">

        <div class="client-card-header">

            <div>

                <h2>
                    Invoices
                </h2>

                <p>
                    Your company invoice records
                </p>

            </div>

        </div>


        <div class="client-table-wrapper">

            <table class="client-table">

                <thead>

                    <tr>

                        <th>
                            Invoice #
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Due
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Paid
                        </th>

                        <th>
                            Balance
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

                    @forelse($invoices as $i)

                        @php

                            $bal =
                                (float) $i->total_amount -
                                (float) $i->paid_amount;

                            $late =
                                $i->status !== 'PAID' &&
                                $i->due_date &&
                                $i->due_date < now()->toDateString();

                        @endphp


                        <tr class="{{ $late ? 'client-overdue-row' : '' }}">

                            <td>

                                <strong>
                                    {{ $i->invoice_no }}
                                </strong>

                            </td>


                            <td>
                                {{ $i->invoice_date ?? '-' }}
                            </td>


                            <td>

                                {{ $i->due_date ?? '-' }}

                                @if($late)
                                    <span class="client-overdue">
                                        Overdue
                                    </span>
                                @endif

                            </td>


                            <td>

                                {{ $i->currency ?? '' }}

                                {{ number_format(
                                    (float) $i->total_amount,
                                    2
                                ) }}

                            </td>


                            <td>

                                {{ number_format(
                                    (float) $i->paid_amount,
                                    2
                                ) }}

                            </td>


                            <td>

                                {{ number_format(
                                    $bal,
                                    2
                                ) }}

                            </td>


                            <td>

                                <span class="client-status">
                                    {{ $i->status }}
                                </span>

                            </td>


                            <td>

                                <a
                                    href="{{ route('cp.invoices.show', $i->id) }}"
                                    class="client-view-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="8">

                                <div class="client-empty">

                                    <div class="client-empty-title">
                                        No Invoices Found
                                    </div>

                                    <div class="client-empty-text">
                                        There are no invoices available for your company.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($invoices->hasPages())

            <div class="client-pagination">
                {{ $invoices->links() }}
            </div>

        @endif

    </div>

</div>

@endsection