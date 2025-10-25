<?php

namespace App\Policies;

use App\Models\User;

class CasePolicy
{
    /**
     * Determine whether the user can view any cases.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('cases.view');
    }

    /**
     * Determine whether the user can view the case.
     */
    public function view(User $user, $case): bool
    {
        return $user->can('cases.view');
    }

    /**
     * Determine whether the user can create cases.
     */
    public function create(User $user): bool
    {
        return $user->can('cases.create');
    }

    /**
     * Determine whether the user can update the case.
     */
    public function update(User $user, $case): bool
    {
        return $user->can('cases.edit');
    }

    /**
     * Determine whether the user can delete the case.
     */
    public function delete(User $user, $case): bool
    {
        return $user->can('cases.delete');
    }

    /**
     * Determine whether the user can restore the case.
     */
    public function restore(User $user, $case): bool
    {
        return $user->can('cases.edit');
    }

    /**
     * Determine whether the user can permanently delete the case.
     */
    public function forceDelete(User $user, $case): bool
    {
        return $user->can('cases.delete');
    }
}
