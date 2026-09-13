<?php

namespace App\Services\Caching;

use App\Contracts\CacheForUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

abstract class AbstractCacheForUser implements CacheForUser
{
    public function getForUser(User $user): Collection
    {
        return Cache::remember($this->key($user), $this->ttl(), fn () => $this->load($user));
    }

    public function forgetForUser(User $user): void
    {
        Cache::forget($this->key($user));
    }

    public function refreshForUser(User $user): Collection
    {
        return $this->storeForUser($user);
    }

    private function storeForUser(User $user): Collection
    {
        $this->forgetForUser($user);
        $value = $this->load($user);
        Cache::put($this->key($user), $value, $this->ttl());

        return $value;
    }

    protected function ttl(): int
    {
        return 24*60*60;
    }

    abstract protected function key(User $user): string;

    abstract protected function load(User $user): Collection;
}
