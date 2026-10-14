@forelse($hbls as $hbl)
    <tr id="hbl-row-{{ $hbl->id }}" data-id="{{ $hbl->id }}" onclick="rowClick(event,this)">
        <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
            <input type="checkbox" class="row-check" value="{{ $hbl->id }}" onchange="updateToolbar()">
        </td>
        <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
            <i class="fa {{ optional($hbl->airImport)->is_blocked ? 'fa-lock' : 'fa-unlock' }}"
               style="color:{{ optional($hbl->airImport)->is_blocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;"
               title="{{ optional($hbl->airImport)->is_blocked ? 'Locked' : 'Unlocked' }}"></i>
        </td>
        <td class="sticky-col" style="left:46px;">
            <a href="/air-import/{{ $hbl->air_import_id }}/edit" class="col-link">{{ optional($hbl->airImport)->file_no ?? '--' }}</a>
        </td>
        <td class="sticky-col" style="left:166px;font-weight:600;border-right:1px solid #cbd5e1!important;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <a href="/air-import/{{ $hbl->air_import_id }}/edit" class="col-link">{{ $hbl->hawb_no ?: '--' }}</a>
                <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" title="Quick view" onclick="event.stopPropagation();showToast('info','HAWB: {{ $hbl->hawb_no }}')"></i>
            </div>
        </td>
        <td>{{ optional($hbl->airImport)->mawb_no ?? '--' }}</td>
        <td>{{ $hbl->customer->name ?? '--' }}</td>
        <td>{{ $hbl->shipper->name ?? '--' }}</td>
        <td>{{ $hbl->consignee->name ?? '--' }}</td>
        <td>{{ optional($hbl->airImport->depPort)->name ?? '--' }}</td>
        <td>{{ optional($hbl->airImport->dstPort)->name ?? '--' }}</td>
        <td>{{ optional($hbl->airImport)->eta ? \Carbon\Carbon::parse(optional($hbl->airImport)->eta)->format('m-d-Y') : '--' }}</td>
        <td>{{ optional($hbl->airImport)->etd ? \Carbon\Carbon::parse(optional($hbl->airImport)->etd)->format('m-d-Y') : '--' }}</td>
        <td>{{ $hbl->salesPerson->name ?? '--' }}</td>
        <td>{{ $hbl->op->name ?? '--' }}</td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No House Air Waybills found.
        </td>
    </tr>
@endforelse
