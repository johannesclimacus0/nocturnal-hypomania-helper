<?php

namespace App\Services\Caching;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class SessionCacheService extends AbstractCacheForUser
{
    protected function key(User $user): string
    {
        return 'users:' . $user->uuid . ':sessions';
    }

    protected function load(User $user): Collection
    {
        return $user->nightSessions()
            ->with(['area', 'category', 'taskType', 'nightSessionTasks' => fn ($query) => $query
                ->orderBy('position')->orderBy('id')
                ->with(['task.taskType', 'task.area', 'task.category'])])
            ->withCount('nightSessionTasks')->latest('started_at')->latest('id')->get();
    }
}
