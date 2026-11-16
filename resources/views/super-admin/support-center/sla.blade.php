@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Header --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-clock-o" style="color: #405189;"></i>
            <span>SLA Management Console</span>
            <span style="font-size: 10px; font-weight: 700; background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Real-Time SLA</span>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.support-center.index') }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-list"></i> All Tickets
            </a>
            <a href="{{ route('super-admin.support-center.create') }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-plus"></i> New Ticket
            </a>
        </div>
    </div>

    {{-- Sub-navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px;">
        <a href="{{ route('super-admin.support-center.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            All Tickets
        </a>
        <a href="{{ route('super-admin.support-center.my-tickets') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            My Tickets
        </a>
        <a href="{{ route('super-admin.support-center.sla-management') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">
            SLA Management
        </a>
        <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            Knowledge Base
        </a>
    </div>

    {{-- SLA KPI Cards --}}
    @if(isset($kpis))
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 16px;">
        <a href="{{ route('super-admin.support-center.sla-management', ['filter' => 'all']) }}" style="text-decoration: none; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Active SLA Tickets</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $kpis['total'] }}</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-clock-o"></i>
            </div>
        </a>

        <a href="{{ route('super-admin.support-center.sla-management', ['filter' => 'overdue']) }}" style="text-decoration: none; background: #fff; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #991b1b; font-weight: 700; text-transform: uppercase;">SLA Breached (Overdue)</div>
                <div style="font-size: 22px; font-weight: 800; color: #ef4444; margin-top: 4px;">{{ $kpis['overdue'] }}</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-exclamation-circle"></i>
            </div>
        </a>

        <a href="{{ route('super-admin.support-center.sla-management', ['filter' => 'due_soon']) }}" style="text-decoration: none; background: #fff; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #92400e; font-weight: 700; text-transform: uppercase;">Due Soon (< 4 Hours)</div>
                <div style="font-size: 22px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ $kpis['due_soon'] }}</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-hourglass-half"></i>
            </div>
        </a>

        <a href="{{ route('super-admin.support-center.sla-management', ['filter' => 'on_track']) }}" style="text-decoration: none; background: #fff; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #065f46; font-weight: 700; text-transform: uppercase;">On Track Within SLA</div>
                <div style="font-size: 22px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $kpis['on_track'] }}</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-check-circle"></i>
            </div>
        </a>
    </div>
    @endif

    {{-- SLA Tickets Table --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; width: 80px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569;">Company & User</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569;">Subject & Module</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; width: 120px;">Status / Priority</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; width: 150px;">Assigned Agent</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; width: 150px;">SLA Deadline</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 700; color: #475569; width: 90px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 600;">
                        <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #405189; text-decoration: none;">
                            #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 600; color: #1e293b;">{{ $ticket->company ? $ticket->company->name : 'Platform System' }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $ticket->user ? $ticket->user->name : 'Anonymous' }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="color: #1e293b; font-weight: 600; margin-bottom: 2px;">{{ $ticket->title }}</div>
                        <span style="font-size: 10px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 3px;">
                            {{ $ticket->module_name ?: 'General' }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="margin-bottom: 4px; font-weight: 600; color: #1e293b;">{{ $ticket->status }}</div>
                        @php
                            $pColors = ['Low' => '#64748b', 'Medium' => '#3b82f6', 'High' => '#f59e0b', 'Urgent' => '#ef4444'];
                            $pc = $pColors[$ticket->priority] ?? '#64748b';
                        @endphp
                        <span style="color: {{ $pc }}; font-weight: 700; font-size: 10px;">{{ strtoupper($ticket->priority) }}</span>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        @if($ticket->assignedAgent)
                            <div style="font-weight: 500; color: #1e293b;">{{ $ticket->assignedAgent->name }}</div>
                        @else
                            <form method="POST" action="{{ route('super-admin.support-center.claim', $ticket) }}" style="margin: 0; display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="font-size: 10px; font-weight: 600; color: #0284c7; background: #e0f2fe; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer;">
                                    + Assign to Me
                                </button>
                            </form>
                        @endif
                    </td>
                    <td style="padding: 12px 16px;">
                        @if($ticket->sla_due_at)
                            @php
                                $sla = \Carbon\Carbon::parse($ticket->sla_due_at);
                                $isOverdue = $sla->isPast();
                                $hoursLeft = !$isOverdue ? $sla->diffInHours(now()) : 0;
                            @endphp
                            
                            @if($isOverdue)
                                <div style="font-size: 10px; font-weight: 700; color: #991b1b; background: #fee2e2; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    <i class="fa fa-exclamation-circle"></i> OVERDUE
                                </div>
                                <div style="font-size: 10px; color: #ef4444; margin-top: 2px;">{{ $sla->format('M d, H:i') }}</div>
                            @elseif($hoursLeft < 4)
                                <div style="font-size: 10px; font-weight: 700; color: #92400e; background: #fef3c7; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    <i class="fa fa-clock-o"></i> DUE SOON ({{ $hoursLeft }}h)
                                </div>
                                <div style="font-size: 10px; color: #f59e0b; margin-top: 2px;">{{ $sla->format('M d, H:i') }}</div>
                            @else
                                <div style="font-size: 10px; font-weight: 700; color: #065f46; background: #ecfdf5; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                    <i class="fa fa-check"></i> ON TRACK
                                </div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ $sla->format('M d, H:i') }}</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #405189; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px; text-decoration: none;" title="Manage Ticket & SLA">
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #64748b;">
                        <i class="fa fa-check-circle" style="font-size: 24px; color: #22c55e; margin-bottom: 10px; display: block;"></i>
                        No pending SLA tickets for this filter criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($tickets->hasPages())
        <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $tickets->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>
@endsection
