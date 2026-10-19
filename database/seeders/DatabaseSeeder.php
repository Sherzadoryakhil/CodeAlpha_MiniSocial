<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo account for reviewers (local demo data only; password is "password").
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'username' => 'demo',
            'email' => 'demo@connectly.test',
            'bio' => 'Welcome to Connectly. This is a demo account.',
        ]);

        $users = User::factory(9)->create()->push($demo);

        // Three posts per user.
        foreach ($users as $user) {
            Post::factory(3)->create(['user_id' => $user->id]);
        }

        $posts = Post::all();

        foreach ($posts as $post) {
            // 0-4 comments from random users.
            foreach ($users->random(fake()->numberBetween(0, 4)) as $commenter) {
                Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                ]);
            }

            // 0-6 likes from distinct users, so the unique constraint is never hit.
            $post->likedBy()->attach($users->random(fake()->numberBetween(0, 6))->pluck('id'));
        }

        // Each user follows 2-5 other users, never themselves.
        foreach ($users as $user) {
            $others = $users->where('id', '!=', $user->id);
            $user->following()->attach($others->random(fake()->numberBetween(2, 5))->pluck('id'));
        }
    }
}
