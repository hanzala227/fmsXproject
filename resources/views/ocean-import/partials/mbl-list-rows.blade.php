@forelse($shipments as $shipment)
<tr id="shipment-row-{{ $shipment->id }}"
    data-id="{{ $shipment->id }}"
    data-file="{{ $shipment->file_no }}"
    data-mbl="{{ $shipment->mbl_no }}"
    data-carrier="{{ $shipment->carrier->name ?? '' }}"
    data-vessel="{{ ($shipment->vessel->name ?? '--') . ' / ' . ($shipment->voyage ?? '--') }}"
    data-pol="{{ $shipment->portOfLoading->name ?? '--' }}"
    data-pod="{{ $shipment->portOfDischarge->name ?? '--' }}"
    data-etd="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}"
    data-eta="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}"
    data-obl="{{ $shipment->obl_type ?? '--' }}"
    data-bl="{{ $shipment->bl_type ?? '--' }}"
    data-containers="{{ $shipment->containers->count() }}"
    data-hbls="{{ $shipment->hbls->count() }}"
    data-customer="{{ $shipment->dmCustomer->name ?? '--' }}"
    onclick="rowClick(event, this)">
    
    <!-- 1. Selection Checkbox -->
    <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" name="ids[]" value="{{ $shipment->id }}" class="row-check" onchange="updateToolbar()">
    </td>
    <!-- 2. Lock Status -->
    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
        <i class="fa {{ $shipment->is_hold ? 'fa-lock' : 'fa-unlock' }}" 
           style="color:{{ $shipment->is_hold ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
           title="{{ $shipment->is_hold ? 'Locked' : 'Unlocked' }}" 
           onclick="toggleLock(this)"></i>
    </td>

    <!-- [REMOVED: VIEW BUTTON COLUMN WAS HERE] -->

    <!-- 3. File Number -->
    <td class="sticky-col" style="left:50px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;">
            <a href="{{ route('ocean-import.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open in new tab"></i>
        </div>
    </td>
    <td class="sticky-col" style="left:160px;" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;">
            <span>{{ $shipment->mbl_no }}</span>
            <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" title="Quick view MBL" onclick="event.stopPropagation();showMbl({
                file_no: '{{ addslashes($shipment->file_no) }}',
                mbl_no: '{{ addslashes($shipment->mbl_no) }}',
                carrier: '{{ addslashes($shipment->carrier->name ?? '--') }}',
                vessel: '{{ addslashes(($shipment->vessel->name ?? '--') . ' / ' . ($shipment->voyage ?? '--')) }}',
                pol: '{{ addslashes($shipment->portOfLoading->name ?? '--') }}',
                pod: '{{ addslashes($shipment->portOfDischarge->name ?? '--') }}',
                etd: '{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}',
                eta: '{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}',
                obl_type: '{{ addslashes($shipment->obl_type ?? '--') }}',
                bl_type: '{{ addslashes($shipment->bl_type ?? '--') }}',
                containers: {{ $shipment->containers->count() }},
                hbls: {{ $shipment->hbls->count() }}
            })"></i>
        </div>
    </td>

    <td>{{ $shipment->updated_at->format('m-d-Y H:i') }}</td>
    <td><span class="badge-status bg-green">MATCHED</span></td>
    
    <!-- [REMOVED: HBL COUNT / LINK COLUMN WAS HERE] -->

    <td style="text-align:right;">{{ $shipment->containers->count() }}</td>
    <td>
        @foreach($shipment->containers->take(2) as $c)
            {{ $c->containerType->code ?? '' }}*1&nbsp;
        @endforeach
    </td>
    <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>
    <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>
    <td>{{ $shipment->portOfLoading->name ?? '--' }}</td>
    <td>{{ $shipment->portOfDischarge->name ?? '--' }}</td>
    <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>
    <td>{{ $shipment->dmCustomer->name ?? '--' }}</td>
    <td>{{ $shipment->containers->first()->container_no ?? '--' }}</td>
    <td>{{ $shipment->vessel->name ?? '--' }}</td>
    <td>{{ $shipment->voyage ?? '--' }}</td>
    <td>{{ $shipment->obl_type ?? '--' }}</td>
    <td>{{ $shipment->bl_type ?? '--' }}</td>
    <td style="text-align:right;">{{ $shipment->containers->sum('pkg_qty') }}</td>
    <td style="text-align:right;">{{ number_format($shipment->containers->sum('weight_kg'), 2) }}</td>
    <td style="text-align:right;">{{ number_format($shipment->containers->sum('measure_cbm'), 2) }}</td>
    <td>{{ $shipment->freight_term ?? '--' }}</td>
    <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
</tr>
@empty
<tr id="empty-row">
    <!-- Updated colspan to 48 (assuming you had 50 originally and removed 2) -->
    <td colspan="48" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
        No Master B/L found.
    </td>
</tr>
@endforelse