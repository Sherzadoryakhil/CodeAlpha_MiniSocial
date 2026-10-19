<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->sentences(fake()->numberBetween(1, 3), true),
            'image_path' => null,
            'created_at' => $created = fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => $created,
        ];
    }
}
