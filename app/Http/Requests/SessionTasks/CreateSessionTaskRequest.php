<?php

namespace App\Http\Requests\SessionTasks;

use App\Models\NightSession;
use App\Models\NightSessionTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateSessionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof NightSession
            && ($this->user()?->can('create', [NightSessionTask::class, $session]) ?? false);
    }

    public function rules(): array
    {
        return [
            'task_uuid' => ['bail', 'required', 'uuid',
                Rule::exists('tasks', 'uuid')->where('user_id', $this->user()?->getKey()),
            ],
            'position' => 'nullable|integer|min:0|max:2147483647',
        ];
    }
}
