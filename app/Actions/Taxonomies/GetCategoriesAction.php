<?php

namespace App\Actions\Taxonomies;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetCategoriesAction
{
    public function handle(User $actor): Collection
    {
        return $actor->categories()
            ->orderBy('name')
            ->get([
                'uuid',
                'name',
            ]);
    }
}
