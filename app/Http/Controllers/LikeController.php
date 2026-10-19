<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        try {
            $request->user()->likedPosts()->attach($post->id);
        } catch (UniqueConstraintViolationException) {
            // Already liked. The unique key (user_id, post_id) in the database
            // refused the duplicate, so there is nothing more to do.
        }

        return back()->withFragment('post-' . $post->id);
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $request->user()->likedPosts()->detach($post->id);

        return back()->withFragment('post-' . $post->id);
    }
}
