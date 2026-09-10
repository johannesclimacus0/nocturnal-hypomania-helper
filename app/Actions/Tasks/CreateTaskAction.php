<?php

namespace App\Actions\Tasks;

use App\DTO\Tasks\CreateTaskData;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class CreateTaskAction
{
    public function handle(User $actor, CreateTaskData $data): Task
    {
        $taskType = TaskType::query()
            ->where('uuid', $data->taskTypeUuid)
            ->where(function (Builder $query) use ($actor): void {
                $query->where('user_id', $actor->getKey())
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('user_id')
                            ->where('is_system', true);
                    });
            })
            ->firstOrFail();

        $area = $data->areaUuid !== null
            ? $actor->areas()
                ->where('uuid', $data->areaUuid)
                ->firstOrFail()
            : null;

        $category = $data->categoryUuid !== null
            ? $actor->categories()
                ->where('uuid', $data->categoryUuid)
                ->firstOrFail()
            : null;

        $task = new Task([
            'title' => $data->title,
            'description' => $data->description,
            'estimated_time_minutes' => $data->estimatedTimeMinutes,
            'difficulty' => $data->difficulty,
            'status' => TaskStatus::Active,
        ]);

        $task->taskType()->associate($taskType);
        $task->area()->associate($area);
        $task->category()->associate($category);
        $actor->tasks()->save($task);

        return $task->load(['taskType', 'area', 'category']);
    }
}
