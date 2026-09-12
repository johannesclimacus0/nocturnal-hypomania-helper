<?php

namespace App\Services\TaskSelection\Pipes;

use App\Services\TaskSelection\DTO\SelectionContext;
use Closure;

final class ExcludeSessionTasks
{
    public function handle(SelectionContext $context, Closure $next): mixed
    {
        $context->candidates->whereNotIn('tasks.id',
            function ($query) use ($context) {
                $query
                    ->select('task_id')
                    ->from('night_session_tasks')
                    ->where('night_session_id', $context->session->getKey());
            },
        );

        return $next($context);
    }
}
