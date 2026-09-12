<?php

namespace App\Http\Controllers;

use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\FinishSessionAction;
use App\Http\Requests\Sessions\CreateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\NightSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        if (! $request->expectsJson()) {
            return response()->view('app');
        }

        Gate::authorize('viewAny', NightSession::class);

        return SessionResource::collection($request->user()->nightSessions()
            ->withCount('nightSessionTasks')
            ->latest('started_at')->latest('id')->paginate(20));
    }

    public function store(CreateSessionRequest $request, CreateSessionAction $action): JsonResponse
    {
        $session = $action->handle($request->user());

        return (new SessionResource($session->loadCount('nightSessionTasks')))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NightSession $session): SessionResource|Response
    {
        if (! request()->expectsJson()) {
            return response()->view('app');
        }

        Gate::authorize('view', $session);

        $session->load(['nightSessionTasks' => fn ($query) => $query
            ->orderBy('position')->orderBy('id')
            ->with(['task.taskType', 'task.area', 'task.category'])]);

        return new SessionResource($session->loadCount('nightSessionTasks'));
    }

    public function finish(Request $request, NightSession $session, FinishSessionAction $action): SessionResource
    {
        return new SessionResource($action->handle($request->user(), $session)
            ->loadCount('nightSessionTasks'));
    }
}
