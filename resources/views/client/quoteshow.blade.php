@extends('layouts.userapp')
@section('title', 'Quote ' . $quote->quote_no)

@section('content')

<div class="page-content">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><i class="fa fa-home"></i> <a href="{{ route('client.index') }}">Home</a> <i class="fa fa-angle-right"></i></li>
            <li><a href="{{ route('cp.quotes.index') }}">My Quotes</a> <i class="fa fa-angle-right"></i></li>
            <li><span style="color:#333;font-weight:700;">{{ $quote->quote_no }}</span></li>
        </ul>
    </div>

    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-file-text-o" style="color:#3b82f6;"></i>
                <span class="caption-subject">QUOTE {{ $quote->quote_no }}</span>
            </div>
        </div>

        {{-- Index page sirf yeh div uthati hai --}}
        <div id="quote-detail-content">

            <div class="sub-title">
                Quote Information
                <span class="status-pill" style="margin-left:8px;">{{ $quote->status }}</span>
            </div>

            <div class="details-grid">
                <div class="detail-item"><span class="detail-label">Quote Number</span><span class="detail-value">{{ $quote->quote_no }}</span></div>
                <div class="detail-item"><span class="detail-label">Quote Date</span><span class="detail-value">{{ $quote->quote_date ? \Carbon\Carbon::parse($quote->quote_date)->format('d M Y') : '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Valid Till</span><span class="detail-value">{{ $quote->expiry_date ? \Carbon\Carbon::parse($quote->expiry_date)->format('d M Y') : '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Transport Mode</span><span class="detail-value">{{ $quote->transport_mode ?? '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Shipping Type</span><span class="detail-value">{{ $quote->shipping_type ?? '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Commodity</span><span class="detail-value">{{ $quote->commodity ?? '-' }}</span></div>
                <div class="detail-item"><span class="detail-label">Weight</span><span class="detail-value">{{ $quote->weight_kg ?? '-' }} kg</span></div>
                <div class="detail-item"><span class="detail-label">Volume</span><span class="detail-value">{{ $quote->volume_cbm ?? '-' }} CBM</span></div>
                <div class="detail-item full">
                    <span class="detail-label">Route</span>
                    <span class="detail-value">
                        {{ $quote->pol_name ?? '-' }}@if($quote->pol_code) ({{ $quote->pol_code }})@endif
                        <i class="fa fa-long-arrow-right" style="color:#94a3b8;"></i>
                        {{ $quote->pod_name ?? '-' }}@if($quote->pod_code) ({{ $quote->pod_code }})@endif
                    </span>
                </div>
            </div>

            <div class="sub-title">Charges</div>
            <div class="grid-wrapper">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:30px;text-align:center;">#</th>
                            <th>Charge</th>
                            <th>Unit</th>
                            <th style="text-align:right;">Rate</th>
                            <th style="text-align:right;">Amount</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotationCharges as $c)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                                <td><strong>{{ $c->charge_code ?? '-' }}</strong></td>
                                <td>{{ $c->unit ?? '-' }}</td>
                                <td style="text-align:right;">
                                    @if(isset($c->rate_all))
                                        {{ number_format((float) $c->rate_all, 2) }}
                                    @elseif(isset($c->rate_20gp))
                                        {{ number_format((float) $c->rate_20gp, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align:right;">{{ number_format((float) ($c->amount ?? 0), 2) }}</td>
                                <td>{{ $c->remark ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-title">No Charges Found</div>
                                    No charges have been listed for this quotation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php $notes = $quote->quotation_remark ?: $quote->remark; @endphp
            @if($notes)
                <div class="sub-title">Notes</div>
                <div class="notes-box">{{ $notes }}</div>
            @endif

        </div>
    </div>

</div>


@endsection