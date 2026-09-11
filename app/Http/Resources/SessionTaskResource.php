<?php

namespace App\Http\Resources;

use App\Models\NightSessionTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NightSessionTask */
class SessionTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'task_uuid' => $this->whenLoaded('task', fn () => $this->task->uuid),
            'status' => $this->status->value,
            'position' => $this->position,
            'selected_at' => $this->selected_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'skipped_at' => $this->skipped_at?->toISOString(),
            'task' => new TaskResource($this->whenLoaded('task')),
        ];
    }
}
