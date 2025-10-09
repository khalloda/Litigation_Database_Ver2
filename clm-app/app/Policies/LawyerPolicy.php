<?php

namespace App\Policies;

use App\Models\User;

class LawyerPolicy
{
    /**
     * Determine whether the user can view any lawyers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can view the lawyer.
     */
    public function view(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can create lawyers.
     */
    public function create(User $user): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can update the lawyer.
     */
    public function update(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can delete the lawyer.
     */
    public function delete(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can restore the lawyer.
     */
    public function restore(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    /**
     * Determine whether the user can permanently delete the lawyer.
     */
    public function forceDelete(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }
}

