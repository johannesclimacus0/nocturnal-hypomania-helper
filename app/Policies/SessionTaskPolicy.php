<?php

namespace App\Policies;

use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\User;

class SessionTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NightSessionTask $task): bool
    {
        return $task->nightSession()
            ->where('user_id', $user->getKey())
            ->exists();
    }

    public function create(User $user, NightSession $session): bool
    {
        return $session->user_id === $user->getKey();
    }

    public function update(User $user, NightSessionTask $task): bool
    {
        return $task->nightSession()
            ->where('user_id', $user->getKey())
            ->exists();
    }

    public function delete(User $user, NightSessionTask $nightsessionTask): bool
    {
        return $this->view($user, $nightsessionTask);
    }
}
