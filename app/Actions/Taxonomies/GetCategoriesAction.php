<?php

namespace App\Actions\Taxonomies;

use App\Models\User;
use App\Services\Caching\CategoryCacheService;
use Illuminate\Database\Eloquent\Collection;

class GetCategoriesAction
{
    public function __construct(private readonly CategoryCacheService $cache) {}

    public function handle(User $actor): Collection
    {
        return $this->cache->getForUser($actor);
    }
}
