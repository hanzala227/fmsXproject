@forelse($bookings as $b)
<tr id="booking-row-{{ $b->id }}"
    data-id="{{ $b->id }}"
    data-booking="{{ $b->booking_no }}"
    data-customer="{{ $b->customer->name ?? '' }}"
    data-carrier="{{ $b->carrier->name ?? '' }}"
    data-vessel="{{ $b->vessel->name ?? '' }}"
    data-pol="{{ $b->pol->name ?? '' }}"
    data-pod="{{ $b->pod->name ?? '' }}"
    data-etd="{{ $b->etd ? $b->etd->format('m-d-Y') : '' }}"
    data-eta="{{ $b->eta ? $b->eta->format('m-d-Y') : '' }}"
    data-status="{{ $b->status }}"
    onclick="rowClick(event, this)">
    
    {{-- Checkbox --}}
    <td class="sticky-col" style="left:0;width:25px;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" name="ids[]" value="{{ $b->id }}" class="row-check" onchange="updateToolbar()">
    </td>
    {{-- Booking No. --}}
    <td class="sticky-col" style="left:25px;" onclick="event.stopPropagation()">
        <a href="{{ route('ocean-bookings.edit', $b->id) }}" class="col-link">{{ $b->booking_no }}</a>
    </td>
    {{-- Customer --}}
    <td class="sticky-col" style="left:148px;">{{ $b->customer->name ?? '--' }}</td>
    {{-- Office --}}
    <td>{{ $b->office->code ?? '--' }}</td>
    {{-- Carrier --}}
    <td>{{ $b->carrier->name ?? '--' }}</td>
    {{-- Carrier Bkg No. --}}
    <td>{{ $b->carrier_bkg_no ?? '--' }}</td>
    {{-- Agent --}}
    <td>{{ $b->hblAgent->name ?? ($b->shipping_agent ?? '--') }}</td>
    {{-- Vessel --}}
    <td>{{ $b->vessel->name ?? '--' }}</td>
    {{-- Voyage --}}
    <td>{{ $b->voyage ?? '--' }}</td>
    {{-- ETD --}}
    <td>{{ $b->etd ? $b->etd->format('m-d-Y') : '--' }}</td>
    {{-- ETA --}}
    <td>{{ $b->eta ? $b->eta->format('m-d-Y') : '--' }}</td>
    {{-- POL --}}
    <td>{{ $b->pol->name ?? '--' }}</td>
    {{-- POD --}}
    <td>{{ $b->pod->name ?? '--' }}</td>
    {{-- POR --}}
    <td>{{ $b->por->name ?? '--' }}</td>
    {{-- DEL --}}
    <td>{{ $b->del->name ?? '--' }}</td>
    {{-- OP --}}
    <td>{{ $b->op->name ?? '--' }}</td>
    {{-- Sales --}}
    <td>{{ $b->salesPerson->name ?? '--' }}</td>
    {{-- Status --}}
    <td>
        <span class="badge-status badge-{{ strtolower($b->status) }}">{{ $b->status }}</span>
    </td>
    {{-- Booking Date --}}
    <td>{{ $b->booking_date ? $b->booking_date->format('m-d-Y') : '--' }}</td>
    {{-- Incoterms --}}
    <td>{{ $b->incoterms ?? '--' }}</td>
    {{-- Container --}}
    <td>{{ $b->container_no ?? '--' }}</td>
    {{-- Pkg Qty --}}
    <td style="text-align:right;">{{ $b->pkg_qty ? number_format($b->pkg_qty, 2) : '--' }}</td>
    {{-- Weight --}}
    <td style="text-align:right;">{{ $b->weight_kg ? number_format($b->weight_kg, 2) : '--' }}</td>
    {{-- Measure --}}
    <td style="text-align:right;">{{ $b->measure_cbm ? number_format($b->measure_cbm, 2) : '--' }}</td>
</tr>
@empty
<tr id="empty-row">
    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:13px;font-weight:600;">No bookings found</div>
        <div style="font-size:11px;margin-top:4px;">Try adjusting your filters or search criteria</div>
    </td>
</tr>
@endforelse
