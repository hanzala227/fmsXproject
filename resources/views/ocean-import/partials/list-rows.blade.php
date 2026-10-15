
@forelse($shipments as $shipment)
<tr id="shipment-row-{{ $shipment->id }}"
    data-id="{{ $shipment->id }}"
    data-file="{{ $shipment->file_no }}"
    data-mbl="{{ $shipment->mbl_no }}"
    data-status="{{ $shipment->status ?? '--' }}"
    data-carrier="{{ $shipment->carrier->name ?? '' }}"
    data-vessel="{{ ($shipment->vessel->name ?? '--') . ' / ' . ($shipment->voyage ?? '--') }}"
    data-pol="{{ $shipment->portOfLoading->name ?? '--' }}"
    data-pod="{{ $shipment->portOfDischarge->name ?? '--' }}"
    data-etd="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}"
    data-eta="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}"`
    data-obl="{{ $shipment->obl_type ?? '--' }}"
    data-bl="{{ $shipment->bl_type ?? '--' }}"
    data-containers="{{ $shipment->containers->count() }}"
    data-hbls="{{ $shipment->hbls->count() }}"
    data-customer="{{ $shipment->dmCustomer->name ?? '--' }}"
    onclick="rowClick(event, this)"
>
    {{-- Checkbox --}}
    <td class="sticky-col" style="width:25px;text-align:center;"
        onclick="event.stopPropagation()">
        <input type="checkbox"
               name="ids[]"
               value="{{ $shipment->id }}"
               class="row-check"
               onchange="updateToolbar()">
    </td>

    {{-- Lock --}}
    <td class="sticky-col"
        style="width:25px;left:25px;text-align:center;"
        onclick="event.stopPropagation()">
        <i class="fa {{ $shipment->is_hold ? 'fa-lock' : 'fa-unlock' }}"
           style="color:{{ $shipment->is_hold ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;"
           title="{{ $shipment->is_hold ? 'Lock' : 'Unlock' }}"
           onclick="toggleLock(this)"></i>
    </td>

    {{-- Booking No. --}}
    <td class="sticky-col"
        style="width:110px;left:30px;"
        onclick="event.stopPropagation()">
        <a href="{{ route('ocean-import.edit', $shipment->id) }}"
           class="col-link">
            {{ $shipment->file_no }}
        </a>
    </td>
    {{-- Booking no --}}
    <td class="sticky-col" style="width:110px;left:90px;" onclick="event.stopPropagation()">
        <a href="{{ route('ocean-import.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
    </td>
    {{-- MBL + Eye --}}
    <td class="sticky-col" style="width:150px;left:200px;" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span>{{ $shipment->mbl_no }}</span>

            <i class="fa fa-eye"
               style="color:#3b82f6;cursor:pointer;font-size:10px;"
               title="Quick View MBL"
               onclick="showMbl({
                   file_no: '{{ addslashes($shipment->file_no ?? '') }}',
                   mbl_no: '{{ addslashes($shipment->mbl_no ?? '') }}',
                   carrier: '{{ addslashes($shipment->carrier->name ?? "--") }}',
                   vessel: '{{ addslashes(($shipment->vessel->name ?? "--") . " / " . ($shipment->voyage ?? "--")) }}',
                   pol: '{{ addslashes($shipment->portOfLoading->name ?? "--") }}',
                   pod: '{{ addslashes($shipment->portOfDischarge->name ?? "--") }}',
                   etd: '{{ $shipment->etd ? $shipment->etd->format("m-d-Y") : "--" }}',
                   eta: '{{ $shipment->eta ? $shipment->eta->format("m-d-Y") : "--" }}',
                   obl_type: '{{ addslashes($shipment->obl_type ?? "--") }}',
                   bl_type: '{{ addslashes($shipment->bl_type ?? "--") }}',
                   containers: {{ $shipment->containers->count() }},
                   hbls:       {{ $shipment->hbls->count() }},
                   routes:     @json($shipment->routes ?? [])
               })"></i>
        </div>
    </td>

    {{-- Tracking EDI Response --}}
    <td>
        {{ $shipment->updated_at
            ? $shipment->updated_at->format('m-d-Y H:i')
            : '--' }}
    </td>

    {{-- Submit Status --}}
    <td>
        <span class="badge-status bg-green">MATCHED</span>
    </td>

    {{-- Actual Shipment Status --}}
    <td>
        @php
            $shipmentStatus = $shipment->status ?? '--';

            $statusClass = match (strtolower((string) $shipmentStatus)) {
                'delivered', 'completed', 'closed' => 'bg-green',
                'in transit', 'in_transit', 'shipped' => 'bg-blue',
                'pending', 'booked' => 'bg-yellow',
                'cancelled', 'canceled', 'rejected' => 'bg-red',
                default => 'bg-gray',
            };
        @endphp

        <span class="badge-status {{ $statusClass }}">
            {{ ucfirst(str_replace('_', ' ', $shipmentStatus)) }}
        </span>
    </td>

    {{-- Office --}}
    <td>{{ $shipment->office->code ?? '--' }}</td>

    {{-- HBLs --}}
    <td>{{ $shipment->hbls->count() }} HBLs</td>

    {{-- Container Count --}}
    <td style="text-align:right;">
        {{ $shipment->containers->count() }}
    </td>

    {{-- Container / Qty --}}
    <td>
        @foreach($shipment->containers->take(2) as $c)
            {{ $c->containerType->code ?? '' }}*1&nbsp;
        @endforeach
    </td>

    {{-- Consignee --}}
    <td>{{ $shipment->dmConsignee->name ?? '--' }}</td>

    {{-- ETD --}}
    <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>

    {{-- ETA --}}
    <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>

    {{-- OBL Type --}}
    <td>{{ $shipment->obl_type ?? '--' }}</td>

    {{-- BL Type --}}
    <td>{{ $shipment->bl_type ?? '--' }}</td>

    {{-- POL --}}
    <td>{{ $shipment->portOfLoading->name ?? '--' }}</td>

    {{-- POD --}}
    <td>{{ $shipment->portOfDischarge->name ?? '--' }}</td>

    {{-- Place of Delivery --}}
    <td>{{ $shipment->placeOfDelivery->name ?? '--' }}</td>

    {{-- Final Destination --}}
    <td>{{ $shipment->finalDestination->name ?? '--' }}</td>

    {{-- Oversea Agent --}}
    <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>

    {{-- Customer --}}
    <td>{{ $shipment->dmCustomer->name ?? '--' }}</td>

    {{-- Operator --}}
    <td>{{ $shipment->operator->name ?? '--' }}</td>

    {{-- Package Quantity --}}
    <td style="text-align:right;">
        {{ $shipment->containers->sum('pkg_qty') }}
    </td>

    {{-- Weight --}}
    <td style="text-align:right;">
        {{ number_format($shipment->containers->sum('weight_kg'), 2) }}
    </td>

    {{-- CBM --}}
    <td style="text-align:right;">
        {{ number_format($shipment->containers->sum('measure_cbm'), 2) }}
    </td>

    {{-- Freight Term --}}
    <td>{{ $shipment->freight_term ?? '--' }}</td>

    {{-- Freight --}}
    <td>{{ $shipment->freight_term ?? '--' }}</td>

    {{-- LFD --}}
    <td>
        {{ $shipment->containers->max('lfd')
            ? \Carbon\Carbon::parse($shipment->containers->max('lfd'))->format('m-d-Y')
            : '--' }}
    </td>

    {{-- Vessel / Voyage --}}
    <td>{{ ($shipment->vessel->name ?? '--') }} / {{ $shipment->voyage ?? '--' }}</td>

    {{-- Container No. --}}
    <td>{{ $shipment->containers->first()?->container_no ?? '--' }}</td>

    {{-- BL Type --}}
    <td>{{ $shipment->bl_type ?? '--' }}</td>

    {{-- Sub BL No. --}}
    <td>{{ $shipment->sub_bl_no ?? '--' }}</td>

    {{-- Available Date --}}
    <td>{{ $shipment->available_date?->format('m-d-Y') ?? '--' }}</td>

    {{-- Go Date --}}
    <td>{{ $shipment->go_date?->format('m-d-Y') ?? '--' }}</td>

    {{-- Trucker --}}
    <td>{{ $shipment->trucker->name ?? '--' }}</td>

    {{-- Entry No. --}}
    <td>{{ $shipment->entry_no ?? '--' }}</td>

    {{-- POD ETA --}}
    <td>
        {{ $shipment->containers->max('pod_eta')
            ? \Carbon\Carbon::parse($shipment->containers->max('pod_eta'))->format('m-d-Y')
            : '--' }}
    </td>

    <td>--</td>

    {{-- Final ETA --}}
    <td>{{ $shipment->final_eta?->format('m-d-Y') ?? '--' }}</td>

    <td>--</td>

    {{-- Hold --}}
    <td style="text-align:center;">
        @if($shipment->is_hold)
            <i class="fa fa-check" style="color:#22c55e;"></i>
        @else
            <i class="fa fa-times" style="color:#ef4444;"></i>
        @endif
    </td>

    {{-- Entry Document Sent Date --}}
    <td>{{ $shipment->entry_doc_sent_date?->format('m-d-Y') ?? '--' }}</td>

    {{-- AMS --}}
    <td>{{ $shipment->ams_no ?? '--' }}</td>

    {{-- ISF --}}
    <td>{{ $shipment->isf_no ?? '--' }}</td>

    {{-- Incoterm --}}
    <td>{{ $shipment->incoterm->code ?? '--' }}</td>

    {{-- Post Date --}}
    <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
</tr>

@empty
<tr id="empty-row">
    <td colspan="51"
        style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox"
           style="font-size:28px;display:block;margin-bottom:8px;"></i>
        No shipments found.
    </td>
</tr>
@endforelse
