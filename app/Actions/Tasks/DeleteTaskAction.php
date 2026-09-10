<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class DeleteTaskAction
{
    public function handle(User $actor, Task $task): void
    {
        Gate::forUser($actor)->authorize('delete', $task);

        $task->delete();
    }
}
