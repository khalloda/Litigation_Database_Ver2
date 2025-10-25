<?php

namespace App\Policies;

use App\Models\AdminTask;
use App\Models\User;

class AdminTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return auth()->check();
    }

    public function view(User $user, AdminTask $adminTask): bool
    {
        return auth()->check();
    }

    public function create(User $user): bool
    {
        return auth()->check();
    }

    public function update(User $user, AdminTask $adminTask): bool
    {
        return auth()->check();
    }

    public function delete(User $user, AdminTask $adminTask): bool
    {
        return auth()->check();
    }

    public function restore(User $user, AdminTask $adminTask): bool
    {
        return auth()->check();
    }

    public function forceDelete(User $user, AdminTask $adminTask): bool
    {
        return $user->can('admin.users.manage');
    }
}

