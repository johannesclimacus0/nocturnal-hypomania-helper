<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Models\Category;
use App\Services\Caching\CategoryCacheService;
use App\Services\Caching\SessionCacheService;
use App\Services\Caching\TaskCacheService;

final class CategoryObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly CategoryCacheService $categories,
        private readonly TaskCacheService $tasks,
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(Category $model): void
    {
        $this->refreshCaches($model);
    }

    public function deleted(Category $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(Category $model): void
    {
        $this->refreshUserCaches($model, [$model->user_id, $model->getOriginal('user_id')], [

            $this->categories, $this->tasks, $this->sessions,
        ]);
    }
}
