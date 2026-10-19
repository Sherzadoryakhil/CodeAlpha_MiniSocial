<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    // Only the comment's author may edit it.
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    // Only the comment's author may delete it.
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
