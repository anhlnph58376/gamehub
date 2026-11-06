<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = 'Danh mục ' . fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'icon' => 'Gamepad2',
            'description' => 'Tuyển tập các trò chơi thuộc ' . $name . ' hấp dẫn nhất trên GameHub.',
            'position' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
