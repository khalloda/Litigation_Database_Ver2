<?php

namespace App\Policies;

use App\Models\User;

class HearingPolicy
{
    /**
     * Determine whether the user can view any hearings.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('hearings.view');
    }

    /**
     * Determine whether the user can view the hearing.
     */
    public function view(User $user, $hearing): bool
    {
        return $user->can('hearings.view');
    }

    /**
     * Determine whether the user can create hearings.
     */
    public function create(User $user): bool
    {
        return $user->can('hearings.create');
    }

    /**
     * Determine whether the user can update the hearing.
     */
    public function update(User $user, $hearing): bool
    {
        return $user->can('hearings.edit');
    }

    /**
     * Determine whether the user can delete the hearing.
     */
    public function delete(User $user, $hearing): bool
    {
        return $user->can('hearings.delete');
    }

    /**
     * Determine whether the user can restore the hearing.
     */
    public function restore(User $user, $hearing): bool
    {
        return $user->can('hearings.edit');
    }

    /**
     * Determine whether the user can permanently delete the hearing.
     */
    public function forceDelete(User $user, $hearing): bool
    {
        return $user->can('hearings.delete');
    }
}
