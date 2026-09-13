<?php

namespace App\Contracts;

use App\Models\User;

interface CacheForUser
{
    public function getForUser(User $user): mixed;

    public function forgetForUser(User $user): void;

    public function refreshForUser(User $user): mixed;
}
