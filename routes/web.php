<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
Route::view('/login', 'app')->name('login');
Route::view('/register', 'app');
Route::view('/forgot-password', 'app');
Route::view('/reset-password/{token}', 'app')->name('password.reset');

Route::middleware('auth')->group(function (): void {
    Route::get('/me', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
        ]);
    });

    Route::view('/verify-email', 'app')->name('verification.notice');

    Route::middleware('verified')->group(function (): void {
        Route::view('/home', 'app');

        Route::apiResource('tasks', TaskController::class);
    });
});
