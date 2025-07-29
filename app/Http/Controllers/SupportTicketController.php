<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Http\Requests\SupportTicketRequest;
use App\Http\Requests\SupportTicketReplyRequest;
use App\Events\TicketReplied;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'replies'])
                              ->where('user_id', auth()->id());

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Priority filter
        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('ticket_number', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $tickets = $query->paginate($request->get('per_page', 10));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        }

        return view('dashboard.support-tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new support ticket
     */
    public function create()
    {
        return view('dashboard.support-tickets.create');
    }

    /**
     * Store a newly created support ticket
     */
    public function store(SupportTicketRequest $request)
    {
        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'ticket_number' => 'TKT-' . strtoupper(uniqid()),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'category' => $request->category ?? 'general',
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-tickets/' . $ticket->id, 'public');
                $ticket->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            $ticket->load(['user', 'attachments']);
            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully',
                'data' => $ticket
            ], 201);
        }

        return redirect()->route('dashboard.support-tickets.show', $ticket)
                        ->with('success', 'Support ticket created successfully');
    }

    /**
     * Display the specified support ticket
     */
    public function show(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }
            abort(403);
        }

        $ticket->load(['user', 'replies.user', 'attachments']);

        // Mark as read
        if (!$ticket->is_read) {
            $ticket->update(['is_read' => true]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        }

        return view('dashboard.support-tickets.show', compact('ticket'));
    }

    /**
     * Add a reply to the support ticket
     */
    public function addReply(SupportTicketReplyRequest $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }
            abort(403);
        }

        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reply to closed ticket'
                ], 400);
            }
            return back()->withErrors(['message' => 'Cannot reply to closed ticket']);
        }

        $reply = $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_staff_reply' => false,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-tickets/' . $ticket->id . '/replies', 'public');
                $reply->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Update ticket status and timestamps
        $ticket->update([
            'status' => SupportTicket::STATUS_AWAITING_STAFF,
            'last_reply_at' => now(),
            'is_read' => false,
        ]);

        $reply->load(['user', 'attachments']);

        // Fire event for real-time notifications
        event(new TicketReplied($ticket, $reply));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully',
                'data' => $reply
            ], 201);
        }

        return back()->with('success', 'Reply added successfully');
    }

    /**
     * Close the support ticket
     */
    public function close(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }
            abort(403);
        }

        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is already closed'
                ], 400);
            }
            return back()->withErrors(['message' => 'Ticket is already closed']);
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket closed successfully',
                'data' => $ticket
            ]);
        }

        return back()->with('success', 'Ticket closed successfully');
    }

    /**
     * Get ticket statistics for user
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'total' => SupportTicket::where('user_id', $userId)->count(),
            'open' => SupportTicket::where('user_id', $userId)
                                  ->where('status', SupportTicket::STATUS_OPEN)
                                  ->count(),
            'awaiting_staff' => SupportTicket::where('user_id', $userId)
                                            ->where('status', SupportTicket::STATUS_AWAITING_STAFF)
                                            ->count(),
            'awaiting_user' => SupportTicket::where('user_id', $userId)
                                           ->where('status', SupportTicket::STATUS_AWAITING_USER)
                                           ->count(),
            'closed' => SupportTicket::where('user_id', $userId)
                                    ->where('status', SupportTicket::STATUS_CLOSED)
                                    ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
