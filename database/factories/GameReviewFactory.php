<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameReview>
 */
class GameReviewFactory extends Factory
{
    protected $model = GameReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'rating' => fake()->numberBetween(4, 5),
            'comment' => 'Game chạy rất mượt, đồ họa đẹp và âm thanh sống động!',
            'status' => 'approved',
        ];
    }
}
