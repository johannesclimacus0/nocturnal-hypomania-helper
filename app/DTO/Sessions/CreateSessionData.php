<?php

namespace App\DTO\Sessions;

use App\Enums\TaskDifficulty;

final readonly class CreateSessionData
{
    public function __construct(
        public int $availableTimeMinutes,
        public ?TaskDifficulty $difficulty,
        public ?string $areaUuid,
        public ?string $categoryUuid,
        public ?string $taskTypeUuid,
    ) {}
}
