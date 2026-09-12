<?php

namespace App\Http\Controllers;

use App\Actions\SessionTasks\CompleteSessionTaskAction;
use App\Actions\SessionTasks\CreateSessionTaskAction;
use App\Actions\SessionTasks\SkipSessionTaskAction;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\DTO\SessionTasks\UpdateSessionTaskData;
use App\Http\Requests\SessionTasks\CreateSessionTaskRequest;
use App\Http\Resources\SessionTaskResource;
use App\Models\NightSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SessionTaskController extends Controller
{
    public function index(NightSession $session): AnonymousResourceCollection
    {
        Gate::authorize('view', $session);

        return SessionTaskResource::collection($session->nightSessionTasks()
            ->with(['task.taskType', 'task.area', 'task.category'])
            ->orderBy('position')->orderBy('id')->paginate(20));
    }

    public function store(CreateSessionTaskRequest $request, NightSession $session, CreateSessionTaskAction $action): JsonResponse
    {
        $data = $request->validated();
        $task = $action->handle($request->user(), $session, new CreateSessionTaskData(
            taskUuid: $data['task_uuid'],
            position: isset($data['position']) ? (int) $data['position'] : null,
        ));

        return new SessionTaskResource($task->load(['task.taskType', 'task.area', 'task.category']))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function complete(Request $request, NightSession $session, string $sessionTaskUuid, CompleteSessionTaskAction $action): SessionTaskResource
    {
        $task = $action->handle($request->user(), $session, new UpdateSessionTaskData($sessionTaskUuid));

        return new SessionTaskResource($task->load(['task.taskType', 'task.area', 'task.category']));
    }

    public function skip(Request $request, NightSession $session, string $sessionTaskUuid, SkipSessionTaskAction $action): SessionTaskResource
    {
        $result = $action->handle($request->user(), $session, new UpdateSessionTaskData($sessionTaskUuid));

        return new SessionTaskResource($result->skipped->load(['task.taskType', 'task.area', 'task.category']))
            ->additional([
                'replacement' => $result->replacement === null ? null : new SessionTaskResource(
                    $result->replacement->load(['task.taskType', 'task.area', 'task.category']),
                )
            ]);
    }
}
