<?php

namespace App\Actions\SessionTasks;

use App\DTO\SessionTasks\UpdateSessionTaskData;
use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class CompleteSessionTaskAction
{
    public function handle(
        User $actor,
        NightSession $session,
        UpdateSessionTaskData $data
    ): NightSessionTask {
        $task = $session->nightSessionTasks()
            ->where('uuid', $data->sessionTaskUuid)
            ->firstOrFail();

        Gate::forUser($actor)->authorize('update', $task);

        $task->update([
            'status' => NightSessionTaskStatus::Completed,
            'completed_at' => now(),
            'skipped_at' => null,
        ]);

        return $task->refresh();
    }
}
