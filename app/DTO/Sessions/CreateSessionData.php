<?php

namespace App\DTO\Sessions;

use App\Enums\TaskDifficulty;
use App\Enums\TaskSelectionMode;

final readonly class CreateSessionData
{
    public function __construct(
        public int $availableTimeMinutes,
        public ?TaskDifficulty $difficulty,
        public ?string $areaUuid,
        public ?string $categoryUuid,
        public ?string $taskTypeUuid,
        public TaskSelectionMode $selectionStrategy = TaskSelectionMode::Shortest,
    ) {}
}
