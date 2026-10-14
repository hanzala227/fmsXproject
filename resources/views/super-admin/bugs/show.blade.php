@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; min-height: calc(100vh - 60px); display: flex; flex-direction: column; gap: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('super-admin.bugs.index') }}" style="color: #64748b; text-decoration: none; font-size: 14px;"><i class="fa fa-arrow-left"></i></a>
            <h2 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;">Manage Ticket #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</h2>
        </div>
        
        <form action="{{ route('super-admin.bugs.update-status', $ticket->id) }}" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
            @csrf
            @method('PATCH')
            <label style="font-size: 12px; font-weight: 600; color: #475569;">Status:</label>
            <select name="status" style="height: 32px; padding: 0 12px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button type="submit" style="height: 32px; padding: 0 12px; background: #0ab39c; color: #fff; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">
                Update
            </button>
        </form>
    </div>

    @if(session('success'))
    <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 4px; font-size: 13px; font-weight: 500;">
        <i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}
    </div>
    @endif

        {{-- Ticket Details Box --}}
        <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #1e293b;">{{ $ticket->title }}</h3>
                    <div style="font-size: 12px; color: #64748b; display: flex; gap: 12px; align-items: center;">
                        <span><i class="fa fa-building-o"></i> {{ optional($ticket->company)->name ?? 'N/A' }}</span>
                        <span><i class="fa fa-user-o"></i> {{ optional($ticket->user)->name }}</span>
                        <span style="background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">{{ $ticket->module_name }}</span>
                    </div>
                </div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
                    Reported on {{ $ticket->created_at->format('d M, Y h:i A') }}
                </div>
            </div>

            <div style="padding: 24px; color: #334155; font-size: 14px; line-height: 1.6;">
                <h4 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Bug Description</h4>
                <div style="background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
            </div>

            @if($ticket->attachments->count() > 0)
            <div style="padding: 0 24px 24px 24px;">
                <h4 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Attachments ({{ $ticket->attachments->count() }})</h4>
                
                <div style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px;">
                    @foreach($ticket->attachments as $attachment)
                    @php
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $attachment->file_name);
                        $url = str_starts_with($attachment->file_path, 'uploads/') 
                            ? asset($attachment->file_path) 
                            : asset('storage/' . $attachment->file_path);
                    @endphp
                    <a href="{{ $url }}" target="_blank" style="flex: 0 0 auto; width: 150px; text-decoration: none; display: flex; flex-direction: column; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        @if($isImage)
                            <div style="height: 100px; width: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <img src="{{ $url }}" alt="{{ $attachment->file_name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div style=\'color:#94a3b8;font-size:24px\'><i class=\'fa fa-image\'></i></div>'">
                            </div>
                        @else
                            <div style="height: 100px; width: 100%; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8;">
                                <i class="fa fa-file-text-o" style="font-size: 28px; margin-bottom: 8px;"></i>
                                <span style="font-size: 9px; font-weight: 700; text-transform: uppercase;">Document</span>
                            </div>
                        @endif
                        <div style="padding: 8px; font-size: 11px; color: #475569; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-top: 1px solid #e2e8f0;" title="{{ $attachment->file_name }}">
                            {{ $attachment->file_name }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
