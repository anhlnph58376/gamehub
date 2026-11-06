<?php

namespace App\Policies;

use App\Models\GameReview;
use App\Models\User;

class GameReviewPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, GameReview $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }

    public function delete(User $user, GameReview $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }
}

