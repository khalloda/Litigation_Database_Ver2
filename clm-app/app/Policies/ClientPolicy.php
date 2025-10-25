<?php

namespace App\Policies;

use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any clients.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    /**
     * Determine whether the user can view the client.
     */
    public function view(User $user, $client): bool
    {
        return $user->can('clients.view');
    }

    /**
     * Determine whether the user can create clients.
     */
    public function create(User $user): bool
    {
        return $user->can('clients.create');
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, $client): bool
    {
        return $user->can('clients.edit');
    }

    /**
     * Determine whether the user can edit the client.
     */
    public function edit(User $user, $client): bool
    {
        return $user->can('clients.edit');
    }

    /**
     * Determine whether the user can delete the client.
     */
    public function delete(User $user, $client): bool
    {
        return $user->can('clients.delete');
    }

    /**
     * Determine whether the user can restore the client.
     */
    public function restore(User $user, $client): bool
    {
        return $user->can('clients.edit');
    }

    /**
     * Determine whether the user can permanently delete the client.
     */
    public function forceDelete(User $user, $client): bool
    {
        return $user->can('clients.delete');
    }
}
