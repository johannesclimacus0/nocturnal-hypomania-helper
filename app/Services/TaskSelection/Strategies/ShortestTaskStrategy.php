<?php

namespace App\Services\TaskSelection\Strategies;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class ShortestTaskStrategy implements TaskSelectionStrategy
{
    public function select(Builder $candidates): ?Task
    {
        return $candidates
            ->orderBy('estimated_time_minutes')
            ->first();
    }
}
