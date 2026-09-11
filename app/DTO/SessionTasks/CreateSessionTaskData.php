<?php

namespace App\DTO\SessionTasks;

final readonly class CreateSessionTaskData
{
    public function __construct(
        public string $taskUuid,
        public ?int $position = null,
    ) {}
}
