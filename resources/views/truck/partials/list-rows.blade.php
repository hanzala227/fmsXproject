@forelse($shipments as $shipment)
    <tr id="shipment-row-{{ $shipment->id }}"
        data-id="{{ $shipment->id }}"
        data-file="{{ $shipment->file_no }}"
        onclick="rowClick(event, this)"
    >
        {{-- Checkbox --}}
        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
            <input type="checkbox" name="ids[]" value="{{ $shipment->id }}" class="row-check" onchange="updateToolbar()">
        </td>
        {{-- Lock --}}
        <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
            @php
                $isBlocked = $shipment->is_blocked ?? false;
                $lockClass = $isBlocked ? 'fa-lock' : 'fa-unlock';
                $lockColor = $isBlocked ? '#94a3b8' : '#22c55e';
                $lockTitle = $isBlocked ? 'Blocked' : 'Unlocked';
            @endphp
            <i class="fa {{ $lockClass }}" style="color:{{ $lockColor }};cursor:pointer;font-size:10px;" title="{{ $lockTitle }}" onclick="toggleLock(this)"></i>
        </td>
        {{-- File No. --}}
        <td class="sticky-col" style="width:110px;left:50px;" onclick="event.stopPropagation()">
            <a href="{{ route('truck.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
        </td>
        {{-- Post Date --}}
        <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
        {{-- Customer --}}
        <td>{{ $shipment->customer->name ?? '--' }}</td>
        {{-- Trucker --}}
        <td>{{ $shipment->trucker->name ?? '--' }}</td>
        {{-- MB/L --}}
        <td>{{ $shipment->mbl_no ?? '--' }}</td>
        {{-- HB/L --}}
        <td>{{ $shipment->hbl_no ?? '--' }}</td>
        {{-- Package --}}
        <td style="text-align:right;">{{ $shipment->pkg_qty ?? 0 }}</td>
        {{-- Weight --}}
        <td style="text-align:right;">{{ number_format($shipment->weight_kg ?? 0, 2) }}</td>
        {{-- POD --}}
        <td>{{ $shipment->pod->name ?? '--' }}</td>
        {{-- Final Destination --}}
        <td>{{ $shipment->finalDestination->name ?? '--' }}</td>
        {{-- AR Balance --}}
        <td style="text-align:right;">
            @php
                $arTotal = $shipment->charges->where('type', 'AR')->sum('amount');
            @endphp
            <a href="{{ route('truck.edit', $shipment->id) }}" class="col-link" style="float:right;">{{ $arTotal > 0 ? number_format($arTotal, 2) : 'N/A' }}</a>
        </td>
        {{-- D/O --}}
        <td style="text-align:center;">{{ $shipment->is_delivered ? 'Y' : 'N' }}</td>
        {{-- Action --}}
        <td>
            <i class="fa fa-arrow-right icon-btn" onclick="event.stopPropagation();window.location='{{ route('truck.edit', $shipment->id) }}'" style="cursor:pointer;"></i>
        </td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="16" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No shipments found.
        </td>
    </tr>
@endforelse
