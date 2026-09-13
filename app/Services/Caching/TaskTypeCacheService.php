<?php

namespace App\Services\Caching;

use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class TaskTypeCacheService extends AbstractCacheForUser
{
    protected function key(User $user): string
    {
        return 'users:' . $user->uuid . ':task-types';
    }

    protected function load(User $user): Collection
    {
        return TaskType::query()->available($user)->orderBy('name')->get(['uuid', 'name', 'slug', 'user_id', 'is_system']);
    }

    protected function ttl(): int
    {
        return 7*parent::ttl();
    }
}
