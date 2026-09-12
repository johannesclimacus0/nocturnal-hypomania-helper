<?php

namespace App\DTO\SessionTasks;

use App\Models\NightSessionTask;

final readonly class SkipSessionTaskResult
{
    public function __construct(
        public NightSessionTask $skipped,
        public ?NightSessionTask $replacement,
    ) {}
}
