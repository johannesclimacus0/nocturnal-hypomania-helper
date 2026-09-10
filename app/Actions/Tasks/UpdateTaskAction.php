<?php

namespace App\Actions\Tasks;

use App\DTO\Tasks\UpdateTaskData;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

final class UpdateTaskAction
{
    public function handle(User $actor, Task $task, UpdateTaskData $data): Task
    {
        Gate::forUser($actor)->authorize('update', $task);

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

        $task->fill([
            'title' => $data->title,
            'description' => $data->description,
            'estimated_time_minutes' => $data->estimatedTimeMinutes,
            'difficulty' => $data->difficulty,
            'status' => $data->status,
        ]);

        $task->taskType()->associate($taskType);
        $task->area()->associate($area);
        $task->category()->associate($category);
        $task->save();

        return $task->load(['taskType', 'area', 'category']);
    }
}
