<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Game $game)
    {
        return $game->is_active || $user->hasRole(['super-admin', 'admin', 'editor']);
    }

    public function create(User $user)
    {
        return $user->hasRole(['super-admin', 'admin', 'editor']);
    }

    public function update(User $user, Game $game)
    {
        return $user->hasRole(['super-admin', 'admin', 'editor']);
    }

    public function delete(User $user, Game $game)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function restore(User $user, Game $game)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function forceDelete(User $user, Game $game)
    {
        return $user->hasRole(['super-admin']);
    }
}
