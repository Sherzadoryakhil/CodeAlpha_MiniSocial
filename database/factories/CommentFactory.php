<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(fake()->numberBetween(4, 14)),
            'created_at' => $created = fake()->dateTimeBetween('-29 days', 'now'),
            'updated_at' => $created,
        ];
    }
}
