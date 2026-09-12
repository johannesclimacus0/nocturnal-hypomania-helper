<?php

namespace App\Http\Requests\Sessions;

use App\Enums\TaskDifficulty;
use App\Enums\TaskSelectionMode;
use App\Models\NightSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', NightSession::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'available_time_minutes' => 'required|integer|min:1',
            'difficulty' => ['nullable', Rule::enum(TaskDifficulty::class)],
            'area_uuid' => 'nullable|uuid',
            'category_uuid' => 'nullable|uuid',
            'task_type_uuid' => 'nullable|uuid',
            'selection_strategy' => ['sometimes', 'required', Rule::enum(TaskSelectionMode::class)],
        ];
    }
}
