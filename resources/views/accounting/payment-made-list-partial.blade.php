<table>
    <tbody id="grid-body">
    @forelse($payments as $payment)
    <tr id="payment-row-{{ $payment->id }}"
        data-id="{{ $payment->id }}"
        data-payment_no="{{ $payment->payment_no }}"
        data-date="{{ $payment->payment_date?->format('Y-m-d') ?? '' }}"
        data-partner="{{ $payment->tradePartner?->name ?? '' }}"
        data-amount="{{ $payment->amount }}"
        onclick="rowClick(event, this)"
    >
        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
            <input type="checkbox" class="row-check" value="{{ $payment->id }}" onchange="updateToolbar()">
        </td>
        <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
            <i class="fa {{ $payment->deleted_at ? 'fa-lock' : 'fa-unlock-alt' }}" style="color:{{ $payment->deleted_at ? '#94a3b8' : '#22c55e' }};font-size:10px;"></i>
        </td>
        <td class="sticky-col" style="width:80px;left:50px;" onclick="event.stopPropagation()">
            <a href="{{ route('accounting.payment.edit', $payment->id) }}" class="col-link">
                {{ $payment->payment_date?->format('m-d-Y') ?? '' }}
            </a>
        </td>
        <td class="sticky-col" style="width:140px;left:130px;" onclick="event.stopPropagation()">
            <a href="{{ route('accounting.payment.edit', $payment->id) }}" class="col-link">
                {{ $payment->tradePartner?->name ?? '' }}
            </a>
        </td>
        <td>{{ $payment->payment_method }}</td>
        <td>{{ $payment->reference_no }}</td>
        <td>{{ $payment->bank_name ?? '' }}</td>
        <td style="text-align:right;">{{ number_format($payment->amount, 2) }}</td>
        <td style="text-align:right;">
            <span style="color:#999;">{{ $payment->bankCurrency?->code ?? $payment->currency?->code ?? 'CAD' }}</span>
            <span style="margin-left:6px;">{{ number_format($payment->amount, 2) }}</span>
        </td>
        <td>{{ $payment->clear_date?->format('m-d-Y') ?? '' }}</td>
        <td style="text-align:center;">{!! $payment->void_date ? '<i class="fa fa-check" style="color:#ef4444;"></i>' : '' !!}</td>
        <td>{{ $payment->void_date?->format('m-d-Y') ?? '' }}</td>
        <td>{{ $payment->office?->code ?? '' }}</td>
        <td style="text-align:center;">
            @if($payment->show_party_on_check)
                <i class="fa fa-check" style="color:#22c55e;font-weight:bold;"></i>
            @endif
        </td>
        <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;">{{ $payment->remark ?? '' }}</td>
    </tr>
    @empty
    <tr id="empty-row">
        <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No payments made found.
        </td>
    </tr>
    @endforelse
    </tbody>
</table>
