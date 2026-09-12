<?php

namespace App\Services\TaskSelection\Pipes;

use App\Services\TaskSelection\DTO\SelectionContext;
use Closure;

final class LimitEstimatedTime
{
    public function handle(SelectionContext $context, Closure $next): mixed
    {
        $context->candidates->where(
            'estimated_time_minutes',
            '<=',
            $context->session->available_time_minutes
        );

        return $next($context);
    }
}
