<?php

namespace App\Actions\SessionTasks;

use App\Actions\Sessions\SelectNextTaskAction;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\DTO\SessionTasks\SkipSessionTaskResult;
use App\DTO\SessionTasks\UpdateSessionTaskData;
use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SkipSessionTaskAction
{
    public function __construct(
        private readonly SelectNextTaskAction $selectNextTask,
        private readonly CreateSessionTaskAction $createSessionTask,
    )
    {}

    public function handle(
        User $actor,
        NightSession $session,
        UpdateSessionTaskData $data
    ): SkipSessionTaskResult {
        Gate::forUser($actor)->authorize('update', $session);

        return $session->getConnection()->transaction(function () use ($actor, $session, $data): SkipSessionTaskResult {
            $session = $session->newQuery()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $task = $session->nightSessionTasks()->where('uuid', $data->sessionTaskUuid)->firstOrFail();

            if ($session->ended_at !== null || $task->status !== NightSessionTaskStatus::Selected) {
                throw ValidationException::withMessages([
                    'session_task' => 'Only a selected task in an active session can be skipped.',
                ]);
            }

            $task->update([
                'status' => NightSessionTaskStatus::Skipped,
                'skipped_at' => now(),
                'completed_at' => null,
            ]);

            $candidate = $this->selectNextTask->handle($actor, $session);
            $replacement = $candidate === null ? null : $this->createSessionTask->handle(
                $actor,
                $session,
                new CreateSessionTaskData(taskUuid: $candidate->uuid, position: $task->position),
            );

            return new SkipSessionTaskResult($task->refresh(), $replacement);
        });
    }
}
