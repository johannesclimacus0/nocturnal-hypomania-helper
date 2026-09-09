<?php

namespace App\DTO\Tasks;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;

final readonly class UpdateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $estimatedTimeMinutes,
        public TaskDifficulty $difficulty,
        public TaskStatus $status,
        public string $taskTypeUuid,
        public ?string $areaUuid = null,
        public ?string $categoryUuid = null,
    ) {}
}
