<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contact;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('clients.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('clients.edit');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('clients.delete');
    }

    public function restore(User $user, Contact $contact): bool
    {
        return $user->can('clients.delete');
    }

    public function forceDelete(User $user, Contact $contact): bool
    {
        return $user->can('admin.users.manage');
    }
}
