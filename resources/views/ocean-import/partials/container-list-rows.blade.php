@forelse($containers as $c)
<tr data-id="{{ $c->id }}" data-ocean-import-id="{{ $c->ocean_import_id }}" onclick="rowClick(event, this)">

    {{-- ===== STICKY COLS ===== --}}
    <td data-col="check" class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="row-check" onchange="updateToolbar()">
    </td>
    <td data-col="flag" class="sticky-col" style="left:25px;text-align:center;">
        <i class="fa fa-flag" style="color:{{ $c->is_dg ? '#ef4444' : '#cbd5e1' }};font-size:10px;"></i>
    </td>
        <span class="color-mark" style="background:{{ $c->oceanImport?->color ?? '#94a3b8' }}" title="Click to change status color" onclick="event.stopPropagation();openColorPicker({{ $c->oceanImport?->id ?? 0 }}, '{{ $c->oceanImport?->color ?? '' }}')"></span>
    </td>
    <td data-col="container_no" class="sticky-col" style="left:220px;">{{ $c->container_no ?? 'N/A' }}</td>
    <td data-col="consignee" class="sticky-col" style="left:345px;">{{ $c->oceanImport?->dmConsignee?->name ?? 'N/A' }}</td>

    {{-- ===== GENERAL COLS ===== --}}
    <td data-col="remarks">
        <input type="text" class="cell-input" value="{{ $c->remarks ?? '' }}" @change="markChanged({{ $c->id }}, 'remarks', $event.target.value)" placeholder="Take a note...">
    </td>
    <td data-col="stages">
        <span style="color:#3b82f6;font-weight:600;">{{ $c->oceanImport?->ship_mode ?? 'FCL' }}</span>
        <span style="color:#64748b;font-size:9px;">/{{ $c->containerType?->code ?? '' }}</span>
    </td>
    <td data-col="hbl">
        @if($c->oceanImport && $c->oceanImport?->hbls->count())
            {{ $c->oceanImport?->hbls->pluck('hbl_no')->join(', ') }}
        @else
            --
        @endif
    </td>
    <td data-col="location">{{ $c->oceanImport?->cfsLocation?->name ?? ($c->oceanImport?->cyLocation?->name ?? '--') }}</td>
    <td data-col="rail">{{ $c->rail_start_date ? 'Yes' : 'No' }}</td>
    <td data-col="rail_code">{{ $c->it_no ?? '--' }}</td>
    <td data-col="etd">{{ $c->oceanImport && $c->oceanImport?->etd ? $c->oceanImport?->etd->format('m-d-Y') : '--' }}</td>
    <td data-col="eta">{{ $c->oceanImport && $c->oceanImport?->eta ? $c->oceanImport?->eta->format('m-d-Y') : '--' }}</td>
    <td data-col="last_edi">{{ $c->updated_at ? $c->updated_at->format('m-d-Y H:i') : '--' }}</td>

    {{-- ===== CONTAINER FIELDS ===== --}}
    <td data-col="ppctf">{{ $c->pp_ctf ?? '--' }}</td>
    <td data-col="tpsz">{{ $c->containerType?->code ?? '--' }}</td>
    <td data-col="seal_no">
        <input type="text" class="cell-input" value="{{ $c->seal_no ?? '' }}" @change="markChanged({{ $c->id }}, 'seal_no', $event.target.value)">
    </td>
    <td data-col="seal_no2">
        <input type="text" class="cell-input" value="{{ $c->seal_no2 ?? '' }}" @change="markChanged({{ $c->id }}, 'seal_no2', $event.target.value)">
    </td>
    <td data-col="lfd">
        <input type="date" class="cell-input" value="{{ $c->lfd ? $c->lfd->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'lfd', $event.target.value)">
    </td>
    <td data-col="fdd">
        <input type="date" class="cell-input" value="{{ $c->fdd ? $c->fdd->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'fdd', $event.target.value)">
    </td>
    <td data-col="pkg" style="text-align:right;">{{ $c->pkg_qty ?? '--' }}</td>
    <td data-col="weight_kg" style="text-align:right;">{{ $c->weight_kg ? number_format($c->weight_kg, 2) : '--' }}</td>
    <td data-col="weight_lb" style="text-align:right;">{{ $c->weight_lb ? number_format($c->weight_lb, 2) : '--' }}</td>
    <td data-col="measure_cbm" style="text-align:right;">{{ $c->measure_cbm ? number_format($c->measure_cbm, 2) : '--' }}</td>
    <td data-col="measure_cft" style="text-align:right;">{{ $c->measure_cft ? number_format($c->measure_cft, 2) : '--' }}</td>
    <td data-col="dg" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_dg ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_dg', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="unload_vessel">
        <input type="date" class="cell-input" value="{{ $c->unload_vessel_date ? $c->unload_vessel_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'unload_vessel_date', $event.target.value)">
    </td>
    <td data-col="gate_in">
        <input type="date" class="cell-input" value="{{ $c->gate_in_date ? $c->gate_in_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'gate_in_date', $event.target.value)">
    </td>
    <td data-col="rail_start">
        <input type="date" class="cell-input" value="{{ $c->rail_start_date ? $c->rail_start_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'rail_start_date', $event.target.value)">
    </td>
    <td data-col="pod_eta">
        <input type="date" class="cell-input" value="{{ $c->pod_eta ? $c->pod_eta->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'pod_eta', $event.target.value)">
    </td>
    <td data-col="appt">
        <input type="date" class="cell-input" value="{{ $c->appointment_date ? $c->appointment_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'appointment_date', $event.target.value)">
    </td>
    <td data-col="pickup">
        <input type="date" class="cell-input" value="{{ $c->pickup_date ? $c->pickup_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'pickup_date', $event.target.value)">
    </td>
    <td data-col="gate_out">
        <input type="date" class="cell-input" value="{{ $c->gate_out_date ? $c->gate_out_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'gate_out_date', $event.target.value)">
    </td>
    <td data-col="fdest_eta">
        <input type="date" class="cell-input" value="{{ $c->fdest_eta ? $c->fdest_eta->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'fdest_eta', $event.target.value)">
    </td>
    <td data-col="eta_door">
        <input type="date" class="cell-input" value="{{ $c->eta_door ? $c->eta_door->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'eta_door', $event.target.value)">
    </td>
    <td data-col="ata_door">
        <input type="date" class="cell-input" value="{{ $c->ata_door ? $c->ata_door->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'ata_door', $event.target.value)">
    </td>
    <td data-col="empty_conf">
        <input type="date" class="cell-input" value="{{ $c->empty_conf_date ? $c->empty_conf_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'empty_conf_date', $event.target.value)">
    </td>
    <td data-col="empty_ret">
        <input type="date" class="cell-input" value="{{ $c->empty_ret_date ? $c->empty_ret_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'empty_ret_date', $event.target.value)">
    </td>
    <td data-col="storage_start">
        <input type="date" class="cell-input" value="{{ $c->storage_start_date ? $c->storage_start_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'storage_start_date', $event.target.value)">
    </td>
    <td data-col="storage_end">
        <input type="date" class="cell-input" value="{{ $c->storage_end_date ? $c->storage_end_date->format('Y-m-d') : '' }}" @change="markChanged({{ $c->id }}, 'storage_end_date', $event.target.value)">
    </td>
    <td data-col="pick_no">
        <input type="text" class="cell-input" value="{{ $c->pickup_no ?? '' }}" @change="markChanged({{ $c->id }}, 'pickup_no', $event.target.value)">
    </td>
    <td data-col="cprs_no">
        <input type="text" class="cell-input" value="{{ $c->cprs_no ?? '' }}" @change="markChanged({{ $c->id }}, 'cprs_no', $event.target.value)">
    </td>
    <td data-col="cnru_no">
        <input type="text" class="cell-input" value="{{ $c->cnru_no ?? '' }}" @change="markChanged({{ $c->id }}, 'cnru_no', $event.target.value)">
    </td>
    <td data-col="carrier_rel" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_carrier_release ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_carrier_release', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="yard_loc">
        <input type="text" class="cell-input" value="{{ $c->yard_location ?? '' }}" @change="markChanged({{ $c->id }}, 'yard_location', $event.target.value)">
    </td>
    <td data-col="avail_pickup" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_avail_pickup ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_avail_pickup', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="trucker">{{ $c->trucker?->name ?? '--' }}</td>
    <td data-col="chassis_days" style="text-align:right;">
        <input type="number" class="cell-input" style="text-align:right;" value="{{ $c->chassis_days ?? '' }}" @change="markChanged({{ $c->id }}, 'chassis_days', $event.target.value)">
    </td>
    <td data-col="c_hold" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_customs_hold ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_customs_hold', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="an" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_an_sent ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_an_sent', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="do" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_do_sent ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_do_sent', $event.target.checked ? 1 : 0)">
    </td>
    <td data-col="cont_remarks">
        <input type="text" class="cell-input" value="{{ $c->container_remarks ?? '' }}" @change="markChanged({{ $c->id }}, 'container_remarks', $event.target.value)">
    </td>
    <td data-col="complete" style="text-align:center;">
        <input type="checkbox" class="cell-checkbox" {{ $c->is_complete ? 'checked' : '' }} @change="markChanged({{ $c->id }}, 'is_complete', $event.target.checked ? 1 : 0)">
    </td>

    {{-- ===== SHIPMENT FIELDS ===== --}}
    <td data-col="mbl_no">{{ $c->oceanImport?->mbl_no ?? '--' }}</td>
    <td data-col="carrier">{{ $c->oceanImport?->carrier?->name ?? '--' }}</td>
    <td data-col="vessel">{{ $c->oceanImport?->vessel?->name ?? '--' }}</td>
    <td data-col="pol">{{ $c->oceanImport?->portOfLoading?->name ?? '--' }}</td>
    <td data-col="pod">{{ $c->oceanImport?->portOfDischarge?->name ?? '--' }}</td>
    <td data-col="del">{{ $c->oceanImport?->placeOfDelivery?->name ?? '--' }}</td>
    <td data-col="final_dest">{{ $c->oceanImport?->finalDestination?->name ?? '--' }}</td>
    <td data-col="mbl_cy">{{ $c->oceanImport?->cyLocation?->name ?? '--' }}</td>
    <td data-col="office">{{ $c->oceanImport?->office?->name ?? '--' }}</td>
    <td data-col="sales">{{ $c->oceanImport?->salesPerson?->name ?? '--' }}</td>
    <td data-col="operator">{{ $c->oceanImport?->operator?->name ?? '--' }}</td>
    <td data-col="shipper">{{ $c->oceanImport?->dmShipper?->name ?? '--' }}</td>
    <td data-col="notify">{{ $c->oceanImport?->dmNotify?->name ?? '--' }}</td>
    <td data-col="customer">{{ $c->oceanImport?->dmCustomer?->name ?? '--' }}</td>
    <td data-col="voyage">{{ $c->oceanImport?->voyage ?? '--' }}</td>
    <td data-col="ship_mode">{{ $c->oceanImport?->ship_mode ?? '--' }}</td>
    <td data-col="etb">{{ $c->oceanImport?->etb ? $c->oceanImport?->etb->format('m-d-Y') : '--' }}</td>
    <td data-col="obl">{{ $c->oceanImport?->obl_type ?? '--' }}</td>
    <td data-col="freight_term">{{ $c->oceanImport?->freight_term ?? '--' }}</td>
    <td data-col="sales_type">{{ $c->oceanImport?->sales_type ?? '--' }}</td>
    <td data-col="isf_no">{{ $c->oceanImport?->isf_no ?? '--' }}</td>
    <td data-col="isf_3rd" style="text-align:center;">
        @if($c->oceanImport && $c->oceanImport?->is_isf_3rd_party)
            <i class="fa fa-check" style="color:#22c55e;"></i>
        @else
            <i class="fa fa-times" style="color:#ef4444;"></i>
        @endif
    </td>
    <td data-col="isf_matched">{{ $c->oceanImport && $c->oceanImport?->isf_matched_date ? $c->oceanImport?->isf_matched_date->format('m-d-Y') : '--' }}</td>
    <td data-col="entry_no">{{ $c->oceanImport?->entry_no ?? '--' }}</td>
    <td data-col="entry_doc">{{ $c->oceanImport && $c->oceanImport?->entry_doc_sent_date ? $c->oceanImport?->entry_doc_sent_date->format('m-d-Y') : '--' }}</td>
    <td data-col="contract_no">{{ $c->oceanImport?->contract_no ?? '--' }}</td>
    <td data-col="receipt">{{ $c->oceanImport?->receipt?->name ?? '--' }}</td>
    <td data-col="receipt_etd">{{ $c->oceanImport && $c->oceanImport?->receipt_etd ? $c->oceanImport?->receipt_etd->format('m-d-Y') : '--' }}</td>

    {{-- ===== HBL FIELDS ===== --}}
    <td data-col="po_no">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('po_no')->filter()->join(', ') : '--' }}</td>
    <td data-col="express_bl">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('is_express_bl')->map(fn($v) => $v ? 'Yes' : 'No')->join(', ') : '--' }}</td>
    <td data-col="freight_rel">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('is_fr_released')->map(fn($v) => $v ? 'Yes' : 'No')->join(', ') : '--' }}</td>
    <td data-col="customs_doc">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('is_customs_doc')->map(fn($v) => $v ? 'Yes' : 'No')->join(', ') : '--' }}</td>
    <td data-col="c_clearance">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('is_customs_clear')->map(fn($v) => $v ? 'Yes' : 'No')->join(', ') : '--' }}</td>
    <td data-col="delivery_loc">{{ $c->oceanImport && $c->oceanImport?->hbls->count() ? $c->oceanImport?->hbls->pluck('deliveryLocation.name')->filter()->join(', ') : '--' }}</td>

</tr>
@empty
<tr id="empty-row">
    <td colspan="90" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:13px;font-weight:600;">No containers found</div>
        <div style="font-size:11px;margin-top:4px;">Try adjusting your filters or search criteria</div>
    </td>
</tr>
@endforelse
