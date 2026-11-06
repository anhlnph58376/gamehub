<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Game $game): bool
    {
        if ($game->status === 'published') {
            return true;
        }

        return $user !== null && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Game $game): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Game $game): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Game $game): bool
    {
        return $user->isAdmin();
    }
}

