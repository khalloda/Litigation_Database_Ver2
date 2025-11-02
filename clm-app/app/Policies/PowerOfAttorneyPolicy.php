<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PowerOfAttorney;

class PowerOfAttorneyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view') || $user->can('cases.view');
    }

    public function view(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('clients.view') || $user->can('cases.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create') || $user->can('cases.create');
    }

    public function update(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('clients.edit') || $user->can('cases.edit');
    }

    public function delete(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('clients.delete') || $user->can('cases.delete');
    }

    public function restore(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('clients.delete') || $user->can('cases.delete');
    }

    public function forceDelete(User $user, PowerOfAttorney $powerOfAttorney): bool
    {
        return $user->can('admin.users.manage');
    }
}
