<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $user->loadCount(['posts', 'followers', 'following']);

        $posts = $user->posts()
            ->with('user')
            ->withCount(['comments', 'likedBy as likes_count'])
            ->withLikeState($request->user())
            ->latest()
            ->latest('id')
            ->paginate(10);

        Comment::attachLatestTo($posts->getCollection(), 2);

        $isFollowing = $request->user()->isNot($user) && $request->user()->isFollowing($user);

        return view('profiles.show', compact('user', 'posts', 'isFollowing'));
    }

    public function edit(Request $request): View
    {
        return view('profiles.edit', ['user' => $request->user()]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Always the logged-in user. No user id is ever read from the request,
        // so nobody can edit another person's profile.
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'username', 'email', 'bio']));

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            // Stored under a random file name in storage/app/public/profile-photos.
            $user->profile_photo_path = $request->file('photo')->store('profile-photos', 'public');
        }

        $user->save();

        return redirect()->route('profile.show', $user)->with('success', 'Your profile was updated.');
    }
}
