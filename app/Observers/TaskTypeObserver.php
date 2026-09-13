<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Models\TaskType;
use App\Services\Caching\SessionCacheService;
use App\Services\Caching\TaskCacheService;
use App\Services\Caching\TaskTypeCacheService;

final class TaskTypeObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly TaskTypeCacheService $taskTypes,
        private readonly TaskCacheService $tasks,
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(TaskType $model): void
    {
        $this->refreshCaches($model);
    }

    public function deleted(TaskType $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(TaskType $model): void
    {
        $allUsers = $model->user_id === null
            || ($model->wasChanged('user_id')
                && $model->getOriginal('user_id') === null);

        $this->refreshUserCaches($model, [$model->user_id, $model->getOriginal('user_id')], [
            $this->taskTypes, $this->tasks, $this->sessions,
        ], $allUsers);
    }
}
