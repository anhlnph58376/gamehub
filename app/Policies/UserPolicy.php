<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function manage(User $user, User $target): bool
    {
        if ($target->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin() && $user->id !== $target->id;
    }
}

