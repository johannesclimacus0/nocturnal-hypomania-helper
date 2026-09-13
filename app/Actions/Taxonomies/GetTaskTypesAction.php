<?php

namespace App\Actions\Taxonomies;

use App\Models\User;
use App\Services\Caching\TaskTypeCacheService;
use Illuminate\Database\Eloquent\Collection;

class GetTaskTypesAction
{
    public function __construct(private readonly TaskTypeCacheService $cache) {}

    public function handle(User $actor): Collection
    {
        return $this->cache->getForUser($actor);
    }
}
