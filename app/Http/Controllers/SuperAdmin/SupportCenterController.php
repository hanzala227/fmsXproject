<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SupportCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['company', 'user', 'assignedAgent']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        $agents = User::whereIn('role', ['SuperAdmin', 'Support'])->get();

        return view('super-admin.support-center.index', compact('tickets', 'agents'));
    }

    public function create()
    {
        $agents = User::whereIn('role', ['SuperAdmin', 'Support'])->get();
        return view('super-admin.support-center.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'module_name' => 'required|string'
        ]);

        $ticket = Ticket::create([
            'company_id' => null, // SuperAdmin level
            'user_id' => auth()->id(),
            'module_name' => $request->module_name,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'Open',
            'assigned_agent_id' => $request->assigned_agent_id,
            'sla_due_at' => $this->calculateSLA($request->priority)
        ]);

        return redirect()->route('super-admin.support-center.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['company', 'user', 'assignedAgent', 'messages.user', 'attachments']);
        $agents = User::whereIn('role', ['SuperAdmin', 'Support'])->get();
        
        return view('super-admin.support-center.show', compact('ticket', 'agents'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $ticket->update([
            'status' => $request->status,
            'priority' => $request->priority,
            'assigned_agent_id' => $request->assigned_agent_id,
        ]);

        return back()->with('success', 'Ticket updated successfully.');
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_internal' => $request->has('is_internal')
        ]);

        return back()->with('success', 'Reply added successfully.');
    }

    public function myTickets(Request $request)
    {
        $request->merge(['assigned_agent_id' => auth()->id()]);
        
        $tickets = Ticket::with(['company', 'user'])
            ->where('assigned_agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $agents = User::whereIn('role', ['SuperAdmin', 'Support'])->get();
        return view('super-admin.support-center.index', compact('tickets', 'agents'));
    }

    public function slaManagement()
    {
        $tickets = Ticket::with(['company', 'user', 'assignedAgent'])
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->orderBy('sla_due_at', 'asc')
            ->paginate(20);
            
        return view('super-admin.support-center.sla', compact('tickets'));
    }

    public function knowledgeBase()
    {
        // Placeholder for future knowledge base implementation
        return view('super-admin.support-center.knowledge-base');
    }

    private function calculateSLA($priority)
    {
        return match($priority) {
            'Urgent' => Carbon::now()->addHours(2),
            'High' => Carbon::now()->addHours(8),
            'Medium' => Carbon::now()->addHours(24),
            'Low' => Carbon::now()->addHours(72),
            default => Carbon::now()->addHours(24),
        };
    }
}
