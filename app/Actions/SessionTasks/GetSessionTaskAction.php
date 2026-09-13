<?php

namespace App\Actions\SessionTasks;

use App\Models\NightSessionTask;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class GetSessionTaskAction
{
    public function handle(User $actor, NightSessionTask $task): NightSessionTask
    {
        Gate::forUser($actor)->authorize('view', $task);

        return $task->load(['task.taskType', 'task.area', 'task.category']);
    }
}
