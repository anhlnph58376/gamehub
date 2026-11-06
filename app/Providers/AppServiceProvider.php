<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\GameReview;
use App\Models\User;
use App\Policies\GamePolicy;
use App\Policies\GameReviewPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Game::class, GamePolicy::class);
        Gate::policy(GameReview::class, GameReviewPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
