<?php

namespace App\Observers;

use App\Concerns\RefreshesUserCaches;
use App\Events\NightSessionEnded;
use App\Models\NightSession;
use App\Services\Caching\SessionCacheService;

final class NightSessionObserver
{
    use RefreshesUserCaches;

    public function __construct(
        private readonly SessionCacheService $sessions,
    ) {}

    public function saved(NightSession $model): void
    {
        $this->refreshCaches($model);
    }

    public function updated(NightSession $nightSession): void
    {
        if (!$nightSession->wasChanged('ended_at') || $nightSession->ended_at === null) {
            return;
        }
        NightSessionEnded::dispatch($nightSession);
    }

    public function deleted(NightSession $model): void
    {
        $this->refreshCaches($model);
    }

    private function refreshCaches(NightSession $model): void
    {
        $this->refreshUserCaches($model, [$model->user_id, $model->getOriginal('user_id')], [$this->sessions]);
    }
}
