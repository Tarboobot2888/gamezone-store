<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, SupportTicket $ticket)
    {
        return $user->id === $ticket->user_id || $user->hasRole(['super-admin', 'admin', 'support']);
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, SupportTicket $ticket)
    {
        if ($user->hasRole(['super-admin', 'admin', 'support'])) {
            return true;
        }
        
        return $user->id === $ticket->user_id && $ticket->status !== 'closed';
    }

    public function delete(User $user, SupportTicket $ticket)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function close(User $user, SupportTicket $ticket)
    {
        return $user->id === $ticket->user_id || $user->hasRole(['super-admin', 'admin', 'support']);
    }
}
