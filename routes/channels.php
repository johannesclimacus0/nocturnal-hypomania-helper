<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{uuid}', function (User $user, string $uuid) {
    return $user->uuid === $uuid;
});
