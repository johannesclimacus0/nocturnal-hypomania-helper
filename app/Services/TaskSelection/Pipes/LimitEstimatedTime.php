<?php

namespace App\Services\TaskSelection\Pipes;

use App\Enums\NightSessionTaskStatus;
use App\Services\TaskSelection\DTO\SelectionContext;
use Closure;

final class LimitEstimatedTime
{
    public function handle(SelectionContext $context, Closure $next): mixed
    {
        $used = $context->session->nightSessionTasks()
            ->where('night_session_tasks.status', '!=', NightSessionTaskStatus::Skipped)
            ->join('tasks', 'tasks.id', '=', 'night_session_tasks.task_id')
            ->sum('tasks.estimated_time_minutes');

        $remaining = max(0, $context->session->available_time_minutes - $used);

        $context->candidates->where(
            'estimated_time_minutes',
            '<=',
            $remaining
        );

        return $next($context);
    }
}
