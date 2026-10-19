<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        // post_id and user_id come from the URL and the logged-in user, never from the form.
        $comment = new Comment(['body' => $request->validated('body')]);
        $comment->post()->associate($post);
        $comment->user()->associate($request->user());
        $comment->save();

        return redirect(route('posts.show', $post) . '#comment-' . $comment->id)
            ->with('success', 'Your comment was added.');
    }

    public function edit(Comment $comment): View
    {
        Gate::authorize('update', $comment);

        return view('comments.edit', compact('comment'));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $comment->update(['body' => $request->validated('body')]);

        return redirect(route('posts.show', $comment->post_id) . '#comment-' . $comment->id)
            ->with('success', 'Your comment was updated.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $postId = $comment->post_id;
        $comment->delete();

        return redirect(route('posts.show', $postId) . '#comments')
            ->with('success', 'Your comment was deleted.');
    }
}
