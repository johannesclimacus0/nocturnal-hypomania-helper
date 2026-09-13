<?php

namespace App\Services\Caching;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class AreaCacheService extends AbstractCacheForUser
{
    protected function key(User $user): string
    {
        return 'users:' . $user->uuid . ':areas';
    }

    protected function load(User $user): Collection
    {
        return $user->areas()->orderBy('name')->get();
    }

    protected function ttl(): int
    {
        return 7*parent::ttl();
    }
}
