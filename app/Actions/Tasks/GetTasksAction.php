<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Caching\TaskCacheService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class GetTasksAction
{
    public function __construct(private readonly TaskCacheService $cache) {}

    public function handle(User $actor): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewAny', Task::class);

        $items = $this->cache->getForUser($actor);
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator($items->forPage($page, 20)->values(), $items->count(), 20, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }
}
