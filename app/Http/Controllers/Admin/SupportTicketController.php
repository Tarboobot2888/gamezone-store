<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Events\TicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:super-admin|admin|support']);
    }

    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo', 'replies']);

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                               ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        $supportUsers = User::role(['super-admin', 'admin', 'support'])->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $tickets,
                'support_users' => $supportUsers
            ]);
        }

        return view('admin.support-tickets.index', compact('tickets', 'supportUsers'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'replies.user', 'assignedTo']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        }

        return view('admin.support-tickets.show', compact('ticket'));
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => $request->assigned_to ? SupportTicket::STATUS_IN_PROGRESS : SupportTicket::STATUS_OPEN
        ]);

        $message = $request->assigned_to 
            ? __('Ticket assigned successfully')
            : __('Ticket unassigned successfully');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $ticket->load(['assignedTo'])
            ]);
        }

        return redirect()->route('admin.support-tickets.show', $ticket)
            ->with('success', $message);
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                SupportTicket::STATUS_OPEN,
                SupportTicket::STATUS_IN_PROGRESS,
                SupportTicket::STATUS_RESOLVED,
                SupportTicket::STATUS_CLOSED
            ])
        ]);

        $ticket->update([
            'status' => $request->status,
            'resolved_at' => $request->status === SupportTicket::STATUS_RESOLVED ? now() : null,
            'closed_at' => $request->status === SupportTicket::STATUS_CLOSED ? now() : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Ticket status updated successfully'),
                'data' => $ticket
            ]);
        }

        return redirect()->route('admin.support-tickets.show', $ticket)
            ->with('success', __('Ticket status updated successfully'));
    }

    public function addReply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'boolean'
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin_reply' => true,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Update ticket status if it was closed
        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        // Update last activity
        $ticket->touch();

        // Broadcast event (only if not internal)
        if (!$reply->is_internal) {
            event(new TicketReplied($ticket, $reply));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Reply added successfully'),
                'data' => $reply->load('user')
            ], 201);
        }

        return redirect()->route('admin.support-tickets.show', $ticket)
            ->with('success', __('Reply added successfully'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign,close,resolve,delete',
            'tickets' => 'required|array',
            'tickets.*' => 'exists:support_tickets,id',
            'assigned_to' => 'required_if:action,assign|nullable|exists:users,id'
        ]);

        $tickets = SupportTicket::whereIn('id', $request->tickets);

        switch ($request->action) {
            case 'assign':
                $tickets->update([
                    'assigned_to' => $request->assigned_to,
                    'status' => SupportTicket::STATUS_IN_PROGRESS
                ]);
                $message = __('Tickets assigned successfully');
                break;
            case 'close':
                $tickets->update([
                    'status' => SupportTicket::STATUS_CLOSED,
                    'closed_at' => now()
                ]);
                $message = __('Tickets closed successfully');
                break;
            case 'resolve':
                $tickets->update([
                    'status' => SupportTicket::STATUS_RESOLVED,
                    'resolved_at' => now()
                ]);
                $message = __('Tickets resolved successfully');
                break;
            case 'delete':
                $tickets->delete();
                $message = __('Tickets deleted successfully');
                break;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.support-tickets.index')->with('success', $message);
    }

    public function stats()
    {
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'in_progress' => SupportTicket::where('status', SupportTicket::STATUS_IN_PROGRESS)->count(),
            'resolved' => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed' => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
            'unassigned' => SupportTicket::whereNull('assigned_to')->count(),
            'high_priority' => SupportTicket::where('priority', SupportTicket::PRIORITY_HIGH)->count(),
            'urgent' => SupportTicket::where('priority', SupportTicket::PRIORITY_URGENT)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
