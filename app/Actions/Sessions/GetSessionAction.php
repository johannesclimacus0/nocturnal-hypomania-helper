<?php

namespace App\Actions\Sessions;

use App\Models\NightSession;
use App\Models\User;
use App\Services\Caching\SessionCacheService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

final class GetSessionAction
{
    public function __construct(private readonly SessionCacheService $cache) {}

    public function handle(User $actor, NightSession $session): NightSession
    {
        Gate::forUser($actor)->authorize('view', $session);

        return $this->cache->getForUser($actor)->find($session->getKey());
    }
}
