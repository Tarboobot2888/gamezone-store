<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Http\Requests\SupportTicketRequest;
use App\Http\Requests\SupportTicketReplyRequest;
use App\Events\SupportTicketCreated;
use App\Events\SupportTicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'replies'])
                             ->where('user_id', Auth::id());

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        $tickets = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ]
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['user', 'replies.user']);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    public function store(SupportTicketRequest $request)
    {
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'ticket_number' => 'TKT-' . strtoupper(uniqid()),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
            'category' => $request->category,
        ]);

        event(new SupportTicketCreated($ticket));

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket->load('user')
        ], 201);
    }

    public function update(SupportTicketRequest $request, SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Support ticket updated successfully',
            'data' => $ticket->load('user')
        ]);
    }

    public function addReply(SupportTicketReplyRequest $request, SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $reply = $ticket->replies()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin_reply' => Auth::user()->hasRole(['super-admin', 'admin', 'support']),
        ]);

        // Update ticket status
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        // Update last activity
        $ticket->touch();

        event(new SupportTicketReplied($ticket, $reply));

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'data' => $reply->load('user')
        ], 201);
    }

    public function close(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket closed successfully'
        ]);
    }

    public function destroy(SupportTicket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Support ticket deleted successfully'
        ]);
    }
}
