<?php

namespace App\Services\TaskSelection\Pipes;

use App\Services\TaskSelection\DTO\SelectionContext;
use Closure;

final class FilterDifficulty
{
    public function handle(SelectionContext $context, Closure $next): mixed
    {
        if ($context->session->difficulty !== null) {
            $context->candidates->where('difficulty', $context->session->difficulty);
        }

        return $next($context);
    }
}
