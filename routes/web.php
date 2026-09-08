<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/me', function (Request $request) {
    return response()->json($request->user());
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
