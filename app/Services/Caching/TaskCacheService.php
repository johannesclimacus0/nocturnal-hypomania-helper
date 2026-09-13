<?php

namespace App\Services\Caching;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class TaskCacheService extends AbstractCacheForUser
{
    protected function key(User $user): string
    {
        return 'users:' . $user->uuid . ':tasks';
    }

    protected function load(User $user): Collection
    {
        return $user->tasks()->with(['area', 'category', 'taskType'])->latest('id')->get();
    }

    protected function ttl(): int
    {
        return 3*parent::ttl();
    }
}
