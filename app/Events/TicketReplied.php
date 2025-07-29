<?php

namespace App\Events;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $reply;

    public function __construct(SupportTicket $ticket, SupportTicketReply $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->ticket->user_id),
            new PrivateChannel('admin.support'),
        ];
    }

    public function broadcastAs()
    {
        return 'ticket.replied';
    }

    public function broadcastWith()
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'reply_id' => $this->reply->id,
            'reply_message' => $this->reply->message,
            'is_admin_reply' => $this->reply->is_admin_reply,
            'user_name' => $this->reply->user->name,
            'timestamp' => $this->reply->created_at->toISOString(),
        ];
    }
}
