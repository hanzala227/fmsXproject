@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-bug" style="color: #405189; margin-right: 6px;"></i> Bugs & Issues
        </div>
        
        <form method="GET" action="{{ route('super-admin.bugs.index') }}" style="display: flex; gap: 12px; margin: 0;">
            <select name="status" onchange="this.form.submit()" style="height: 32px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                <option value="">All Statuses</option>
                <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <select name="module_name" onchange="this.form.submit()" style="height: 32px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                <option value="">All Modules</option>
                @php
                    $modules = [
                        'Ocean Import', 'Ocean Export', 'Air Import', 'Air Export',
                        'Truck Shipment', 'Warehouse & Distribution', 'Accounting',
                        'CRM', 'Master Data', 'Settings', 'General'
                    ];
                @endphp
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module_name') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                @endforeach
            </select>
            @if(request('status') || request('module_name'))
                <a href="{{ route('super-admin.bugs.index') }}" style="display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc; color: #64748b; font-size: 12px; text-decoration: none;">Clear</a>
            @endif
        </form>
    </div>

    @if(session('success'))
    <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; font-weight: 500;">
        <i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}
    </div>
    @endif

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Ticket ID</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Tenant / User</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Module</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Subject</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Status</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Reported On</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr style="border-bottom: 1px solid #e2e8f0; hover:background-color: #f8fafc;">
                    <td style="padding: 12px 16px; font-size: 12px; color: #405189; font-weight: 600;">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td style="padding: 12px 16px;">
                        <div style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ optional($ticket->company)->name ?? 'System' }}</div>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ optional($ticket->user)->name }}</div>
                    </td>
                    <td style="padding: 12px 16px; font-size: 12px; color: #64748b;">{{ $ticket->module_name }}</td>
                    <td style="padding: 12px 16px; font-size: 13px; color: #1e293b; font-weight: 500;">{{ $ticket->title }}</td>
                    <td style="padding: 12px 16px;">
                        @php
                            $colors = [
                                'Open' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                'In Progress' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                'Resolved' => ['bg' => '#dcfce3', 'text' => '#166534'],
                                'Closed' => ['bg' => '#f1f5f9', 'text' => '#475569'],
                            ];
                            $color = $colors[$ticket->status] ?? $colors['Open'];
                        @endphp
                        <span style="background: {{ $color['bg'] }}; color: {{ $color['text'] }}; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600;">
                            {{ $ticket->status }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px; font-size: 12px; color: #64748b;">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                    <td style="padding: 12px 16px;">
                        <a href="{{ route('super-admin.bugs.show', $ticket->id) }}" style="color: #3b82f6; text-decoration: none; font-size: 12px; font-weight: 600;">Manage <i class="fa fa-arrow-right"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8; font-size: 13px;">
                        <i class="fa fa-check-circle-o" style="font-size: 32px; opacity: 0.5; margin-bottom: 12px; display: block; color: #10b981;"></i>
                        No bugs or issues found. Everything looks great!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0;">
            {{ $tickets->links('pagination::simple-tailwind') }}
        </div>
    </div>
</div>
@endsection
