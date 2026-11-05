@forelse($quotations as $q)
    @php
        $arItems = $q->items->where('type', 'AR')->values();
        $dcItems = $q->items->where('type', 'DC_NOTE')->values();
        $stParts = explode('~', $q->service_term ?? '');
        $stDisplay = trim(($stParts[0] ?? '') . ' / ' . ($stParts[1] ?? ''), ' /');
    @endphp
    <tr id="quote-row-{{ $q->id }}"
        data-id="{{ $q->id }}"
        data-quote="{{ $q->quote_no }}"
        data-customer="{{ $q->customer?->name ?? '' }}"
        data-status="{{ $q->status }}"
        onclick="rowClick(event, this)"
    >
        <td class="sticky-col" style="width:25px;text-align:center;left:0;" onclick="event.stopPropagation()">
            <input type="checkbox" name="ids[]" value="{{ $q->id }}" class="row-check" onchange="updateToolbar()">
        </td>
        <td class="sticky-col" style="width:120px;left:25px;" onclick="event.stopPropagation()">
            <a href="{{ route('sales.quotations.edit', $q->id) }}" class="col-link">{{ $q->quote_no }}</a>
            <a href="{{ route('sales.quotations.print', $q->id) }}" target="_blank" title="Print Quotation PDF" style="margin-left: 6px; color: #64748b; font-size: 11px;" onclick="event.stopPropagation()"><i class="fa fa-print"></i></a>
        </td>
        <td class="sticky-col" style="width:85px;left:145px;">{{ ($q->quote_date ?? $q->created_at)?->format('Y-m-d') ?? '--' }}</td>
        <td class="sticky-col" style="width:85px;left:230px;text-align:center;">
            @php
                $bgClass = match(strtolower($q->status)) {
                    'sent' => 'bg-blue',
                    'won', 'approved' => 'bg-green',
                    'expired', 'lost', 'cancelled' => 'bg-red',
                    'pending' => 'bg-yellow',
                    default => 'bg-gray'
                };
            @endphp
            <span class="badge-status {{ $bgClass }}">{{ strtoupper($q->status) }}</span>
        </td>
        <td class="freeze-divider">{{ $q->office?->name ?? '--' }}</td>
        <td>{{ $q->customer?->name ?? '--' }}</td>
        <td>{{ $q->agent?->name ?? '--' }}</td>
        <td>{{ $stDisplay ?: '--' }}</td>
        <td>{{ $q->transport_mode ?? '--' }}</td>
        <td>{{ $q->pol?->name ?? '--' }}</td>
        <td>{{ $q->pod?->name ?? '--' }}</td>
        @for($i = 0; $i < 7; $i++)
        <td>
            <div class="rate-cell">
                @isset($arItems[$i])<span class="rate-val">{{ number_format($arItems[$i]->rate, 2) }}</span> <span class="rate-type">{{ $arItems[$i]->currency->code ?? '' }} {{ $arItems[$i]->unit }}</span>@else<span class="rate-val" style="color:#ccc;">--</span>@endisset
            </div>
        </td>
        @endfor
        <td title="{{ $q->quotation_remark ?? '' }}">{{ Str::limit($q->quotation_remark ?? '', 30) ?: '--' }}</td>
        <td>{{ $q->expiry_date?->format('Y-m-d') ?? '--' }}</td>
        <td>{{ $q->departure ?? '--' }}</td>
        <td>{{ $q->destination ?? '--' }}</td>
        <td>{{ $q->carrier?->name ?? '--' }}</td>
        <td>{{ $q->via ?? '--' }}</td>
        <td>{{ $q->tt ?? '--' }}</td>
        <td title="{{ $q->commodity ?? '' }}">{{ Str::limit($q->commodity ?? '', 20) ?: '--' }}</td>
        <td>{{ $q->createdBy?->name ?? '--' }}</td>
        <td>{{ $q->salesPerson?->name ?? '--' }}</td>
        <td>{{ $q->op?->name ?? '--' }}</td>
        <td title="{{ $q->internal_remark ?? '' }}">{{ Str::limit($q->internal_remark ?? '', 25) ?: '--' }}</td>
        <td>{{ $q->liner_code ?? '--' }}</td>
        <td>{{ $q->final_destination ?? '--' }}</td>
        <td>{{ $q->place_of_receipt ?? '--' }}</td>
        <td>{{ $q->place_of_delivery ?? '--' }}</td>
        <td>{{ $q->schedule?->schedule_no ?? '--' }}</td>
        @for($i = 0; $i < 7; $i++)
        <td>
            <div class="rate-cell">
                @isset($dcItems[$i])<span class="rate-val">{{ number_format($dcItems[$i]->rate, 2) }}</span> <span class="rate-type">{{ $dcItems[$i]->currency->code ?? '' }}</span>@else<span class="rate-val" style="color:#ccc;">--</span>@endisset
            </div>
        </td>
        @endfor
        <td>{{ $q->ship_mode ?? '--' }}</td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No quotations found.
        </td>
    </tr>
@endforelse
