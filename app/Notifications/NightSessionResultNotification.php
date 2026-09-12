<?php

namespace App\Notifications;

use App\DTO\Sessions\SessionResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NightSessionResultNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $sessionUuid,
        private readonly SessionResult $result
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'session_uuid' => $this->sessionUuid,
            'result' => $this->result->toArray(),
        ]);
    }

    public function broadcastType(): string
    {
        return 'night-session.result';
    }
}
