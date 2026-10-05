<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::where('company_id', auth()->user()->company_id);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('support.tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'module_name' => 'required|string',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10240', // 10MB limit
        ]);

        $ticket = Ticket::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'module_name' => $validated['module_name'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'Open',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads/tickets/' . $ticket->id);
                $file->move($destinationPath, $filename);
                
                $path = 'uploads/tickets/' . $ticket->id . '/' . $filename;

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('support.tickets.index')->with('success', 'Bug report submitted successfully.');
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        
        $ticket->load(['messages.user', 'attachments']);
        return view('support.tickets.show', compact('ticket'));
    }

    public function message(Request $request, Ticket $ticket)
    {
        if ($ticket->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate(['message' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return back()->with('success', 'Message sent successfully.');
    }
}
