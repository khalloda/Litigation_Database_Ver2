<?php

namespace App\Policies;

use App\Models\ImportSession;
use App\Models\User;

class ImportSessionPolicy
{
    /**
     * Determine whether the user can view any import sessions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view the import session.
     */
    public function view(User $user, ImportSession $importSession): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']) || $importSession->user_id === $user->id;
    }

    /**
     * Determine whether the user can create import sessions.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can update the import session.
     */
    public function update(User $user, ImportSession $importSession): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']) && $importSession->isInProgress();
    }

    /**
     * Determine whether the user can delete the import session.
     */
    public function delete(User $user, ImportSession $importSession): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the import session.
     */
    public function restore(User $user, ImportSession $importSession): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the import session.
     */
    public function forceDelete(User $user, ImportSession $importSession): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can cancel an in-progress import.
     */
    public function cancel(User $user, ImportSession $importSession): bool
    {
        return ($user->hasAnyRole(['super_admin', 'admin']) || $importSession->user_id === $user->id)
            && $importSession->isInProgress();
    }
}

