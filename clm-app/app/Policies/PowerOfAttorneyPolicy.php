<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PowerOfAttorney;

class PowerOfAttorneyPolicy
{
    public function viewAny(User $user): bool
    {
        // Access to POA index is controlled exclusively by POA permissions
        return $user->can('power_of_attorneys.view');
    }

    public function view(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('power_of_attorneys.view');
    }

    public function create(User $user): bool
    {
        return $user->can('power_of_attorneys.create');
    }

    public function update(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('power_of_attorneys.edit');
    }

    public function delete(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('power_of_attorneys.delete');
    }

    public function restore(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('power_of_attorneys.delete');
    }

    public function forceDelete(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('admin.users.manage');
    }
}
