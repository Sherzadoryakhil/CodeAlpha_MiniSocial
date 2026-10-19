<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class FollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        // Rule 1: nobody can follow themselves. (MySQL also blocks it with a trigger.)
        if ($me->is($user)) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        // Rule 2: no duplicate follows. The unique key (follower_id, following_id)
        // in the database refuses a second row, and we simply ignore that case.
        try {
            $me->following()->attach($user->id);
        } catch (UniqueConstraintViolationException) {
            // Already following. Nothing to do.
        }

        return back()->with('success', 'You are now following ' . $user->name . '.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Only the logged-in user's own follow row can be removed.
        $request->user()->following()->detach($user->id);

        return back()->with('success', 'You unfollowed ' . $user->name . '.');
    }

    // People who follow $user, newest first.
    public function followers(Request $request, User $user): View
    {
        $users = $user->followers()
            ->orderByPivot('created_at', 'desc')
            ->orderByPivot('id', 'desc')
            ->paginate(20);

        return $this->listView($request, $user, $users, 'Followers', 'No followers yet.');
    }

    // People that $user follows, newest first.
    public function following(Request $request, User $user): View
    {
        $users = $user->following()
            ->orderByPivot('created_at', 'desc')
            ->orderByPivot('id', 'desc')
            ->paginate(20);

        return $this->listView($request, $user, $users, 'Following', 'Not following anyone yet.');
    }

    private function listView(Request $request, User $user, LengthAwarePaginator $users, string $title, string $emptyText): View
    {
        // Which of the listed people does the logged-in user follow? One query for the whole page.
        $followingIds = $request->user()->following()
            ->whereIn('users.id', $users->pluck('id')->all())
            ->pluck('users.id')
            ->all();

        return view('follows.list', compact('user', 'users', 'title', 'emptyText', 'followingIds'));
    }
}
