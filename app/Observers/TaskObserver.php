<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Models\Task;
use App\Services\Caching\SessionCacheService;
use App\Services\Caching\TaskCacheService;

final class TaskObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly TaskCacheService $tasks,
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(Task $model): void
    {
        $this->refreshCaches($model);
    }

    public function deleted(Task $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(Task $model): void
    {
        $this->refreshUserCaches(
            $model,
            [$model->user_id, $model->getOriginal('user_id')],
            [$this->tasks, $this->sessions],
        );
    }
}
