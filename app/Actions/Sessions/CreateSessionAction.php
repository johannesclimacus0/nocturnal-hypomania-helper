<?php

namespace App\Actions\Sessions;

use App\DTO\Sessions\CreateSessionData;
use App\Models\NightSession;
use App\Models\TaskType;
use App\Models\User;

final readonly class CreateSessionAction
{
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

        return $actor->nightSessions()->create([
            'available_time_minutes' => $data->availableTimeMinutes,
            'selection_strategy' => $data->selectionStrategy,
            'difficulty' => $data->difficulty,
            'area_id' => $area?->getKey(),
            'category_id' => $category?->getKey(),
            'task_type_id' => $taskType?->getKey(),
            'started_at' => now(),
        ]);
    }
}
