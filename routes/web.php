<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => $user->getKey(),
        'name' => $user->name,
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at,
    ]);
})->middleware('auth');

Route::view('/', 'app');

Route::view('/login', 'app')
    ->name('login');

Route::view('/register', 'app');

Route::view('/forgot-password', 'app');

Route::view('/verify-email', 'app')
    ->middleware('auth')
    ->name('verification.notice');

Route::view('/home', 'app')
    ->middleware(['auth', 'verified']);

Route::view('/reset-password/{token}', 'app')
    ->name('password.reset');
