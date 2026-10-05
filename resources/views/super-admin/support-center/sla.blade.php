@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-clock-o" style="color: #405189; margin-right: 6px;"></i> SLA Management
        </div>
        
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('super-admin.support-center.index') }}" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa fa-list" style="margin-right: 6px;"></i> All Tickets
            </a>
        </div>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 80px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Company & User</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Subject</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 120px;">Status / Priority</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 150px;">Assigned To</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 150px;">SLA Due</th>
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
                        <div style="color: #1e293b; font-weight: 500;">{{ $ticket->title }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="margin-bottom: 4px; font-weight: 500; color: #1e293b;">{{ $ticket->status }}</div>
                        @php
                            $pColors = ['Low' => '#64748b', 'Medium' => '#3b82f6', 'High' => '#f59e0b', 'Urgent' => '#ef4444'];
                            $pc = $pColors[$ticket->priority] ?? '#64748b';
                        @endphp
                        <div style="color: {{ $pc }}; font-weight: 600; font-size: 11px;">{{ strtoupper($ticket->priority) }}</div>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        {{ $ticket->assignedAgent ? $ticket->assignedAgent->name : 'Unassigned' }}
                    </td>
                    <td style="padding: 12px 16px;">
                        @if($ticket->sla_due_at)
                            @php
                                $sla = \Carbon\Carbon::parse($ticket->sla_due_at);
                                $isOverdue = $sla->isPast();
                                $hoursLeft = !$isOverdue ? $sla->diffInHours(now()) : 0;
                            @endphp
                            
                            @if($isOverdue)
                                <div style="font-size: 11px; font-weight: 600; color: #ef4444; background: #fef2f2; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    OVERDUE
                                </div>
                                <div style="font-size: 10px; color: #ef4444; margin-top: 4px;">{{ $sla->format('Y-m-d H:i') }}</div>
                            @elseif($hoursLeft < 4)
                                <div style="font-size: 11px; font-weight: 600; color: #f59e0b; background: #fef3c7; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    DUE SOON
                                </div>
                                <div style="font-size: 10px; color: #f59e0b; margin-top: 4px;">{{ $sla->format('Y-m-d H:i') }}</div>
                            @else
                                <div style="font-size: 11px; font-weight: 600; color: #22c55e; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    ON TRACK
                                </div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">{{ $sla->format('Y-m-d H:i') }}</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #405189; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px; text-decoration: none;" title="Manage SLA">
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #64748b;">
                        <i class="fa fa-check-circle" style="font-size: 24px; color: #22c55e; margin-bottom: 10px; display: block;"></i>
                        No pending SLA tickets. Great job!
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
