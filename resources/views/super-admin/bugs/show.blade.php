@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Breadcrumb & Quick Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('super-admin.bugs.index') }}" 
               style="width: 36px; height: 36px; border-radius: 8px; background: #fff; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; color: #475569; text-decoration: none; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s;"
               onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#fff';">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-family: monospace; font-size: 13px; font-weight: 700; color: #405189; background: #e0e7ff; padding: 2px 8px; border-radius: 4px;">
                        #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    <h1 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">
                        {{ $ticket->title }}
                    </h1>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                    Logged on {{ $ticket->created_at->format('M d, Y h:i A') }} ({{ $ticket->created_at->diffForHumans() }})
                </div>
            </div>
        </div>

        {{-- Top Status Pill --}}
        @php
            $statuses = [
                'Open'        => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'dot' => '#f59e0b'],
                'In Progress' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'dot' => '#3b82f6'],
                'Resolved'    => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'dot' => '#10b981'],
                'Closed'      => ['bg' => '#f8fafc', 'color' => '#64748b', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
            ];
            $stat = $statuses[$ticket->status] ?? $statuses['Open'];
        @endphp
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: {{ $stat['bg'] }}; color: {{ $stat['color'] }}; border: 1px solid {{ $stat['border'] }}; font-size: 12px; font-weight: 700;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $stat['dot'] }};"></span>
                {{ $ticket->status }}
            </span>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <i class="fa fa-check-circle" style="color: #059669; font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Two Column Layout --}}
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
        
        {{-- LEFT COLUMN: Details, Attachments & Discussion --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- 1. Main Issue Details Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-align-left" style="color: #405189; margin-right: 4px;"></i> Issue Description & Diagnostic Context
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span style="padding: 3px 8px; border-radius: 6px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700;">
                            <i class="fa fa-cube"></i> {{ $ticket->module_name ?? 'General Freight' }}
                        </span>
                        @if($ticket->category)
                        <span style="padding: 3px 8px; border-radius: 6px; background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600;">
                            <i class="fa fa-tag"></i> {{ $ticket->category }}
                        </span>
                        @endif
                    </div>
                </div>

                <div style="padding: 20px; color: #334155; font-size: 14px; line-height: 1.7;">
                    <div style="background: #fbfcfd; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; font-size: 13.5px; color: #1e293b; white-space: pre-wrap; font-family: inherit;">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>
                </div>
            </div>

            {{-- 2. Attachments Card --}}
            @if($ticket->attachments->count() > 0)
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-paperclip" style="color: #0284c7;"></i>
                    <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        Attached Evidence & Screenshots ({{ $ticket->attachments->count() }})
                    </span>
                </div>

                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;">
                        @foreach($ticket->attachments as $attachment)
                        @php
                            $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $attachment->file_name);
                            $url = str_starts_with($attachment->file_path, 'uploads/') 
                                ? asset($attachment->file_path) 
                                : asset('storage/' . $attachment->file_path);
                        @endphp
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                            @if($isImg)
                            <a href="{{ $url }}" target="_blank" style="display: block; height: 120px; background: #f1f5f9; overflow: hidden; text-decoration: none;">
                                <img src="{{ $url }}" alt="{{ $attachment->file_name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div style=\'height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:24px;\'><i class=\'fa fa-image\'></i></div>'">
                            </a>
                            @else
                            <a href="{{ $url }}" target="_blank" style="height: 120px; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b; text-decoration: none;">
                                <i class="fa fa-file-pdf-o" style="font-size: 32px; color: #ef4444; margin-bottom: 6px;"></i>
                                <span style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Freight Doc</span>
                            </a>
                            @endif
                            <div style="padding: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-size: 11px; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;" title="{{ $attachment->file_name }}">
                                    {{ $attachment->file_name }}
                                </div>
                                <a href="{{ $url }}" download style="color: #3b82f6; font-size: 12px; text-decoration: none;" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. Communication & Resolution Thread --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-comments-o" style="color: #405189; font-size: 15px;"></i>
                        <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            Diagnostic Conversation Thread ({{ $ticket->messages->count() }})
                        </span>
                    </div>
                    <span style="font-size: 11px; color: #94a3b8;">SuperAdmin & Tenant Support</span>
                </div>

                {{-- Messages List --}}
                <div style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    @forelse($ticket->messages as $msg)
                    @php
                        $isSuperAdmin = ($msg->user && ($msg->user->company_id === null || $msg->user->hasRole('SuperAdmin')));
                    @endphp
                    <div style="display: flex; gap: 12px; align-items: flex-start; {{ $isSuperAdmin ? 'flex-direction: row-reverse;' : '' }}">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: {{ $isSuperAdmin ? '#405189' : '#0284c7' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                            {{ strtoupper(substr(optional($msg->user)->name ?? 'U', 0, 1)) }}
                        </div>
                        <div style="max-width: 80%; background: {{ $isSuperAdmin ? '#eff6ff' : '#f8fafc' }}; border: 1px solid {{ $isSuperAdmin ? '#bfdbfe' : '#e2e8f0' }}; border-radius: 10px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 12px; font-weight: 700; color: #1e293b;">{{ optional($msg->user)->name ?? 'System' }}</span>
                                    <span style="font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px; background: {{ $isSuperAdmin ? '#dbeafe' : '#e2e8f0' }}; color: {{ $isSuperAdmin ? '#1e40af' : '#475569' }};">
                                        {{ $isSuperAdmin ? 'Super Admin' : 'Tenant Operator' }}
                                    </span>
                                </div>
                                <span style="font-size: 10px; color: #94a3b8;">{{ $msg->created_at->format('M d, h:i A') }}</span>
                            </div>
                            <div style="font-size: 13px; color: #334155; line-height: 1.5; white-space: pre-wrap;">{{ $msg->message }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 24px; color: #94a3b8; font-size: 12px;">
                        <i class="fa fa-comment-o" style="font-size: 24px; opacity: 0.5; margin-bottom: 8px; display: block;"></i>
                        No replies in this thread yet. Send a diagnostic update below to communicate with the tenant operator.
                    </div>
                    @endforelse
                </div>

                {{-- Post Reply Form --}}
                <div style="padding: 16px 20px; background: #fbfcfd; border-top: 1px solid #e2e8f0;">
                    <form action="{{ route('super-admin.bugs.message', $ticket->id) }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 10px;">
                            <textarea name="message" rows="3" required placeholder="Type diagnostic reply, investigation progress, or hotfix resolution details..." style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; resize: vertical; box-sizing: border-box; font-family: inherit; transition: border-color 0.2s;" onfocus="this.style.borderColor='#405189';" onblur="this.style.borderColor='#cbd5e1';"></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 11px; color: #94a3b8;">
                                <i class="fa fa-info-circle"></i> Replying automatically sets ticket status to "In Progress".
                            </span>
                            <button type="submit" style="height: 34px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Status Control, Tenant Card & SLA --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- 1. Status & Priority Control Box --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-sliders" style="color: #405189; margin-right: 4px;"></i> Triage Control Panel
                    </span>
                </div>

                <div style="padding: 18px;">
                    <form action="{{ route('super-admin.bugs.update-status', $ticket->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Status Selection --}}
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Ticket Status</label>
                            <select name="status" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>🟡 Open (New)</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>🔵 In Progress (Triage)</option>
                                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>🟢 Resolved (Hotfix Deployed)</option>
                                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>⚪ Closed (Archived)</option>
                            </select>
                        </div>

                        {{-- Priority Selection --}}
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Priority / Severity</label>
                            <select name="priority" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                                <option value="Urgent" {{ $ticket->priority === 'Urgent' ? 'selected' : '' }}>⚡ Urgent (Blocker - SLA 4h)</option>
                                <option value="High" {{ $ticket->priority === 'High' ? 'selected' : '' }}>🔺 High (SLA 12h)</option>
                                <option value="Medium" {{ $ticket->priority === 'Medium' ? 'selected' : '' }}>🔸 Medium (SLA 24h)</option>
                                <option value="Low" {{ $ticket->priority === 'Low' ? 'selected' : '' }}>🔹 Low (SLA 48h)</option>
                            </select>
                        </div>

                        {{-- Assigned Agent --}}
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Assigned Resolver</label>
                            <select name="assigned_agent_id" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                                <option value="">Unassigned</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $ticket->assigned_agent_id == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" style="width: 100%; height: 36px; background: #059669; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 1px 2px rgba(5,150,105,0.2);">
                            <i class="fa fa-save"></i> Save Updates
                        </button>
                    </form>
                </div>
            </div>

            {{-- 2. Tenant Context Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-building-o" style="color: #405189; margin-right: 4px;"></i> Tenant Organization
                    </span>
                </div>

                <div style="padding: 18px; font-size: 12px;">
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Company Name</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                            {{ optional($ticket->company)->name ?? 'Internal System' }}
                        </div>
                        @if(optional($ticket->company)->code)
                        <div style="display: inline-block; font-size: 10px; font-weight: 700; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 4px; margin-top: 4px;">
                            Code: {{ $ticket->company->code }}
                        </div>
                        @endif
                    </div>

                    <div style="margin-bottom: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Reported By</div>
                        <div style="font-weight: 600; color: #1e293b; margin-top: 2px;">
                            {{ optional($ticket->user)->name ?? 'Unknown Operator' }}
                        </div>
                        <div style="color: #64748b; font-size: 11px;">
                            {{ optional($ticket->user)->email ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- Login as Tenant ERP Impersonation button --}}
                    @if($ticket->user_id)
                    <div style="padding-top: 12px; border-top: 1px solid #f1f5f9;">
                        <form method="POST" action="{{ route('super-admin.users.login-as', $ticket->user_id) }}" target="_blank">
                            @csrf
                            <button type="submit" style="width: 100%; height: 34px; background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fa fa-sign-in"></i> Inspect in Tenant ERP
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 3. SLA Compliance Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-clock-o" style="color: #405189; margin-right: 4px;"></i> SLA & Timeline
                    </span>
                </div>

                <div style="padding: 18px; font-size: 12px;">
                    <div style="margin-bottom: 10px;">
                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Resolution Target</div>
                        <div style="font-weight: 600; color: #1e293b; margin-top: 2px;">
                            {{ $ticket->sla_due_at ? \Carbon\Carbon::parse($ticket->sla_due_at)->format('M d, Y h:i A') : 'Standard 24h SLA' }}
                        </div>
                    </div>

                    <div style="margin-bottom: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Time Elapsed</div>
                        <div style="font-weight: 600; color: #1e293b; margin-top: 2px;">
                            {{ $ticket->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Last Status Update</div>
                        <div style="font-weight: 600; color: #1e293b; margin-top: 2px;">
                            {{ $ticket->updated_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
