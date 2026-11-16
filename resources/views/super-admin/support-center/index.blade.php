@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-life-ring" style="color: #405189;"></i>
            <span>Support Center</span>
            @if(request()->routeIs('super-admin.support-center.my-tickets'))
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">My Tickets</span>
            @endif
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="{{ url()->current() }}" style="display: flex; gap: 8px; margin: 0; align-items: center;">
                @if(request('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." style="height: 32px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; width: 180px;">
                
                <select name="status" onchange="this.form.submit()" style="height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="">All Statuses</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <select name="priority" onchange="this.form.submit()" style="height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="">All Priorities</option>
                    <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Medium" {{ request('priority') === 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="High" {{ request('priority') === 'High' ? 'selected' : '' }}>High</option>
                    <option value="Urgent" {{ request('priority') === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </form>
            
            <a href="{{ route('super-admin.support-center.create') }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-plus"></i> New Ticket
            </a>
        </div>
    </div>

    {{-- Sub-navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px;">
        <a href="{{ route('super-admin.support-center.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ request()->routeIs('super-admin.support-center.index') ? '600; color: #0ab39c; border-bottom: 2px solid #0ab39c;' : '500; color: #64748b;' }}">
            All Tickets
        </a>
        <a href="{{ route('super-admin.support-center.my-tickets') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ request()->routeIs('super-admin.support-center.my-tickets') ? '600; color: #0ab39c; border-bottom: 2px solid #0ab39c;' : '500; color: #64748b;' }}">
            My Tickets
        </a>
        <a href="{{ route('super-admin.support-center.sla-management') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            SLA Management
        </a>
        <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            Knowledge Base
        </a>
    </div>

    {{-- When on My Tickets: Show Assignment Sub-Tabs --}}
    @if(isset($tabCounts))
    <div style="display: flex; gap: 8px; margin-bottom: 16px;">
        <a href="{{ route('super-admin.support-center.my-tickets', ['tab' => 'assigned']) }}" style="padding: 6px 14px; font-size: 11px; font-weight: 600; text-decoration: none; border-radius: 4px; {{ ($currentTab ?? 'assigned') === 'assigned' ? 'background: #405189; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #cbd5e1;' }}">
            Assigned to Me ({{ $tabCounts['assigned'] }})
        </a>
        <a href="{{ route('super-admin.support-center.my-tickets', ['tab' => 'unassigned']) }}" style="padding: 6px 14px; font-size: 11px; font-weight: 600; text-decoration: none; border-radius: 4px; {{ ($currentTab ?? '') === 'unassigned' ? 'background: #405189; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #cbd5e1;' }}">
            Unassigned Queue ({{ $tabCounts['unassigned'] }})
        </a>
        <a href="{{ route('super-admin.support-center.my-tickets', ['tab' => 'created']) }}" style="padding: 6px 14px; font-size: 11px; font-weight: 600; text-decoration: none; border-radius: 4px; {{ ($currentTab ?? '') === 'created' ? 'background: #405189; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #cbd5e1;' }}">
            Created by Me ({{ $tabCounts['created'] }})
        </a>
    </div>
    @endif

    @if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; display: flex; justify-content: space-between; align-items: center;">
        <div><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</div>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #166534; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 80px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Company & User</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Subject & Category</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 120px;">Status</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 120px;">Priority</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 150px;">Assigned To</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 120px;">SLA Due</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                        <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #405189; text-decoration: none;">
                            #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 500; color: #1e293b;">{{ $ticket->company ? $ticket->company->name : 'N/A' }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $ticket->user->name }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="color: #1e293b; font-weight: 500; margin-bottom: 4px;">{{ $ticket->title }}</div>
                        <div style="font-size: 11px; color: #64748b;">Mod: {{ $ticket->module_name }} {{ $ticket->category ? '| Cat: '.$ticket->category : '' }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        @php
                            $statusColors = [
                                'Open' => ['bg' => '#fef2f2', 'text' => '#ef4444', 'border' => '#fca5a5'],
                                'In Progress' => ['bg' => '#eff6ff', 'text' => '#3b82f6', 'border' => '#93c5fd'],
                                'Resolved' => ['bg' => '#f0fdf4', 'text' => '#22c55e', 'border' => '#86efac'],
                                'Closed' => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#cbd5e1']
                            ];
                            $color = $statusColors[$ticket->status] ?? $statusColors['Open'];
                        @endphp
                        <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['border'] }}; border-radius: 12px; font-size: 11px; font-weight: 500;">
                            {{ $ticket->status }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px;">
                        @php
                            $pColors = [
                                'Low' => '#64748b',
                                'Medium' => '#3b82f6',
                                'High' => '#f59e0b',
                                'Urgent' => '#ef4444'
                            ];
                            $pc = $pColors[$ticket->priority] ?? '#64748b';
                        @endphp
                        <span style="color: {{ $pc }}; font-weight: 600; font-size: 11px;">{{ strtoupper($ticket->priority) }}</span>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        {{ $ticket->assignedAgent ? $ticket->assignedAgent->name : 'Unassigned' }}
                    </td>
                    <td style="padding: 12px 16px;">
                        @if($ticket->sla_due_at)
                            @php
                                $sla = \Carbon\Carbon::parse($ticket->sla_due_at);
                                $isOverdue = $sla->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']);
                            @endphp
                            <div style="font-size: 11px; font-weight: 500; color: {{ $isOverdue ? '#ef4444' : '#1e293b' }};">
                                {{ $sla->format('Y-m-d H:i') }}
                            </div>
                            @if($isOverdue)
                                <div style="font-size: 10px; color: #ef4444; font-weight: 600;">OVERDUE</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #405189; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px; text-decoration: none;" title="View Ticket">
                                <i class="fa fa-eye"></i>
                            </a>
                            @if(!$ticket->assigned_agent_id)
                            <form method="POST" action="{{ route('super-admin.support-center.claim', $ticket) }}" style="margin: 0; display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="height: 28px; padding: 0 8px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer;" title="Assign to Me">
                                    Claim
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 30px; text-align: center; color: #64748b;">
                        <i class="fa fa-inbox" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        No tickets found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $tickets->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
