<?php

namespace App\Services\TaskSelection\Strategies;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

final class LeastRecentlySelectedStrategy implements TaskSelectionStrategy
{
    public function select(Builder $candidates): ?Task
    {
        return $candidates
            ->withMax('nightSessionTasks as selection_last_selected_at', 'selected_at')
            ->orderByRaw('selection_last_selected_at ASC NULLS FIRST')
            ->orderBy('tasks.id')
            ->first();
    }
}
