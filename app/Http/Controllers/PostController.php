<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    // Home feed: all users' posts, newest first, 10 per page.
    // with('user') loads every author in one query (avoids the N+1 problem).
    public function index(Request $request): View
    {
        $posts = Post::with('user')
            ->withCount(['comments', 'likedBy as likes_count'])
            ->withLikeState($request->user())
            ->latest()
            ->latest('id')
            ->paginate(10);

        // The latest two comments of each post on this page.
        Comment::attachLatestTo($posts->getCollection(), 2);

        return view('posts.index', compact('posts'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = ['body' => $request->validated('body')];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('post-images', 'public');
        }

        // The author is always the logged-in user, never a value from the form.
        $request->user()->posts()->create($data);

        return redirect()->route('dashboard')->with('success', 'Your post was published.');
    }

    public function show(Request $request, Post $post): View
    {
        $post->load('user')->loadCount(['comments', 'likedBy as likes_count']);
        $post->loadExists([
            'likedBy as liked_by_me' => fn ($q) => $q->whereKey($request->user()->id),
        ]);

        // The detail page shows every comment, oldest first.
        $comments = $post->comments()->with('user')->oldest()->oldest('id')->get();

        return view('posts.show', compact('post', 'comments'));
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->body = $request->validated('body');

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $post->image_path = $request->file('image')->store('post-images', 'public');
        } elseif ($request->boolean('remove_image') && $post->image_path) {
            Storage::disk('public')->delete($post->image_path);
            $post->image_path = null;
        }

        $post->save();

        return redirect()->route('posts.show', $post)->with('success', 'Your post was updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        // Comments and likes of this post are removed by the database (ON DELETE CASCADE).
        $post->delete();

        return redirect()->route('dashboard')->with('success', 'Your post was deleted.');
    }
}
