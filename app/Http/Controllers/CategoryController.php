<?php

namespace App\Http\Controllers;

use App\Actions\Taxonomies\GetCategoriesAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request, GetCategoriesAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->handle($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('categories', 'name')->where('user_id', $request->user()->getKey()),
            ],
        ]);

        $entry = $request->user()->categories()->create($data);

        return response()->json(['data' => [
            'uuid' => $entry->uuid,
            'name' => $entry->name,
        ]], 201);
    }
}
