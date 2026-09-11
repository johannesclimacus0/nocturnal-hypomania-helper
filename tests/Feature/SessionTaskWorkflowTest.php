<?php

namespace Tests\Feature;

use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\FinishSessionAction;
use App\Actions\SessionTasks\CompleteSessionTaskAction;
use App\Actions\SessionTasks\CreateSessionTaskAction;
use App\Actions\SessionTasks\SkipSessionTaskAction;
use App\DTO\SessionTasks\CreateSessionTaskData;
use App\DTO\SessionTasks\UpdateSessionTaskData;
use App\Enums\NightSessionTaskStatus;
use App\Http\Requests\Sessions\CreateSessionRequest;
use App\Http\Requests\SessionTasks\CreateSessionTaskRequest;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SessionTaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_saves_relationships_and_rejects_duplicates(): void
    {
        $session = NightSession::factory()->create();
        $task = Task::factory()->for($session->user)->create();
        $action = app(CreateSessionTaskAction::class);
        $data = new CreateSessionTaskData($task->uuid, 0);
        $entry = $action->handle($session->user, $session, $data)->refresh();

        $this->assertSame($task->id, $entry->task_id);
        $this->assertSame($session->id, $entry->night_session_id);
        $this->assertSame(NightSessionTaskStatus::Selected, $entry->status);
        $this->assertNotNull($entry->selected_at);
        $this->assertNotNull($entry->uuid);
        $this->assertSame(0, $entry->position);

        try {
            $action->handle($session->user, $session, $data);
            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('task_uuid', $exception->errors());
        }
        $this->assertSame(1, $session->nightSessionTasks()->count());
    }

    public function test_creation_rejects_a_foreign_session(): void
    {
        $session = NightSession::factory()->create();
        $task = Task::factory()->create();
        $this->expectException(AuthorizationException::class);
        app(CreateSessionTaskAction::class)->handle($task->user, $session, new CreateSessionTaskData($task->uuid));
    }

    public function test_creation_rejects_a_foreign_source_task(): void
    {
        $session = NightSession::factory()->create();
        $task = Task::factory()->create();
        $this->expectException(ModelNotFoundException::class);
        app(CreateSessionTaskAction::class)->handle($session->user, $session, new CreateSessionTaskData($task->uuid));
    }

    public function test_completion_and_skip_select_by_uuid_even_with_identical_positions(): void
    {
        $entry = NightSessionTask::factory()->skipped()->create(['position' => null]);
        $other = NightSessionTask::factory()->for($entry->nightSession)->create(['position' => null]);
        $actor = $entry->nightSession->user;
        $data = new UpdateSessionTaskData($entry->uuid);

        $completed = app(CompleteSessionTaskAction::class)->handle($actor, $entry->nightSession, $data);
        $this->assertTrue($completed->is($entry));
        $this->assertSame(NightSessionTaskStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertNull($completed->skipped_at);

        $skipped = app(SkipSessionTaskAction::class)->handle($actor, $entry->nightSession, $data);
        $this->assertSame(NightSessionTaskStatus::Skipped, $skipped->status);
        $this->assertNotNull($skipped->skipped_at);
        $this->assertNull($skipped->completed_at);
        $this->assertSame(NightSessionTaskStatus::Selected, $other->refresh()->status);
    }

    public function test_updates_reject_foreign_users_and_mismatched_sessions(): void
    {
        $entry = NightSessionTask::factory()->create();
        $foreignSession = NightSession::factory()->create();
        foreach ([CompleteSessionTaskAction::class, SkipSessionTaskAction::class] as $action) {
            try {
                app($action)->handle($foreignSession->user, $entry->nightSession, new UpdateSessionTaskData($entry->uuid));
                $this->fail();
            } catch (AuthorizationException) {
                $this->assertSame(NightSessionTaskStatus::Selected, $entry->refresh()->status);
            }
            try {
                app($action)->handle($foreignSession->user, $foreignSession, new UpdateSessionTaskData($entry->uuid));
                $this->fail();
            } catch (ModelNotFoundException) {
                $this->assertSame(NightSessionTaskStatus::Selected, $entry->refresh()->status);
            }
        }
    }

    public function test_policies_use_session_ownership_and_session_can_be_finished(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $session = app(CreateSessionAction::class)->handle($owner);
        $entry = NightSessionTask::factory()->for($session)->create();
        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $entry));
            $this->assertFalse(Gate::forUser($other)->allows($ability, $entry));
        }
        try {
            app(FinishSessionAction::class)->handle($other, $session);
            $this->fail();
        } catch (AuthorizationException) {
            $this->assertNull($session->refresh()->ended_at);
        }
        $this->assertNotNull(app(FinishSessionAction::class)->handle($owner, $session)->refresh()->ended_at);
    }

    public function test_requests_authorize_session_context_and_validate_input(): void
    {
        Route::post('/_test/sessions/{session}/tasks', function (CreateSessionTaskRequest $request, NightSession $session) {
            return response()->json($request->validated());
        })->middleware(SubstituteBindings::class);
        Route::post('/_test/sessions', fn (CreateSessionRequest $request) => response()->noContent());

        $session = NightSession::factory()->create();
        $task = Task::factory()->for($session->user)->create();
        $foreignTask = Task::factory()->create();
        $url = '/_test/sessions/' . $session->uuid . '/tasks';

        $this->postJson('/_test/sessions')->assertForbidden();
        $this->actingAs($session->user)->postJson('/_test/sessions')->assertNoContent();
        $this->postJson($url, ['task_uuid' => $task->uuid, 'position' => 0])->assertOk();
        $this->postJson($url, ['task_uuid' => $task->uuid, 'position' => null])->assertOk();
        $this->postJson($url, ['task_uuid' => $foreignTask->uuid])->assertUnprocessable()->assertJsonValidationErrors('task_uuid');
        $this->postJson($url, ['task_uuid' => 'bad', 'position' => -1])->assertUnprocessable()->assertJsonValidationErrors(['task_uuid', 'position']);
        $this->postJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('task_uuid');
        $this->actingAs($foreignTask->user)->postJson($url, ['task_uuid' => $foreignTask->uuid])->assertForbidden();
    }
}
