<?php

namespace App\Services\TaskSelection\Strategies;

use App\Enums\NightSessionTaskStatus;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

final class MostSkippedStrategy implements TaskSelectionStrategy
{
    public function select(Builder $candidates): ?Task
    {
        return $candidates
            ->withCount(['nightSessionTasks as selection_skips_count' => fn (Builder $query) => $query
                ->where('status', NightSessionTaskStatus::Skipped)])
            ->orderByDesc('selection_skips_count')
            ->orderBy('tasks.id')
            ->first();
    }
}
