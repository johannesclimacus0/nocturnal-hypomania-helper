<?php

namespace App\Actions\Sessions;

use App\Models\NightSession;
use App\Models\User;

final class CreateSessionAction
{
    public function handle(User $actor): NightSession
    {
        return $actor->nightSessions()->create([
            'started_at' => now(),
        ]);
    }
}
