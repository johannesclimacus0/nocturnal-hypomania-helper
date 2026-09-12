<?php

namespace App\Http\Resources;

use App\Models\NightSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NightSession */
class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'tasks_count' => $this->whenCounted('nightSessionTasks'),
            'tasks' => SessionTaskResource::collection($this->whenLoaded('nightSessionTasks')),
            'available_time_minutes' => $this->available_time_minutes,
            'difficulty' => $this->difficulty?->value,
            'area_uuid' => $this->area?->uuid,
            'area_name' => $this->area?->name,
            'category_uuid' => $this->category?->uuid,
            'category_name' => $this->category?->name,
            'task_type_uuid' => $this->taskType?->uuid,
            'task_type_name' => $this->taskType?->name,
        ];
    }
}
