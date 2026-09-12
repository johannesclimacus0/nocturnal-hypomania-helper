<?php

namespace App\Services\TaskSelection\Strategies;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

interface TaskSelectionStrategy
{
    public function select(Builder $candidates): ?Task;
}
