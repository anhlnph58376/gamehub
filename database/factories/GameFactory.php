<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $title = 'Siêu Phẩm ' . ucfirst(fake()->unique()->words(2, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'short_description' => 'Trò chơi giải trí tốc độ cao ngay trên trình duyệt của bạn.',
            'description' => 'Khám phá thế giới sôi động trong ' . $title . ' với đồ họa sắc nét và lối chơi cuốn hút.',
            'instructions' => 'Sử dụng bàn phím hoặc chạm vào màn hình cảm ứng để điều khiển nhân vật và ghi điểm cao nhất.',
            'controls' => [
                'keyboard' => 'Phím mũi tên / WASD / Space',
                'mouse' => 'Click chuột trái để tương tác',
                'touch' => 'Chạm trực tiếp lên màn hình điện thoại',
            ],
            'author' => 'GameHub Studio',
            'thumbnail_path' => null,
            'category_id' => Category::factory(),
            'status' => 'published',
            'orientation' => 'landscape',
            'mobile_support' => true,
            'play_count' => fake()->numberBetween(10, 5000),
            'favorite_count' => fake()->numberBetween(0, 300),
            'review_count' => 0,
            'avg_rating' => 4.5,
            'is_featured' => false,
            'is_hot' => false,
            'is_new' => true,
            'published_at' => now(),
        ];
    }
}
