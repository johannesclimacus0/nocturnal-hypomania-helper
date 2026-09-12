<?php

namespace App\Services\TaskSelection\Pipes;

use App\Services\TaskSelection\DTO\SelectionContext;
use Closure;

final class FilterTaxonomy
{
    public function handle(SelectionContext $context, Closure $next): mixed
    {
        foreach (['area_id', 'category_id', 'task_type_id'] as $column) {
            if ($context->session->{$column} !== null) {
                $context->candidates->where($column, $context->session->{$column});
            }
        }

        return $next($context);
    }
}
