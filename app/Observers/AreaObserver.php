<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Models\Area;
use App\Services\Caching\AreaCacheService;
use App\Services\Caching\SessionCacheService;
use App\Services\Caching\TaskCacheService;

final class AreaObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly AreaCacheService $areas,
        private readonly TaskCacheService $tasks,
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(Area $model): void
    {
        $this->refreshCaches($model);
    }

    public function deleted(Area $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(Area $model): void
    {
        $this->refreshUserCaches($model, [$model->user_id, $model->getOriginal('user_id')], [
            $this->areas, $this->tasks, $this->sessions,
        ]);
    }
}
