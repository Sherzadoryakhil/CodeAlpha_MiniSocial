<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    // Only the author may edit a post.
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    // Only the author may delete a post.
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
