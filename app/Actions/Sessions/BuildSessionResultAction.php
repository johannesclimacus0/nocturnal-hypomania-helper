<?php

namespace App\Actions\Sessions;

use App\DTO\Sessions\SessionResult;
use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;

class BuildSessionResultAction
{
    public function handle(NightSession $session): SessionResult
    {
        $counts = $session->nightSessionTasks()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $completed = (int) ($counts[NightSessionTaskStatus::Completed->value] ?? 0);
        $skipped = (int) ($counts[NightSessionTaskStatus::Skipped->value] ?? 0);
        $unfinished = (int) ($counts[NightSessionTaskStatus::Selected->value] ?? 0);

        return new SessionResult(
            total: $completed + $skipped + $unfinished,
            completed: $completed,
            skipped: $skipped,
            unfinished: $unfinished,
            durationMinutes: (int) $session->started_at->diffInMinutes($session->ended_at),
        );
    }
}
