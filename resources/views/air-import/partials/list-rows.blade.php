@forelse($shipments as $shipment)
    <tr id="shipment-row-{{ $shipment->id }}" data-id="{{ $shipment->id }}" data-file="{{ $shipment->file_no }}" onclick="rowClick(event,this)">
        <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
            <input type="checkbox" class="row-check" value="{{ $shipment->id }}" onchange="updateToolbar()">
        </td>
        <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
            <i class="fa {{ $shipment->is_blocked ? 'fa-lock' : 'fa-unlock' }}"
               style="color:{{ $shipment->is_blocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;"
               title="{{ $shipment->is_blocked ? 'Locked' : 'Unlocked' }}"
               onclick="toggleLock(this)"></i>
        </td>
        <td class="sticky-col" style="left:53px;font-weight:600;" onclick="event.stopPropagation()">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <a href="{{ route('air-import.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no ?: '--' }}</a>
                <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;"></i>
            </div>
        </td>
        <td class="sticky-col" style="left:178px;font-weight:600;border-right:1px solid #cbd5e1!important;">
            <span>{{ $shipment->mawb_no ?: '--' }}</span>
        </td>
        <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>
        <td>{{ $shipment->shipper_rel->name ?? '--' }}</td>
        <td>{{ optional($shipment->hbls->first())->shipper->name ?? '--' }}</td>
        <td>{{ $shipment->carrier->name ?? '--' }}</td>
        <td>{{ $shipment->dstPort->name ?? '--' }}</td>
        <td>{{ $shipment->depPort->name ?? '--' }}</td>
        <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>
        <td>{{ $shipment->ata ? $shipment->ata->format('m-d-Y') : '--' }}</td>
        <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>
        <td>{{ $shipment->atd ? $shipment->atd->format('m-d-Y') : '--' }}</td>
        <td>{{ $shipment->hbls->pluck('hawb_no')->implode(', ') ?: '--' }}</td>
        <td>{{ $shipment->flight_no ?? '--' }}</td>
        <td>{{ $shipment->dmSalesPerson->name ?? '--' }}</td>
        <td>{{ $shipment->operator->name ?? '--' }}</td>
        <td>{{ $shipment->created_at ? $shipment->created_at->format('m-d-Y') : '--' }}</td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No Shipments found.
        </td>
    </tr>
@endforelse
