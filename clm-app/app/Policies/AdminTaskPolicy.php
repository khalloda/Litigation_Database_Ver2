<?php

namespace App\Policies;

use App\Models\AdminTask;
use App\Models\User;

class AdminTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, AdminTask $adminTask): bool
    {
        return $user->can('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, AdminTask $adminTask): bool
    {
        return $user->can('tasks.edit');
    }

    public function delete(User $user, AdminTask $adminTask): bool
    {
        return $user->can('tasks.delete');
    }

    public function restore(User $user, AdminTask $adminTask): bool
    {
        return $user->can('tasks.delete');
    }

    public function forceDelete(User $user, AdminTask $adminTask): bool
    {
        return $user->can('tasks.delete');
    }
}

