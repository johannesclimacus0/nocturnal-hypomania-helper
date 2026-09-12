<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SessionTaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTypeController;
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
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
        ]);
    });

    Route::view('/verify-email', 'app')->name('verification.notice');

    Route::middleware('verified')->group(function (): void {
        Route::view('/home', 'app');
        Route::view('/tasks/create', 'app');
        Route::view('/tasks/{task}/edit', 'app')->whereUuid('task');
        Route::view('/areas', 'app');
        Route::view('/categories', 'app');
        Route::view('/task-types', 'app');

        Route::prefix('api')->group(function (): void {
            Route::apiResource('tasks', TaskController::class);
            Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::post('/task-types', [TaskTypeController::class, 'store'])->name('task-types.store');
            Route::get('/task-types', [TaskTypeController::class, 'index'])->name('task-types.index');
            Route::apiResource('sessions', SessionController::class)->only(['index', 'store', 'show']);
            Route::patch('/sessions/{session}/finish', [SessionController::class, 'finish'])->name('api.sessions.finish');
            Route::get('/sessions/{session}/tasks', [SessionTaskController::class, 'index'])->name('api.sessions.tasks.index');
            Route::post('/sessions/{session}/tasks', [SessionTaskController::class, 'store'])->name('api.sessions.tasks.store');
            Route::patch('/sessions/{session}/tasks/{sessionTaskUuid}/complete', [SessionTaskController::class, 'complete'])
                ->whereUuid('sessionTaskUuid')->name('api.sessions.tasks.complete');
            Route::patch('/sessions/{session}/tasks/{sessionTaskUuid}/skip', [SessionTaskController::class, 'skip'])
                ->whereUuid('sessionTaskUuid')->name('api.sessions.tasks.skip');
        });

        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('sessions', SessionController::class)->only(['index', 'store', 'show']);
        Route::patch('/sessions/{session}/finish', [SessionController::class, 'finish'])->name('sessions.finish');
        Route::get('/sessions/{session}/tasks', [SessionTaskController::class, 'index'])->name('sessions.tasks.index');
        Route::post('/sessions/{session}/tasks', [SessionTaskController::class, 'store'])->name('sessions.tasks.store');
        Route::patch('/sessions/{session}/tasks/{sessionTaskUuid}/complete', [SessionTaskController::class, 'complete'])
            ->whereUuid('sessionTaskUuid')->name('sessions.tasks.complete');
        Route::patch('/sessions/{session}/tasks/{sessionTaskUuid}/skip', [SessionTaskController::class, 'skip'])
            ->whereUuid('sessionTaskUuid')->name('sessions.tasks.skip');
    });
});
