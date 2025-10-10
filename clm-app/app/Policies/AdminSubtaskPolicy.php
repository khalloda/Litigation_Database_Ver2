<?php

namespace App\Policies;

use App\Models\AdminSubtask;
use App\Models\User;

class AdminSubtaskPolicy
{
    public function viewAny(User $user): bool
    {
        return auth()->check();
    }

    public function view(User $user, AdminSubtask $adminSubtask): bool
    {
        return auth()->check();
    }

    public function create(User $user): bool
    {
        return auth()->check();
    }

    public function update(User $user, AdminSubtask $adminSubtask): bool
    {
        return auth()->check();
    }

    public function delete(User $user, AdminSubtask $adminSubtask): bool
    {
        return auth()->check();
    }

    public function restore(User $user, AdminSubtask $adminSubtask): bool
    {
        return auth()->check();
    }

    public function forceDelete(User $user, AdminSubtask $adminSubtask): bool
    {
        return $user->can('admin.users.manage');
    }
}

