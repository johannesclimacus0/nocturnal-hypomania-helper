<?php

namespace App\Actions\Sessions;

use App\Actions\SessionTasks\CreateSessionTaskAction;
use App\DTO\Sessions\CreateSessionData;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\Models\NightSession;
use App\Models\User;

final class StartSessionAction
{
    public function __construct(
        private readonly CreateSessionAction $createSession,
        private readonly SelectNextTaskAction $selectNextTask,
        private readonly CreateSessionTaskAction $createSessionTask,
    ) {}

    public function handle(User $actor, CreateSessionData $data): NightSession
    {
        return $actor->getConnection()->transaction(function () use ($actor, $data): NightSession {
            $session = $this->createSession->handle($actor, $data);

            $position = 1;
            while ($task = $this->selectNextTask->handle($actor, $session)) {
                $this->createSessionTask->handle($actor, $session, new CreateSessionTaskData(
                    taskUuid: $task->uuid,
                    position: $position++,
                ));
            }

            return $session->load([
                'area',
                'category',
                'taskType',
                'nightSessionTasks.task',
            ]);
        });
    }
}
