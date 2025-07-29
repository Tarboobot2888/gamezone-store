<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Order $order)
    {
        return $user->id === $order->user_id || $user->hasRole(['super-admin', 'admin', 'support']);
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Order $order)
    {
        if ($user->hasRole(['super-admin', 'admin', 'support'])) {
            return true;
        }
        
        return $user->id === $order->user_id && $order->status === 'pending';
    }

    public function delete(User $user, Order $order)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function updateStatus(User $user, Order $order)
    {
        return $user->hasRole(['super-admin', 'admin', 'support']);
    }
}
