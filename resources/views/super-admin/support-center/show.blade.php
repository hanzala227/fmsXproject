@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <a href="{{ route('super-admin.support-center.index') }}" style="color: #64748b; text-decoration: none; margin-right: 8px;"><i class="fa fa-arrow-left"></i></a>
            Ticket #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}: {{ $ticket->title }}
        </div>
        
        <div style="display: flex; gap: 12px;">
            <span style="display: inline-flex; align-items: center; padding: 0 12px; height: 32px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-weight: 500; color: #475569;">
                Status: {{ $ticket->status }}
            </span>
            <span style="display: inline-flex; align-items: center; padding: 0 12px; height: 32px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-weight: 500; color: #475569;">
                Priority: {{ $ticket->priority }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; display: flex; justify-content: space-between; align-items: center;">
        <div><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</div>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #166534; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left Column: Conversation -->
        <div>
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 20px;">
                <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-radius: 6px 6px 0 0;">
                    <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Original Request</div>
                    <div style="font-size: 11px; color: #64748b;">{{ $ticket->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div style="padding: 16px; font-size: 13px; color: #334155; line-height: 1.5; white-space: pre-wrap;">{{ $ticket->description }}</div>
            </div>

            <div style="margin-bottom: 20px;">
                @foreach($ticket->messages as $msg)
                <div style="background: {{ $msg->is_internal ? '#fef3c7' : '#fff' }}; border: 1px solid {{ $msg->is_internal ? '#fde68a' : '#e2e8f0' }}; border-radius: 6px; margin-bottom: 12px;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid {{ $msg->is_internal ? '#fde68a' : '#e2e8f0' }}; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 12px;">
                            {{ $msg->user->name }}
                            @if($msg->is_internal)
                                <span style="background: #f59e0b; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 8px;">Internal Note</span>
                            @endif
                        </div>
                        <div style="font-size: 11px; color: #64748b;">{{ $msg->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div style="padding: 16px; font-size: 13px; color: #334155; line-height: 1.5; white-space: pre-wrap;">{{ $msg->message }}</div>
                </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('super-admin.support-center.reply', $ticket->id) }}" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px;">
                @csrf
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Add Reply or Internal Note</label>
                    <textarea name="message" required rows="4" placeholder="Type your message here..." style="width: 100%; padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; resize: vertical;"></textarea>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #475569; cursor: pointer;">
                        <input type="checkbox" name="is_internal" value="1">
                        Internal Note (Hidden from Customer)
                    </label>
                    <button type="submit" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fa fa-paper-plane" style="margin-right: 6px;"></i> Send
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Ticket Properties -->
        <div>
            <form method="POST" action="{{ route('super-admin.support-center.update', $ticket->id) }}" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
                @csrf
                @method('PUT')
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Ticket Details</div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Customer / Company</label>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 500;">{{ $ticket->user->name }}</div>
                    <div style="font-size: 12px; color: #475569;">{{ $ticket->company ? $ticket->company->name : 'No Company' }}</div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Module / Category</label>
                    <div style="font-size: 13px; color: #1e293b;">{{ $ticket->module_name }}</div>
                    <div style="font-size: 12px; color: #475569;">{{ $ticket->category ?? 'None' }}</div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">SLA Due</label>
                    <div style="font-size: 13px; color: #1e293b;">
                        {{ $ticket->sla_due_at ? \Carbon\Carbon::parse($ticket->sla_due_at)->format('Y-m-d H:i') : 'Not Set' }}
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Status</label>
                    <select name="status" style="width: 100%; height: 32px; padding: 0 8px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff;">
                        <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="In Progress" {{ $ticket->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Priority</label>
                    <select name="priority" style="width: 100%; height: 32px; padding: 0 8px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff;">
                        <option value="Low" {{ $ticket->priority == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ $ticket->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ $ticket->priority == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Urgent" {{ $ticket->priority == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Assigned Agent</label>
                    <select name="assigned_agent_id" style="width: 100%; height: 32px; padding: 0 8px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff;">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $ticket->assigned_agent_id == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" style="width: 100%; height: 32px; font-size: 12px; font-weight: 500; color: #405189; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">
                    <i class="fa fa-save" style="margin-right: 6px;"></i> Update Ticket
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
