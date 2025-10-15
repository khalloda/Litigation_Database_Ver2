<?php

namespace App\Policies;

use App\Models\Opponent;
use App\Models\User;

class OpponentPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    public function view(?User $user, Opponent $opponent): bool
    {
        return (bool) $user;
    }

    public function create(?User $user): bool
    {
        return (bool) $user;
    }

    public function update(?User $user, Opponent $opponent): bool
    {
        return (bool) $user;
    }

    public function delete(?User $user, Opponent $opponent): bool
    {
        return (bool) $user;
    }

    public function restore(?User $user, Opponent $opponent): bool
    {
        return (bool) $user;
    }

    public function forceDelete(?User $user, Opponent $opponent): bool
    {
        return false;
    }
}
