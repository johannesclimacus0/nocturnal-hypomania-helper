<?php

namespace App\Actions\Taxonomies;

use App\Models\User;
use App\Services\Caching\AreaCacheService;
use Illuminate\Database\Eloquent\Collection;

class GetAreasAction
{
    public function __construct(private readonly AreaCacheService $cache) {}

    public function handle(User $actor): Collection
    {
        return $this->cache->getForUser($actor);
    }
}
