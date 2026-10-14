@forelse($hbls as $hbl)
<tr onclick="rowClick(event,this)" data-idx="{{ $hbl->id }}" data-shipment-id="{{ $hbl->ocean_import_id }}">
    <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" class="row-check" onchange="updateToolbar()">
    </td>
    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
        <i class="fa {{ $hbl->is_hold ? 'fa-lock' : 'fa-unlock' }}" 
           style="color:{{ $hbl->is_hold ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
           title="{{ $hbl->is_hold ? 'Locked' : 'Unlocked' }}" 
           onclick="toggleLock(this)"></i>
    </td>
    <td class="sticky-col" style="left:50px;text-align:center;">
        <i class="fa fa-flag" style="color:{{ $hbl->is_ecommerce ? '#ef4444' : '#e2e8f0' }};cursor:pointer;font-size:10px;" onclick="toggleFlag(this)"></i>
    </td>
    <td class="sticky-col" style="left:75px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;">
            <a href="{{ route('ocean-import.edit', $hbl->ocean_import_id) }}" class="col-link">{{ $hbl->oceanImport->file_no ?? '--' }}</a>
            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open shipment in new tab"></i>
        </div>
    </td>
    <td class="sticky-col" style="left:195px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;">
            <a href="{{ route('ocean-import.edit', $hbl->ocean_import_id) }}#hbls" class="col-link">{{ $hbl->hbl_no }}</a>
            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open HBL in new tab"></i>
        </div>
    </td>
    <td><span class="badge-status bg-green">Arrived</span></td>
    <td>{{ ($hbl->oceanImport->portOfLoading->name ?? '') . ' -> ' . ($hbl->oceanImport->portOfDischarge->name ?? '') }}</td>
    <td>{{ $hbl->updated_at->format('m-d-Y') }}</td>
    <td>{{ $hbl->oceanImport->mbl_no ?? '--' }}</td>
    <td>{{ $hbl->consignee->name ?? '--' }}</td>
    <td>{{ $hbl->containers->sum(fn($c) => $c->pivot->pkg_qty ?? 0) }} CTNS</td>
    <td>{{ number_format($hbl->containers->sum(fn($c) => $c->pivot->weight_kg ?? 0), 2) }} KG</td>
    <td>{{ number_format($hbl->containers->sum(fn($c) => $c->pivot->measure_cbm ?? 0), 2) }} CBM</td>
    <td style="text-align:center;">
        @if($hbl->is_customs_hold)
            <i class="fa fa-check" style="color:#22c55e;"></i>
        @else
            <span style="color:#94a3b8;">-</span>
        @endif
    </td>
    <td>{{ $hbl->containers->first()?->it_no ?? '--' }}</td>
    <td>{{ $hbl->is_obl_received ? 'OBL' : 'SEAWAY' }}</td>
    <td style="text-align:right;" class="val-pos">{{ number_format($hbl->ar_balance ?? 0, 2) }}</td>
    <td style="text-align:right;" class="val-neg">{{ number_format($hbl->ap_balance ?? 0, 2) }}</td>
    <td style="text-align:right;">{{ number_format($hbl->dc_balance ?? 0, 2) }}</td>
</tr>
@empty
<tr id="empty-row">
    <td colspan="20" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
        No House B/Ls found.
    </td>
</tr>
@endforelse
