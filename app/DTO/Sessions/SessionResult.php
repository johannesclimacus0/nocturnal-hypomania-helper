<?php

namespace App\DTO\Sessions;

final readonly class SessionResult
{
    public function __construct(
        public readonly int $total,
        public readonly int $completed,
        public readonly int $skipped,
        public readonly int $unfinished,
        public readonly int $durationMinutes,
    ) {}

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'completed' => $this->completed,
            'skipped' => $this->skipped,
            'unfinished' => $this->unfinished,
            'duration_minutes' => $this->durationMinutes,
        ];
    }
}
