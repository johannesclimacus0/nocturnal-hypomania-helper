<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Task */
final class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'estimated_time_minutes' => $this->estimated_time_minutes,
            'difficulty' => $this->difficulty->value,
            'status' => $this->status->value,
            'task_type_uuid' => $this->taskType->uuid,
            'area_uuid' => $this->area?->uuid,
            'category_uuid' => $this->category?->uuid,
            'last_selected_at' => $this->last_selected_at?->toISOString(),
            'last_skipped_at' => $this->last_skipped_at?->toISOString(),
            'last_completed_at' => $this->last_completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
