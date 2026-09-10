<?php

namespace App\DTO\Tasks;

use App\Enums\TaskDifficulty;

final readonly class CreateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $estimatedTimeMinutes,
        public TaskDifficulty $difficulty,
        public string $taskTypeUuid,
        public ?string $areaUuid = null,
        public ?string $categoryUuid = null,
    ) {}
}
