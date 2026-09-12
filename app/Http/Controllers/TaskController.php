<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\DTO\Tasks\CreateTaskData;
use App\DTO\Tasks\UpdateTaskData;
use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Http\Requests\Tasks\CreateTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        if (! $request->expectsJson()) {
            return response()->view('app');
        }

        Gate::authorize('viewAny', Task::class);

        $tasks = $request->user()->tasks()
            ->with(['taskType', 'area', 'category'])
            ->latest('id')
            ->paginate(20);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTaskRequest $request, CreateTaskAction $action): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $task = $action->handle($user, new CreateTaskData(
            title: $data['title'],
            description: $data['description'] ?? null,
            estimatedTimeMinutes: $data['estimated_time_minutes'],
            difficulty: TaskDifficulty::from($data['difficulty']),
            taskTypeUuid: $data['task_type_uuid'],
            areaUuid: $data['area_uuid'] ?? null,
            categoryUuid: $data['category_uuid'] ?? null,
        ));

        return new TaskResource($task)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): TaskResource|Response
    {
        if (! request()->expectsJson()) {
            return response()->view('app');
        }

        Gate::authorize('view', $task);

        return new TaskResource($task->load(['taskType', 'area', 'category']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $task->loadMissing(['taskType', 'area', 'category']);

        $task = $action->handle($user, $task, new UpdateTaskData(
            title: $data['title'] ?? $task->title,
            description: array_key_exists('description', $data) ? $data['description'] : $task->description,
            estimatedTimeMinutes: $data['estimated_time_minutes'] ?? $task->estimated_time_minutes,
            difficulty: isset($data['difficulty']) ? TaskDifficulty::from($data['difficulty']) : $task->difficulty,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : $task->status,
            taskTypeUuid: $data['task_type_uuid'] ?? $task->taskType->uuid,
            areaUuid: array_key_exists('area_uuid', $data) ? $data['area_uuid'] : $task->area?->uuid,
            categoryUuid: array_key_exists('category_uuid', $data) ? $data['category_uuid'] : $task->category?->uuid,
        ));

        return new TaskResource($task)->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task, DeleteTaskAction $action): Response
    {
        Gate::authorize('delete', $task);
        $action->handle($request->user(), $task);

        return response()->noContent();
    }
}
