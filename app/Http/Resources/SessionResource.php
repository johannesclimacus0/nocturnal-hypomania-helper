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
        ];
    }
}
