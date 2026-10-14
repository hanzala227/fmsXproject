<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;

class BugController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('company', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('module_name')) {
            $query->where('module_name', $request->module_name);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('super-admin.bugs.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['company', 'user', 'messages.user', 'attachments']);
        return view('super-admin.bugs.show', compact('ticket'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('success', 'Ticket status updated to ' . $validated['status']);
    }

    public function message(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return back()->with('success', 'Message sent successfully.');
    }
}
