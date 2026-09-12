<?php

namespace App\Http\Controllers;

use App\Actions\Taxonomies\GetAreasAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AreaController extends Controller
{
    public function index(Request $request, GetAreasAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->handle($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('areas', 'name')->where('user_id', $request->user()->getKey()),
            ],
        ]);

        $entry = $request->user()->areas()->create($data);

        return response()->json(['data' => [
            'uuid' => $entry->uuid,
            'name' => $entry->name,
        ]], 201);
    }
}
