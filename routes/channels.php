<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{uuid}', function (\App\Models\User $user, string $uuid) {
    return $user->uuid === $uuid;
});
