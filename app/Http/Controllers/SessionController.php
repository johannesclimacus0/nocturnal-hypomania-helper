<?php

namespace App\Http\Controllers;

use App\Actions\Sessions\FinishSessionAction;
use App\Actions\Sessions\GetSessionAction;
use App\Actions\Sessions\GetSessionsAction;
use App\Actions\Sessions\StartSessionAction;
use App\DTO\Sessions\CreateSessionData;
use App\Enums\TaskDifficulty;
use App\Enums\TaskSelectionMode;
use App\Http\Requests\Sessions\CreateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\NightSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SessionController extends Controller
{
    public function index(Request $request, GetSessionsAction $action): AnonymousResourceCollection|Response
    {
        if (!$request->expectsJson()) {
            return response()->view('app');
        }

        return SessionResource::collection($action->handle($request->user()));
    }

    public function store(CreateSessionRequest $request, StartSessionAction $action, GetSessionAction $getSession): JsonResponse
    {
        $data = $request->validated();
        $sessionData = new CreateSessionData(
            availableTimeMinutes: $data['available_time_minutes'],
            difficulty: isset($data['difficulty']) ? TaskDifficulty::from($data['difficulty']) : null,
            areaUuid: $data['area_uuid'] ?? null,
            categoryUuid: $data['category_uuid'] ?? null,
            taskTypeUuid: $data['task_type_uuid'] ?? null,
            selectionStrategy: TaskSelectionMode::from($data['selection_strategy'] ?? 'shortest'),
        );

        $session = $action->handle($request->user(), $sessionData);

        return new SessionResource($getSession->handle($request->user(), $session))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, NightSession $session, GetSessionAction $action): SessionResource|Response
    {
        if (!$request->expectsJson()) {
            return response()->view('app');
        }

        return new SessionResource($action->handle($request->user(), $session));
    }

    public function finish(Request $request, NightSession $session, FinishSessionAction $action): SessionResource
    {
        return new SessionResource($action->handle($request->user(), $session));
    }
}
