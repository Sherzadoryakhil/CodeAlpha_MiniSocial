<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    // Home feed. The route keeps the name "dashboard" because Breeze's login
    // and registration code redirect to route('dashboard').
    Route::get('/', [PostController::class, 'index'])->name('dashboard');

    Route::resource('posts', PostController::class)
        ->only(['store', 'show', 'edit', 'update', 'destroy']);

    // POST /posts/{post}/comments, then /comments/{comment}/edit, PATCH and DELETE /comments/{comment}
    Route::resource('posts.comments', CommentController::class)
        ->shallow()
        ->only(['store', 'edit', 'update', 'destroy']);

    Route::post('/posts/{post}/like', [LikeController::class, 'store'])->name('posts.like');
    Route::delete('/posts/{post}/like', [LikeController::class, 'destroy'])->name('posts.unlike');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');

    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('follows.store');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('follows.destroy');
    Route::get('/users/{user}/followers', [FollowController::class, 'followers'])->name('follows.followers');
    Route::get('/users/{user}/following', [FollowController::class, 'following'])->name('follows.following');
});

require __DIR__.'/auth.php';
