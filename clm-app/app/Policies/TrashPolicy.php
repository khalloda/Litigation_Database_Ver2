<?php

namespace App\Policies;

use App\Models\DeletionBundle;
use App\Models\User;

class TrashPolicy
{
    /**
     * Determine whether the user can view any bundles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('trash.view');
    }

    /**
     * Determine whether the user can view the bundle.
     */
    public function view(User $user, DeletionBundle $bundle): bool
    {
        return $user->can('trash.view');
    }

    /**
     * Determine whether the user can restore bundles.
     */
    public function restore(User $user, DeletionBundle $bundle): bool
    {
        return $user->can('trash.restore');
    }

    /**
     * Determine whether the user can purge bundles.
     */
    public function purge(User $user, DeletionBundle $bundle): bool
    {
        return $user->can('trash.purge');
    }
}
