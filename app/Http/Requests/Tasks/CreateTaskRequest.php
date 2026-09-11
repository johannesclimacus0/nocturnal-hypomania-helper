<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskDifficulty;
use App\Models\Task;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'title' => 'bail|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'estimated_time_minutes' => 'bail|required|integer|min:1',
            'difficulty' => ['bail', 'required', Rule::enum(TaskDifficulty::class)],
            'task_type_uuid' => ['bail', 'required', 'uuid',
                Rule::exists('task_types', 'uuid')
                    ->where(function (Builder $query) use ($userId): void {
                        $query->where(function (Builder $query) use ($userId): void {
                            $query->where('user_id', $userId)
                                ->orWhere(function (Builder $query): void {
                                    $query->whereNull('user_id')
                                        ->where('is_system', true);
                                });
                        });
                    }),
            ],
            'area_uuid' => ['bail', 'nullable', 'uuid',
                Rule::exists('areas', 'uuid')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $userId)),
            ],
            'category_uuid' => ['bail', 'nullable', 'uuid',
                Rule::exists('categories', 'uuid')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $userId)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Укажите название задачи',
            'title.string' => 'Название задачи должно быть строкой',
            'title.max' => 'Название задачи не должно превышать 255 символов',
            'description.string' => 'Описание задачи должно быть строкой',
            'description.max' => 'Описание задачи не должно превышать 5000 символов',
            'estimated_time_minutes.required' => 'Укажите ожидаемую продолжительность задачи',
            'estimated_time_minutes.integer' => 'Продолжительность задачи должна быть целым числом минут',
            'estimated_time_minutes.min' => 'Продолжительность задачи должна быть не меньше 1 минуты',
            'difficulty.required' => 'Укажите сложность задачи',
            'difficulty.enum' => 'Выберите допустимую сложность задачи',
            'task_type_uuid.required' => 'Выберите тип задачи',
            'task_type_uuid.exists' => 'Выбранный тип задачи недоступен',
            'area_uuid.exists' => 'Выбранная область недоступна',
            'category_uuid.exists' => 'Выбранная категория недоступна',
        ];
    }
}
