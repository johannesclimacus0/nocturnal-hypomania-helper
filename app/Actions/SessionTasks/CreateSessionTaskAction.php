<?php

namespace App\Actions\SessionTasks;

use App\DTO\SessionTasks\CreateSessionTaskData;
use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateSessionTaskAction
{
    public function handle(
        User $actor,
        NightSession $session,
        CreateSessionTaskData $data
    ): NightSessionTask {
        Gate::forUser($actor)->authorize('create', [NightSessionTask::class, $session]);

        $task = $actor->tasks()
            ->where('uuid', $data->taskUuid)
            ->firstOrFail();

        return $session->getConnection()->transaction(function () use ($session, $task, $data): NightSessionTask {
            $session->newQuery()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();

            if ($session->nightSessionTasks()->where('task_id', $task->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'task_uuid' => 'Задача уже добавлена в эту сессию.',
                ]);
            }

            $sessionTask = new NightSessionTask([
                'status' => NightSessionTaskStatus::Selected,
                'position' => $data->position,
                'selected_at' => now(),
            ]);

            $sessionTask->task()->associate($task);
            $session->nightSessionTasks()->save($sessionTask);

            return $sessionTask;
        });
    }
}
