<?php

namespace App\Actions\SessionTasks;

use App\Models\NightSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class GetSessionTasksAction
{
    public function handle(User $actor, NightSession $session): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('view', $session);

        return $session->nightSessionTasks()
            ->with(['task.taskType', 'task.area', 'task.category'])
            ->orderBy('position')->orderBy('id')->paginate(20);
    }
}
