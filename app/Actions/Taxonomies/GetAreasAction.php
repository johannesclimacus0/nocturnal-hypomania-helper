<?php

namespace App\Actions\Taxonomies;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetAreasAction
{
    public function handle(User $actor): Collection
    {
        return $actor
            ->areas()
            ->orderBy('name')
            ->get();
    }
}
