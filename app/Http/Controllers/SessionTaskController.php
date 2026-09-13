<?php

namespace App\Http\Controllers;

use App\Actions\SessionTasks\CompleteSessionTaskAction;
use App\Actions\SessionTasks\CreateSessionTaskAction;
use App\Actions\SessionTasks\GetSessionTaskAction;
use App\Actions\SessionTasks\GetSessionTasksAction;
use App\Actions\SessionTasks\SkipSessionTaskAction;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\DTO\SessionTasks\UpdateSessionTaskData;
use App\Http\Requests\SessionTasks\CreateSessionTaskRequest;
use App\Http\Resources\SessionTaskResource;
use App\Models\NightSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SessionTaskController extends Controller
{
    public function index(Request $request, NightSession $session, GetSessionTasksAction $action): AnonymousResourceCollection
    {
        return SessionTaskResource::collection($action->handle($request->user(), $session));
    }

    public function store(CreateSessionTaskRequest $request, NightSession $session, CreateSessionTaskAction $action, GetSessionTaskAction $getTask): JsonResponse
    {
        $data = $request->validated();
        $task = $action->handle($request->user(), $session, new CreateSessionTaskData(
            taskUuid: $data['task_uuid'],
            position: isset($data['position']) ? (int) $data['position'] : null,
        ));

        return new SessionTaskResource($getTask->handle($request->user(), $task))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function complete(Request $request, NightSession $session, string $sessionTaskUuid, CompleteSessionTaskAction $action, GetSessionTaskAction $getTask): SessionTaskResource
    {
        $task = $action->handle($request->user(), $session, new UpdateSessionTaskData($sessionTaskUuid));

        return new SessionTaskResource($getTask->handle($request->user(), $task));
    }

    public function skip(Request $request, NightSession $session, string $sessionTaskUuid, SkipSessionTaskAction $action, GetSessionTaskAction $getTask): SessionTaskResource
    {
        $result = $action->handle($request->user(), $session, new UpdateSessionTaskData($sessionTaskUuid));

        return new SessionTaskResource($getTask->handle($request->user(), $result->skipped))
            ->additional([
                'replacement' => $result->replacement === null ? null : new SessionTaskResource(
                    $getTask->handle($request->user(), $result->replacement),
                ),
            ]);
    }
}
