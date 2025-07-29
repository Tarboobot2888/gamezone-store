<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function view(?User $user, Cart $cart)
    {
        if ($user) {
            return $cart->user_id === $user->id;
        }
        
        return $cart->session_id === session()->getId();
    }

    public function update(?User $user, Cart $cart)
    {
        if ($user) {
            return $cart->user_id === $user->id;
        }
        
        return $cart->session_id === session()->getId();
    }

    public function delete(?User $user, Cart $cart)
    {
        if ($user) {
            return $cart->user_id === $user->id;
        }
        
        return $cart->session_id === session()->getId();
    }
}
