<?php

namespace App\Policies;

use App\Models\OptionValue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OptionValuePolicy
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
    public function view(User $user, OptionValue $optionValue): bool
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
    public function update(User $user, OptionValue $optionValue): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OptionValue $optionValue): bool
    {
        // Check if any clients are using this option value
        $usageCount = $optionValue->getUsageCount();
        return $user->hasRole(['super_admin', 'admin']) && $usageCount === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OptionValue $optionValue): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OptionValue $optionValue): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}