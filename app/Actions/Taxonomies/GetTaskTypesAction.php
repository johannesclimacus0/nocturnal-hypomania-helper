<?php

namespace App\Actions\Taxonomies;

use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetTaskTypesAction
{
    public function handle(User $actor): Collection
    {
        return TaskType::query()
            ->available($actor)
            ->orderBy('name')
            ->get([
                'uuid',
                'name',
                'slug',
                'user_id',
                'is_system',
            ]);
    }
}
