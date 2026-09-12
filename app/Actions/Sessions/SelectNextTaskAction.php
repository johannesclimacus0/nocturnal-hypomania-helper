<?php

namespace App\Actions\Sessions;

use App\Enums\TaskStatus;
use App\Models\NightSession;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskSelection\DTO\SelectionContext;
use App\Services\TaskSelection\Pipes\ExcludeSessionTasks;
use App\Services\TaskSelection\Pipes\FilterDifficulty;
use App\Services\TaskSelection\Pipes\FilterTaxonomy;
use App\Services\TaskSelection\Pipes\LimitEstimatedTime;
use App\Services\TaskSelection\Strategies\TaskSelectionStrategy;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

final class SelectNextTaskAction
{
    public function __construct(
        private TaskSelectionStrategy $strategy,
    )
    {
    }

    public function handle(User $actor, NightSession $session): ?Task
    {
        Gate::forUser($actor)->authorize('update', $session);

        if ($session->ended_at !== null) {
            return null;
        }

        $candidates = $actor->tasks()
            ->where('status', TaskStatus::Active)
            ->getQuery();

        $context = new SelectionContext(
            session: $session,
            candidates: $candidates,
        );

        $context = app(Pipeline::class)
            ->send($context)
            ->through([
                ExcludeSessionTasks::class,
                LimitEstimatedTime::class,
                FilterDifficulty::class,
                FilterTaxonomy::class,
            ])->thenReturn();

        return $this->strategy->select($context->candidates);
    }
}
