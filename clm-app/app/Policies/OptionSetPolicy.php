<?php

namespace App\Policies;

use App\Models\OptionSet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OptionSetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OptionSet $optionSet): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OptionSet $optionSet): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OptionSet $optionSet): bool
    {
        // Check if any clients are using this option set
        $usageCount = $optionSet->getUsageCount();
        return $user->hasRole(['super_admin', 'admin']) && $usageCount === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OptionSet $optionSet): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OptionSet $optionSet): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}