<x-layout>
<div style="padding: 16px; min-height: calc(100vh - 60px); background: #f8fafc;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;"><i class="fa fa-life-ring" style="color: #405189; margin-right: 8px;"></i> Support Center</h2>
            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">Report bugs, request features, and communicate with the Super Admin.</p>
        </div>
        <button onclick="document.getElementById('reportBugModal').style.display = 'flex'" style="background: #0ab39c; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">
            <i class="fa fa-bug"></i> Report a Bug
        </button>
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
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Subject</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Module</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Status</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Reported On</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr style="border-bottom: 1px solid #e2e8f0; hover:background-color: #f8fafc;">
                    <td style="padding: 12px 16px; font-size: 12px; color: #405189; font-weight: 600;">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td style="padding: 12px 16px; font-size: 13px; color: #1e293b; font-weight: 500;">{{ $ticket->title }}</td>
                    <td style="padding: 12px 16px; font-size: 12px; color: #64748b;">{{ $ticket->module_name }}</td>
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
                        <a href="{{ route('support.tickets.show', $ticket->id) }}" style="color: #3b82f6; text-decoration: none; font-size: 12px; font-weight: 600;">View <i class="fa fa-arrow-right"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8; font-size: 13px;">
                        <i class="fa fa-inbox" style="font-size: 32px; opacity: 0.5; margin-bottom: 12px; display: block;"></i>
                        No support tickets found.
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

{{-- Report Bug Modal --}}
<div id="reportBugModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 6px; width: 600px; max-width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1e293b;">Report a Bug</h3>
            <button onclick="document.getElementById('reportBugModal').style.display = 'none'" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        
        <form action="{{ route('support.tickets.store') }}" method="POST" enctype="multipart/form-data" style="padding: 20px;">
            @csrf
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Subject / Title <span style="color: red;">*</span></label>
                <input type="text" name="title" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Module <span style="color: red;">*</span></label>
                <select name="module_name" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; background: #fff;">
                    <option value="">Select a Module</option>
                    @php
                        // Fetch the current user's permitted modules dynamically!
                        $userModules = auth()->user()->permissions->pluck('module_name')
                            ->map(function($m) { return array_map('trim', explode(',', $m)); })
                            ->flatten()->unique()->filter()->sort()->toArray();
                    @endphp
                    @foreach($userModules as $mod)
                        <option value="{{ $mod }}">{{ $mod }}</option>
                    @endforeach
                    <option value="General">General / System</option>
                </select>
                <p style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Only modules you have permission to access are listed here.</p>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Description <span style="color: red;">*</span></label>
                <textarea name="description" rows="5" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; resize: vertical;" placeholder="Please describe the issue in detail. Steps to reproduce are highly appreciated!"></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Attachments (Multiple allowed)</label>
                <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx" style="font-size: 12px; color: #64748b;">
                <p style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Max 10MB per file. (JPEG, PNG, PDF, Word)</p>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('reportBugModal').style.display = 'none'" style="padding: 8px 16px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background: #0ab39c; color: #fff; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">Submit Bug</button>
            </div>
        </form>
    </div>
</div>
</x-layout>
