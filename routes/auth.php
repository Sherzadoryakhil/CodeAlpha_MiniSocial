<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// Only registration, login and logout are part of Connectly.
// Password reset and email verification are out of scope, so their routes are removed.

Route::middleware('guest')->group(function () {
    // At most 10 registration attempts per minute from one address (slows down bots).
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');

    // Login attempts are limited by Breeze's LoginRequest (5 wrong tries, then a wait).
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
