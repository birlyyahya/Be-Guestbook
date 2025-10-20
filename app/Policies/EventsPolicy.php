<?php

namespace App\Policies;

use App\Models\Events;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventsPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'user' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Events $events): bool
    {
        return $user->id === $events->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Events $events): bool
    {
        return $user->id === $events->created_by;
    }
}
