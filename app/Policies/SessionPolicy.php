<?php

namespace App\Policies;

use App\Models\NightSession;
use App\Models\User;

final class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, NightSession $session): bool
    {
        return $session->user_id === $user->getKey();
    }

    public function update(User $user, NightSession $session): bool
    {
        return $this->view($user, $session);
    }
}
