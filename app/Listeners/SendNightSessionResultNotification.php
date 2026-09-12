<?php

namespace App\Listeners;

use App\Actions\Sessions\BuildSessionResultAction;
use App\Events\NightSessionEnded;
use App\Notifications\NightSessionResultNotification;

class SendNightSessionResultNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(private BuildSessionResultAction $buildResult)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NightSessionEnded $event): void
    {
        $session = $event->session->fresh();
        $result = $this->buildResult->handle($session);

        $session->user->notify(
            new NightSessionResultNotification(
                sessionUuid: $session->uuid,
                result: $result,
            )
        );
    }
}
