@forelse($hbls as $hbl)
    @php
        $isBlocked = optional($hbl->airImport)->is_blocked ?? false;
    @endphp
    <tr id="hbl-row-{{ $hbl->id }}" data-id="{{ $hbl->id }}" onclick="rowClick(event,this)">
        <td style="text-align:center;" onclick="event.stopPropagation()">
            <input type="checkbox" class="row-check" value="{{ $hbl->id }}" onchange="updateToolbar()">
        </td>
        <td style="text-align:center;" onclick="event.stopPropagation()">
            <i class="fa {{ $isBlocked ? 'fa-lock' : 'fa-unlock' }}"
               style="color:{{ $isBlocked ? '#ef4444' : '#22c55e' }};cursor:pointer;font-size:11px;"
               title="{{ $isBlocked ? 'Blocked' : 'Unlocked' }}"></i>
        </td>
        <td style="text-align:center;" onclick="event.stopPropagation()">
            <span class="color-mark"
                  onclick="openColorPicker({{ $hbl->id }}, '{{ $hbl->color ?? '' }}')"
                  style="display:inline-block;width:14px;height:14px;border-radius:2px;background:{{ $hbl->color ?: '#cbd5e1' }};cursor:pointer;border:1px solid #94a3b8;vertical-align:middle;"
                  title="Status Color (Click to change)"></span>
        </td>
        <td>
            <a href="/air-import/{{ $hbl->air_import_id }}/edit" class="col-link">{{ optional($hbl->airImport)->file_no ?? '--' }}</a>
        </td>
        <td style="font-weight:600;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <a href="/air-import/{{ $hbl->air_import_id }}/edit" class="col-link">{{ $hbl->hawb_no ?: '--' }}</a>
                <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" title="Quick view" onclick="event.stopPropagation();showToast('info','HAWB: {{ $hbl->hawb_no }}')"></i>
            </div>
        </td>
        <td>{{ optional($hbl->airImport)->mawb_no ?? '--' }}</td>
        <td>{{ $hbl->customer->name ?? '--' }}</td>
        <td>{{ $hbl->shipper->name ?? '--' }}</td>
        <td>{{ $hbl->consignee->name ?? '--' }}</td>
        <td style="text-align:right;">{{ $hbl->pkg_qty ? number_format($hbl->pkg_qty, 0) : '0' }}</td>
        <td style="text-align:right;">{{ $hbl->gross_weight ? number_format($hbl->gross_weight, 2) : '0.00' }}</td>
        <td style="text-align:right;">{{ $hbl->chargeable_weight ? number_format($hbl->chargeable_weight, 2) : '0.00' }}</td>
        <td>{{ optional($hbl->airImport)->etd ? \Carbon\Carbon::parse(optional($hbl->airImport)->etd)->format('m-d-Y') : '--' }}</td>
        <td>{{ optional($hbl->airImport)->eta ? \Carbon\Carbon::parse(optional($hbl->airImport)->eta)->format('m-d-Y') : '--' }}</td>
        <td>{{ optional($hbl->airImport->depPort)->name ?? '--' }}</td>
        <td>{{ optional($hbl->airImport->dstPort)->name ?? '--' }}</td>
        <td>{{ $hbl->salesPerson->name ?? '--' }}</td>
        <td>{{ $hbl->op->name ?? '--' }}</td>
        <td>{{ $hbl->created_at ? $hbl->created_at->format('m-d-Y') : '--' }}</td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="19" style="text-align:center;padding:30px 10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
            No House Air Waybills found.
        </td>
    </tr>
@endforelse
