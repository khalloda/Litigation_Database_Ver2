<?php

namespace App\Policies;

use App\Models\AdminSubtask;
use App\Models\User;

class AdminSubtaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('tasks.edit');
    }

    public function delete(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('tasks.delete');
    }

    public function restore(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('tasks.delete');
    }

    public function forceDelete(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('tasks.delete');
    }
}

