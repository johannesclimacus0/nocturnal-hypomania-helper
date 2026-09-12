<?php

namespace App\Observers;

use App\Events\NightSessionEnded;
use App\Models\NightSession;

class SessionObserver
{
    public function updated(NightSession $nightSession): void
    {
        if (!$nightSession->wasChanged('ended_at') || $nightSession->ended_at === null) {
            return;
        }
        NightSessionEnded::dispatch($nightSession);
    }
}
