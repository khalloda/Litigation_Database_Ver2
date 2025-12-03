<?php

namespace App\Policies;

use App\Models\Opponent;
use App\Models\User;

class OpponentPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->can('opponents.view') ?? false;
    }

    public function view(?User $user, Opponent $opponent): bool
    {
        return $user?->can('opponents.view') ?? false;
    }

    public function create(?User $user): bool
    {
        return $user?->can('opponents.create') ?? false;
    }

    public function update(?User $user, Opponent $opponent): bool
    {
        return $user?->can('opponents.edit') ?? false;
    }

    public function delete(?User $user, Opponent $opponent): bool
    {
        return $user?->can('opponents.delete') ?? false;
    }

    public function restore(?User $user, Opponent $opponent): bool
    {
        return $user?->can('opponents.delete') ?? false;
    }

    public function forceDelete(?User $user, Opponent $opponent): bool
    {
        return $user?->can('opponents.delete') ?? false;
    }
}

