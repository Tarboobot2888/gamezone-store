<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Service $service)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole(['super-admin', 'admin', 'editor']);
    }

    public function update(User $user, Service $service)
    {
        return $user->hasRole(['super-admin', 'admin', 'editor']);
    }

    public function delete(User $user, Service $service)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function restore(User $user, Service $service)
    {
        return $user->hasRole(['super-admin', 'admin']);
    }

    public function forceDelete(User $user, Service $service)
    {
        return $user->hasRole(['super-admin']);
    }
}
