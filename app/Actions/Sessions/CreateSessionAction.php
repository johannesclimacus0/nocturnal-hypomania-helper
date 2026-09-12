<?php

namespace App\Actions\Sessions;

use App\Actions\SessionTasks\CreateSessionTaskAction;
use App\DTO\Sessions\CreateSessionData;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\Models\NightSession;
use App\Models\TaskType;
use App\Models\User;

final class CreateSessionAction
{
    public function __construct(
        private readonly SelectNextTaskAction $selectNextTask,
        private readonly CreateSessionTaskAction $createSessionTask,
    ) {}

    public function handle(User $actor, CreateSessionData $data): NightSession
    {
        $area = $data->areaUuid === null ? null
            : $actor->areas()
                ->where('uuid', $data->areaUuid)
                ->firstOrFail();

        $category = $data->categoryUuid === null ? null
            : $actor->categories()
                ->where('uuid', $data->categoryUuid)
                ->firstOrFail();

        $taskType = $data->taskTypeUuid === null ? null
            : TaskType::query()
                ->available($actor)
                ->where('uuid', $data->taskTypeUuid)
                ->firstOrFail();

        return $actor->getConnection()->transaction(function () use ($actor, $data, $area, $category, $taskType): NightSession {
            $session = $actor->nightSessions()->create([
                'available_time_minutes' => $data->availableTimeMinutes,
                'difficulty' => $data->difficulty,
                'area_id' => $area?->getKey(),
                'category_id' => $category?->getKey(),
                'task_type_id' => $taskType?->getKey(),
                'started_at' => now(),
            ]);

            $position = 1;
            while ($task = $this->selectNextTask->handle($actor, $session)) {
                $this->createSessionTask->handle($actor, $session, new CreateSessionTaskData(
                    taskUuid: $task->uuid,
                    position: $position++,
                ));
            }

            return $session;
        });
    }
}
