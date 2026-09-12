<?php

namespace App\Http\Controllers;

use App\Actions\Taxonomies\GetTaskTypesAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskTypeController extends Controller
{
    public function index(Request $request, GetTaskTypesAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->handle($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('task_types', 'slug')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->getKey())->orWhereNull('user_id')),
            ],
        ]);

        $entry = $request->user()->taskTypes()->make($data);
        $entry->is_system = false;
        $entry->save();

        return response()->json(['data' => [
            'uuid' => $entry->uuid,
            'name' => $entry->name,
            'slug' => $entry->slug,
            'is_system' => $entry->is_system,
        ]], 201);
    }
}
