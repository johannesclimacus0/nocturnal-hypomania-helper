<?php

namespace App\Concerns;

use App\Contracts\CacheForUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model as EloquentModel;

trait RefreshesUserCaches
{
    /**
     * @param  array<int, int|null>  $ownerIds
     * @param  array<int, CacheForUser>  $caches
     */
    protected function refreshUserCaches(
        EloquentModel $model,
        array $ownerIds,
        array $caches,
        bool $allUsers = false,
    ): void {
        $ownerIds = array_unique(array_filter($ownerIds));
        $users = $allUsers ? User::query() : User::query()->whereIn('id', $ownerIds);

        foreach ($users->lazyById() as $user) {
            foreach ($caches as $cache) {
                $cache->refreshForUser($user);
            }
        }
    }
}
