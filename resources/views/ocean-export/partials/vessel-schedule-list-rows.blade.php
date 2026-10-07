@forelse($schedules as $s)
<tr id="schedule-row-{{ $s->id }}"
    data-id="{{ $s->id }}"
    data-schedule="{{ $s->schedule_no }}"
    data-customer="{{ $s->customer->name ?? '' }}"
    data-vessel="{{ $s->vessel->name ?? '' }}"
    data-voyage="{{ $s->voyage ?? '' }}"
    data-pol="{{ $s->pol->name ?? '' }}"
    data-pod="{{ $s->pod->name ?? '' }}"
    data-etd="{{ $s->etd ? $s->etd->format('m-d-Y') : '' }}"
    data-eta="{{ $s->eta ? $s->eta->format('m-d-Y') : '' }}"
    onclick="rowClick(event, this)">
    
    <td class="sticky-col" style="left:0;width:25px;text-align:center;" onclick="event.stopPropagation()">
        <input type="checkbox" name="ids[]" value="{{ $s->id }}" class="row-check" onchange="updateToolbar()">
    </td>
    <td class="sticky-col" style="left:25px;" onclick="event.stopPropagation()">
        <a href="{{ route('vessel-schedules.edit', $s->id) }}" class="col-link">{{ $s->schedule_no ?? 'VS-' . $s->id }}</a>
    </td>
    <td class="sticky-col" style="left:178px;">{{ $s->customer->name ?? '--' }}</td>
    <td>{{ $s->office->code ?? '--' }}</td>
    <td>{{ $s->vessel->name ?? ($s->vessel_name ?? '--') }}</td>
    <td>{{ $s->voyage ?? '--' }}</td>
    <td>{{ $s->etd ? $s->etd->format('m-d-Y') : '--' }}</td>
    <td>{{ $s->eta ? $s->eta->format('m-d-Y') : '--' }}</td>
    <td>{{ $s->pol->name ?? ($s->pol_name ?? '--') }}</td>
    <td>{{ $s->pod->name ?? ($s->pod_name ?? '--') }}</td>
    <td>{{ $s->fdest->name ?? '--' }}</td>
    <td>{{ $s->por->name ?? '--' }}</td>
    <td>{{ $s->del->name ?? '--' }}</td>
    <td>{{ $s->carrier_bkg_no ?? '--' }}</td>
    <td>{{ $s->carrier->name ?? '--' }}</td>
    <td>{{ $s->overseaAgent->name ?? '--' }}</td>
    <td>{{ $s->forwardingAgent->name ?? ($s->shipping_agent ?? '--') }}</td>
    <td>{{ $s->op->name ?? '--' }}</td>
    <td>{{ $s->svcTermFrom->code ?? '--' }}</td>
    <td>{{ $s->svcTermTo->code ?? '--' }}</td>
    <td>{{ $s->cargo_type ?? '--' }}</td>
    <td>{{ $s->ship_mode ?? '--' }}</td>
    <td>{{ $s->post_date ? $s->post_date->format('m-d-Y') : '--' }}</td>
</tr>
@empty
<tr id="empty-row">
    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
        <i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:13px;font-weight:600;">No vessel schedules found</div>
        <div style="font-size:11px;margin-top:4px;">Try adjusting your filters or search criteria</div>
    </td>
</tr>
@endforelse
