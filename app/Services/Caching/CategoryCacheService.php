<?php

namespace App\Services\Caching;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class CategoryCacheService extends AbstractCacheForUser
{
    protected function key(User $user): string
    {
        return 'users:' . $user->uuid . ':categories';
    }

    protected function load(User $user): Collection
    {
        return $user->categories()->orderBy('name')->get(['uuid', 'name']);
    }

    protected function ttl(): int
    {
        return 7*parent::ttl();
    }
}
