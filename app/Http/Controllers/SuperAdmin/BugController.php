<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Company;
use App\Models\User;

class BugController extends Controller
{
    /**
     * Display a listing of freight bugs & support issues.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['company', 'user', 'assignedAgent', 'attachments'])
            ->withCount('messages');

        // Tab Filtering
        $currentTab = $request->get('tab', 'all');
        if ($currentTab === 'open') {
            $query->where('status', 'Open');
        } elseif ($currentTab === 'in_progress') {
            $query->where('status', 'In Progress');
        } elseif ($currentTab === 'urgent') {
            $query->whereIn('priority', ['Urgent', 'High'])
                  ->whereNotIn('status', ['Resolved', 'Closed']);
        } elseif ($currentTab === 'resolved') {
            $query->whereIn('status', ['Resolved', 'Closed']);
        }

        // Direct Status Filter (if dropdown explicitly used)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Module Filter
        if ($request->filled('module_name')) {
            $query->where('module_name', $request->module_name);
        }

        // Tenant Company Filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Search Filter (ID, title, description, category, module, tenant, user)
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term, $request) {
                $rawSearch = trim($request->search);
                $cleanId = ltrim(preg_replace('/[^0-9]/', '', $rawSearch), '0');
                if ($cleanId !== '') {
                    $q->orWhere('id', $cleanId);
                }
                $q->orWhere('title', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('category', 'like', $term)
                  ->orWhere('module_name', 'like', $term)
                  ->orWhereHas('company', function ($cq) use ($term) {
                      $cq->where('name', 'like', $term)
                         ->orWhere('code', 'like', $term);
                  })
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', $term)
                         ->orWhere('email', 'like', $term);
                  });
            });
        }

        // Dynamic Tab Counts
        $counts = [
            'all'         => Ticket::count(),
            'open'        => Ticket::where('status', 'Open')->count(),
            'in_progress' => Ticket::where('status', 'In Progress')->count(),
            'urgent'      => Ticket::whereIn('priority', ['Urgent', 'High'])->whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'resolved'    => Ticket::whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

        // KPI Summary
        $stats = [
            'total'          => $counts['all'],
            'active'         => $counts['open'] + $counts['in_progress'],
            'urgent_sla'     => $counts['urgent'],
            'resolved_rate'  => $counts['all'] > 0 ? round(($counts['resolved'] / $counts['all']) * 100) : 100,
        ];

        $tickets = $query->orderByRaw("FIELD(status, 'Open', 'In Progress', 'Resolved', 'Closed')")
            ->orderByRaw("FIELD(priority, 'Urgent', 'High', 'Medium', 'Low')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Freight standard module directory
        $standardModules = [
            'Ocean Export',
            'Ocean Import',
            'Air Export',
            'Air Import',
            'Truck Shipment',
            'Customs Clearance',
            'Warehouse & Distribution',
            'Freight Accounting',
            'CRM & Sales',
            'Master Data',
            'Reports & Analytics',
            'Settings & Defaults',
        ];

        $dbModules = Ticket::whereNotNull('module_name')->distinct()->pluck('module_name')->toArray();
        $modules = array_values(array_unique(array_merge($standardModules, $dbModules)));

        $companies = Company::select('id', 'name', 'code')->orderBy('name')->get();

        return view('super-admin.bugs.index', compact(
            'tickets',
            'counts',
            'stats',
            'modules',
            'companies',
            'currentTab'
        ));
    }

    /**
     * Show ticket details and resolution console.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['company', 'user', 'assignedAgent', 'messages.user', 'attachments']);
        
        // Potential agents that can be assigned (SuperAdmins & Admins)
        $agents = User::whereNull('company_id')
            ->orWhereHas('roles', function ($q) {
                $q->whereIn('name', ['SuperAdmin', 'Admin', 'Operation']);
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('super-admin.bugs.show', compact('ticket', 'agents'));
    }

    /**
     * Update ticket status, priority, or assigned agent.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status'            => 'required|in:Open,In Progress,Resolved,Closed',
            'priority'          => 'nullable|in:Low,Medium,High,Urgent',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($request->filled('priority')) {
            $updateData['priority'] = $validated['priority'];
        }

        if ($request->has('assigned_agent_id')) {
            $updateData['assigned_agent_id'] = $validated['assigned_agent_id'];
        }

        $ticket->update($updateData);

        return back()->with('success', 'Ticket #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' updated successfully.');
    }

    /**
     * Post a resolution or diagnostic message in the ticket thread.
     */
    public function message(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'message'   => $request->message,
        ]);

        // Automatically move ticket to "In Progress" if currently "Open"
        if ($ticket->status === 'Open') {
            $ticket->update(['status' => 'In Progress']);
        }

        return back()->with('success', 'Diagnostic message posted to ticket thread.');
    }

    /**
     * Export tickets to CSV for audit and compliance.
     */
    public function exportCsv(Request $request)
    {
        $query = Ticket::with(['company', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('module_name')) {
            $query->where('module_name', $request->module_name);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $filename = 'freight_issues_export_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'Ticket ID',
                'Priority',
                'Status',
                'Freight Module',
                'Category',
                'Subject / Title',
                'Tenant Company',
                'Tenant Code',
                'Reported By',
                'Reporter Email',
                'SLA Due Date',
                'Created At',
            ]);

            foreach ($tickets as $t) {
                fputcsv($file, [
                    '#TKT-' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                    $t->priority,
                    $t->status,
                    $t->module_name,
                    $t->category ?? 'General Issue',
                    $t->title,
                    optional($t->company)->name ?? 'System',
                    optional($t->company)->code ?? '—',
                    optional($t->user)->name ?? '—',
                    optional($t->user)->email ?? '—',
                    $t->sla_due_at ? date('Y-m-d H:i', strtotime($t->sla_due_at)) : 'N/A',
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
