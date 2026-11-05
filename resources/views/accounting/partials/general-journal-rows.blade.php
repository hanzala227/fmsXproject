@forelse($entries as $entry)
    @php
        $totalDebit  = $entry->lines->sum('local_debit');
        $totalCredit = $entry->lines->sum('local_credit');
        $isBalanced  = abs($totalDebit - $totalCredit) < 0.01;
        $statusClass = match($entry->status) {
            'POSTED' => 'badge-posted',
            'DRAFT'  => 'badge-draft',
            'VOIDED' => 'badge-voided',
            default  => 'badge-posted',
        };
    @endphp
    <tr data-id="{{ $entry->id }}" data-status="{{ $entry->status ?? 'POSTED' }}" onclick="rowClick(event, this)" style="cursor:pointer;">
        <td style="text-align:center;"><input type="checkbox" class="row-check" value="{{ $entry->id }}" onclick="event.stopPropagation(); updateToolbar()"></td>
        <td style="text-align:center;">
            @if($entry->status === 'POSTED')
                <i class="fa fa-check-circle" style="color:#22c55e;font-size:10px;" title="Posted"></i>
            @elseif($entry->status === 'DRAFT')
                <i class="fa fa-clock-o" style="color:#f59e0b;font-size:10px;" title="Draft"></i>
            @else
                <i class="fa fa-ban" style="color:#ef4444;font-size:10px;" title="Voided"></i>
            @endif
        </td>
        <td>
            <a href="{{ route('accounting.journal.entry', ['id' => $entry->id]) }}" onclick="event.stopPropagation()" class="col-link" style="color:#2563eb;font-weight:600;text-decoration:none;" title="Open in Journal Entry Form">
                {{ $entry->entry_date?->format('Y-m-d') }}
            </a>
        </td>
        <td style="text-align:center;color:#64748b;font-size:10px;">{{ $entry->id }}</td>
        <td style="font-weight:600;color:#3b82f6;">
            <a href="{{ route('accounting.journal.entry', ['id' => $entry->id]) }}" onclick="event.stopPropagation()" class="col-link" style="color:#2563eb;font-weight:600;text-decoration:none;" title="Open in Journal Entry Form">
                {{ $entry->entry_no }}
            </a>
        </td>
        <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $entry->remark ?? $entry->description ?? '' }}">
            {{ Str::limit($entry->remark ?? $entry->description ?? '', 60) }}
        </td>
        <td style="text-align:right;font-family:'Courier New',monospace;font-weight:600;color:{{ $totalDebit > 0 ? '#1e293b' : '#94a3b8' }};">
            {{ number_format($totalDebit, 2) }}
        </td>
        <td style="text-align:right;font-family:'Courier New',monospace;font-weight:600;color:{{ $totalCredit > 0 ? '#1e293b' : '#94a3b8' }};">
            {{ number_format($totalCredit, 2) }}
        </td>
        <td style="text-align:center;">
            @if(!$isBalanced && ($totalDebit > 0 || $totalCredit > 0))
                <i class="fa fa-exclamation-triangle" style="color:#ef4444;font-size:10px;" title="Out of balance"></i>
            @elseif($totalDebit > 0)
                <i class="fa fa-check" style="color:#22c55e;font-size:10px;" title="Balanced"></i>
            @endif
        </td>
        <td style="text-align:center;">
            <span class="gj-badge {{ $statusClass }}">{{ $entry->status ?? 'POSTED' }}</span>
        </td>
        <td style="font-size:10px;color:#475569;">{{ $entry->lines->count() }}</td>
        <td style="font-size:10px;color:#475569;">{{ $entry->creator?->name ?? '—' }}</td>
        <td style="font-size:10px;color:#475569;">{{ $entry->office?->code ?? $entry->office?->name ?? '—' }}</td>
        <td style="text-align:center;">
            <a href="{{ route('accounting.journal.show', $entry->id) }}" onclick="event.stopPropagation()"
               class="btn-action-round white" style="font-size:9px;padding:0 6px;height:18px;"
               title="View / Edit Entry">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="14" style="text-align:center;padding:28px;color:#94a3b8;font-size:11px;">
            <i class="fa fa-book" style="font-size:18px;display:block;margin-bottom:6px;"></i>
            No journal entries found. Adjust your filters or <a href="{{ route('accounting.journal.entry') }}" style="color:#3b82f6;">create a new entry</a>.
        </td>
    </tr>
@endforelse
