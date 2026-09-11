<?php

declare(strict_types=1);

namespace App\DTO\SessionTasks;

final readonly class UpdateSessionTaskData
{
    public function __construct(
        public string $sessionTaskUuid
    ) {}
}
