@forelse($hbls as $hbl)
<tr id="hbl-row-{{ $hbl->id }}"
    data-id="{{ $hbl->id }}"
    data-hbl-no="{{ $hbl->hbl_no }}"
    data-shipment-id="{{ $hbl->ocean_export_id }}"
    onclick="rowClick(event, this)">
    
    <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" name="ids[]" value="{{ $hbl->id }}" class="row-check" onchange="updateToolbar()">
    </td>
    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
        <i class="fa {{ $hbl->is_customs_hold ? 'fa-lock' : 'fa-unlock' }}" 
           style="color:{{ $hbl->is_customs_hold ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
           title="{{ $hbl->is_customs_hold ? 'Lock' : 'Unlock' }}" 
           onclick="toggleLock(this)"></i>
    </td>
    <td class="sticky-col" style="left:50px;text-align:center;">
        <i class="fa fa-flag" 
           style="color:{{ $hbl->is_ecommerce ? '#ef4444' : '#e2e8f0' }};cursor:pointer;font-size:10px;" 
           title="{{ $hbl->is_ecommerce ? 'E-commerce' : 'Regular' }}" 
           onclick="event.stopPropagation();toggleFlag(this, {{ $hbl->id }})"></i>
    </td>
    <td class="sticky-col" style="left:75px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <a href="{{ route('ocean-export.edit', $hbl->ocean_export_id) }}" class="col-link">{{ optional($hbl->oceanExport)->file_no ?? '--' }}</a>
            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open shipment"></i>
        </div>
    </td>
    <td class="sticky-col" style="left:195px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span>{{ $hbl->hbl_no }}</span>
            <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" 
               title="Quick view" 
               onclick="event.stopPropagation();showHblQuickView({{ $hbl->id }}, '{{ addslashes($hbl->hbl_no) }}')"></i>
        </div>
    </td>
    
    <td><span class="badge-status bg-green">Arrived</span></td>
<td>
    {{ $hbl->oceanExport?->portOfLoading?->name ?? '--' }}
    →
    {{ $hbl->oceanExport?->portOfDischarge?->name ?? '--' }}
</td>    <td>{{ $hbl->updated_at->format('m-d-Y') }}</td>
    <td>{{ optional($hbl->oceanExport)->mbl_no ?? '--' }}</td>
    <td>{{ $hbl->consignee->name ?? '--' }}</td>
 <td>{{ $hbl->oceanExport?->containers?->sum('pkg_qty') ?? 0 }} PCS</td>
    <td>{{ number_format(optional($hbl->oceanExport)?->containers?->sum('weight_kg') ?? 0, 2) }} KG</td>
    <td>{{ number_format(optional($hbl->oceanExport)?->containers?->sum('measure_cbm') ?? 0, 2) }} CBM</td>
    <td style="text-align:center;">
        @if($hbl->is_customs_hold)
            <i class="fa fa-check" style="color:#22c55e;"></i>
        @else
            <span style="color:#94a3b8;">-</span>
        @endif
    </td>
    <td>{{ $hbl->it_no ?? '--' }}</td>
    <td>{{ $hbl->is_obl_received ? 'OBL' : 'SEAWAY' }}</td>
    <td style="text-align:right;" class="val-pos">{{ number_format($hbl->ar_balance ?? 0, 2) }}</td>
    <td style="text-align:right;" class="val-neg">{{ number_format($hbl->ap_balance ?? 0, 2) }}</td>
    <td style="text-align:right;">{{ number_format($hbl->dc_balance ?? 0, 2) }}</td>
</tr>
@empty
<tr id="empty-row">
    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:13px;font-weight:600;">No House B/Ls found</div>
        <div style="font-size:11px;margin-top:4px;">Try adjusting your filters or search criteria</div>
    </td>
</tr>
@endforelse
