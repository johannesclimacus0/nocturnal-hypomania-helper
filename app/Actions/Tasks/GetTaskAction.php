<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Caching\TaskCacheService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

final class GetTaskAction
{
    public function __construct(private readonly TaskCacheService $cache) {}

    public function handle(User $actor, Task $task): Task
    {
        Gate::forUser($actor)->authorize('view', $task);

        return $this->cache->getForUser($actor)->find($task->getKey());
    }
}
