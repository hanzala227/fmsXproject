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
        $currentTab = $request->get('tab', 'assigned');
        $query = Ticket::with(['company', 'user', 'assignedAgent']);

        if ($currentTab === 'unassigned') {
            $query->whereNull('assigned_agent_id')->whereNotIn('status', ['Resolved', 'Closed']);
        } elseif ($currentTab === 'created') {
            $query->where('user_id', auth()->id());
        } else {
            // Default: assigned to me
            $query->where('assigned_agent_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('id', 'like', $term);
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $tabCounts = [
            'assigned' => Ticket::where('assigned_agent_id', auth()->id())->count(),
            'unassigned' => Ticket::whereNull('assigned_agent_id')->whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'created' => Ticket::where('user_id', auth()->id())->count(),
        ];

        $agents = User::whereIn('role', ['SuperAdmin', 'Support'])->get();
        return view('super-admin.support-center.index', compact('tickets', 'agents', 'tabCounts', 'currentTab'));
    }

    public function claimTicket(Ticket $ticket)
    {
        $ticket->assigned_agent_id = auth()->id();
        if ($ticket->status === 'Open') {
            $ticket->status = 'In Progress';
        }
        $ticket->save();

        if (class_exists(\App\Models\SuperAdminAuditLog::class)) {
            \App\Models\SuperAdminAuditLog::log('claimed_ticket', 'ticket', $ticket->id, ['title' => $ticket->title]);
        }

        return back()->with('success', "Ticket #{$ticket->id} has been claimed and assigned to you.");
    }

    public function slaManagement(Request $request)
    {
        $now = Carbon::now();
        $filter = $request->get('filter', 'all');

        $query = Ticket::with(['company', 'user', 'assignedAgent'])
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->whereNotNull('sla_due_at');

        if ($filter === 'overdue') {
            $query->where('sla_due_at', '<', $now);
        } elseif ($filter === 'due_soon') {
            $query->whereBetween('sla_due_at', [$now, $now->copy()->addHours(4)]);
        } elseif ($filter === 'on_track') {
            $query->where('sla_due_at', '>', $now->copy()->addHours(4));
        }

        $tickets = $query->orderBy('sla_due_at', 'asc')->paginate(20)->withQueryString();

        $baseQuery = Ticket::whereNotIn('status', ['Resolved', 'Closed'])->whereNotNull('sla_due_at');
        $kpis = [
            'total' => (clone $baseQuery)->count(),
            'overdue' => (clone $baseQuery)->where('sla_due_at', '<', $now)->count(),
            'due_soon' => (clone $baseQuery)->whereBetween('sla_due_at', [$now, $now->copy()->addHours(4)])->count(),
            'on_track' => (clone $baseQuery)->where('sla_due_at', '>', $now->copy()->addHours(4))->count(),
        ];

        return view('super-admin.support-center.sla', compact('tickets', 'kpis', 'filter'));
    }

    public function knowledgeBase(Request $request)
    {
        $query = \App\Models\KnowledgeBaseArticle::with('author')->published();

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $articles = $query->latest()->paginate(9)->withQueryString();

        $categories = \App\Models\KnowledgeBaseArticle::published()
            ->select('category', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        $kpis = [
            'total_articles' => \App\Models\KnowledgeBaseArticle::published()->count(),
            'total_views' => \App\Models\KnowledgeBaseArticle::published()->sum('views_count'),
            'total_categories' => $categories->count(),
        ];

        return view('super-admin.support-center.knowledge-base', compact('articles', 'categories', 'kpis'));
    }

    public function showArticle(\App\Models\KnowledgeBaseArticle $article)
    {
        $article->increment('views_count');
        $article->load('author');

        $relatedArticles = \App\Models\KnowledgeBaseArticle::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->take(3)
            ->get();

        return view('super-admin.support-center.knowledge-base-article', compact('article', 'relatedArticles'));
    }

    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'target_audience' => 'required|string',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']) . '-' . time();
        $validated['is_published'] = true;

        $article = \App\Models\KnowledgeBaseArticle::create($validated);

        if (class_exists(\App\Models\SuperAdminAuditLog::class)) {
            \App\Models\SuperAdminAuditLog::log('created_kb_article', 'article', $article->id, ['title' => $article->title]);
        }

        return redirect()->route('super-admin.support-center.knowledge-base')
            ->with('success', "Help article '{$article->title}' published successfully.");
    }

    public function deleteArticle(\App\Models\KnowledgeBaseArticle $article)
    {
        $title = $article->title;
        $article->delete();

        if (class_exists(\App\Models\SuperAdminAuditLog::class)) {
            \App\Models\SuperAdminAuditLog::log('deleted_kb_article', 'article', $article->id, ['title' => $title]);
        }

        return redirect()->route('super-admin.support-center.knowledge-base')
            ->with('success', "Article '{$title}' removed.");
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
