<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Services\Caching\SessionCacheService;

final class NightSessionTaskObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(NightSessionTask $model): void
    {
        $this->refreshCaches($model);
    }

    public function deleted(NightSessionTask $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(NightSessionTask $model): void
    {
        $ownerIds = NightSession::query()
            ->whereIn('id', array_filter([
                $model->night_session_id, $model->getOriginal('night_session_id'),
            ]))->pluck('user_id')->all();

        $this->refreshUserCaches($model, $ownerIds, [$this->sessions]);
    }
}
