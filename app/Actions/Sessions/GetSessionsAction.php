<?php

namespace App\Actions\Sessions;

use App\Models\NightSession;
use App\Models\User;
use App\Services\Caching\SessionCacheService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class GetSessionsAction
{
    public function __construct(private readonly SessionCacheService $cache) {}

    public function handle(User $actor): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewAny', NightSession::class);

        $items = $this->cache->getForUser($actor);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items->each(fn (NightSession $session) => $session->unsetRelation('nightSessionTasks'));

        return new LengthAwarePaginator($items->forPage($page, 20)->values(), $items->count(), 20, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }
}
