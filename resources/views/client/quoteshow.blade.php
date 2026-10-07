@extends('layouts.userapp')

@section('content')

<div class="client-page">

    {{-- Back --}}
    <div class="client-page-header">

        <div>

            <a
                href="{{ route('cp.quotes.index') }}"
                class="client-back-link"
            >
                ← Back to Quotes
            </a>

            <h1>
                Quote {{ $quote->quote_no }}
            </h1>

            <p>
                Quotation details and charges
            </p>

        </div>

        <div>
            <span class="client-status">
                {{ $quote->status }}
            </span>
        </div>

    </div>

    {{-- Quote Information --}}
    <div class="client-card">

        <div class="client-card-header">
            <div>
                <h2>Quote Information</h2>
                <p>Basic quotation details</p>
            </div>
        </div>

        <div class="client-card-body">

            <div class="client-details-grid">

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Quote Number
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->quote_no }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Quote Date
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->quote_date ?? '-' }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Valid Till
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->expiry_date ?? '-' }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Transport Mode
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->transport_mode ?? '-' }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Shipping Type
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->shipping_type ?? '-' }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Commodity
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->commodity ?? '-' }}
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Weight
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->weight_kg ?? '-' }} kg
                    </span>
                </div>

                <div class="client-detail-item">
                    <span class="client-detail-label">
                        Volume
                    </span>

                    <span class="client-detail-value">
                        {{ $quote->volume_cbm ?? '-' }} CBM
                    </span>
                </div>

                <div class="client-detail-item client-detail-full">

                    <span class="client-detail-label">
                        Route
                    </span>

                    <span class="client-detail-value">

                        {{ $quote->pol_name ?? '-' }}

                        @if($quote->pol_code)
                            ({{ $quote->pol_code }})
                        @endif

                        →

                        {{ $quote->pod_name ?? '-' }}

                        @if($quote->pod_code)
                            ({{ $quote->pod_code }})
                        @endif

                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- Charges --}}
    <div class="client-table-card">

        <div class="client-card-header">

            <div>
                <h2>Charges</h2>
                <p>Quotation charges and rates</p>
            </div>

        </div>

        <div class="client-table-wrapper">

            <table class="client-table">

                <thead>
                    <tr>
                        <th>Charge</th>
                        <th>Unit</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Remark</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($charges as $c)

                        <tr>

                            <td>
                                <strong>
                                    {{ $c->charge_code ?? '-' }}
                                </strong>
                            </td>

                            <td>
                                {{ $c->unit ?? '-' }}
                            </td>

                            <td>

                                @if(isset($c->rate_all))
                                    {{ number_format((float) $c->rate_all, 2) }}
                                @elseif(isset($c->rate_20gp))
                                    {{ number_format((float) $c->rate_20gp, 2) }}
                                @else
                                    -
                                @endif

                            </td>

                            <td>
                                {{ number_format((float) ($c->amount ?? 0), 2) }}
                            </td>

                            <td>
                                {{ $c->remark ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5">

                                <div class="client-empty">

                                    <div class="client-empty-title">
                                        No Charges Found
                                    </div>

                                    <div class="client-empty-text">
                                        No charges have been listed for this quotation.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Notes --}}
    @if($quote->quotation_remark)

        <div class="client-notes">

            <div class="client-detail-header">
                Notes
            </div>

            <p>
                {{ $quote->quotation_remark }}
            </p>

        </div>

    @elseif($quote->remark)

        <div class="client-notes">

            <div class="client-detail-header">
                Notes
            </div>

            <p>
                {{ $quote->remark }}
            </p>

        </div>

    @endif

</div>

@endsection