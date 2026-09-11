<?php

namespace App\Http\Requests\Sessions;

use App\Models\NightSession;
use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', NightSession::class) ?? false;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
