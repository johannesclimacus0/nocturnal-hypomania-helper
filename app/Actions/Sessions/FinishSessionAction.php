<?php

namespace App\Actions\Sessions;

use App\Models\NightSession;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FinishSessionAction
{
    public function handle(User $actor, NightSession $session): NightSession
    {
        Gate::forUser($actor)->authorize('update', $session);

        $session->update([
            'ended_at' => now(),
        ]);

        return $session;
    }
}
