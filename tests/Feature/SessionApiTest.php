<?php

namespace Tests\Feature;

use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_list_view_and_finish_sessions(): void
    {
        $owner = User::factory()->create();
        NightSession::factory()->create();
        $this->actingAs($owner);
        $created = $this->postJson('/sessions')->assertCreated()
            ->assertJsonPath('data.tasks_count', 0)->assertJsonPath('data.ended_at', null);
        $uuid = $created->json('data.uuid');
        $session = NightSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame($owner->id, $session->user_id);
        $late = NightSessionTask::factory()->for($session)->create(['position' => 2]);
        $early = NightSessionTask::factory()->for($session)->create(['position' => 0]);
        $this->getJson('/sessions')->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.uuid', $uuid)->assertJsonPath('data.0.tasks_count', 2)
            ->assertJsonMissingPath('data.0.tasks');
        $this->getJson('/sessions/' . $uuid)->assertOk()
            ->assertJsonPath('data.tasks.0.uuid', $early->uuid)
            ->assertJsonPath('data.tasks.1.uuid', $late->uuid)
            ->assertJsonPath('data.tasks.0.task.uuid', $early->task->uuid)
            ->assertJsonMissingPath('data.user_id')->assertJsonMissingPath('data.tasks.0.task_id');
        $this->patchJson('/sessions/' . $uuid . '/finish')->assertOk();
        $this->assertNotNull($session->refresh()->ended_at);
    }

    public function test_owner_can_add_list_complete_and_skip_session_tasks(): void
    {
        $session = NightSession::factory()->create();
        $task = Task::factory()->for($session->user)->withTaxonomy()->create();
        $url = '/sessions/' . $session->uuid . '/tasks';
        $this->actingAs($session->user);
        $created = $this->postJson($url, ['task_uuid' => $task->uuid, 'position' => '0'])
            ->assertCreated()->assertJsonPath('data.task_uuid', $task->uuid)
            ->assertJsonPath('data.position', 0)->assertJsonPath('data.status', 'selected')
            ->assertJsonPath('data.task.area_uuid', $task->area->uuid);
        $uuid = $created->json('data.uuid');
        $this->assertNotSame($task->uuid, $uuid);
        $this->getJson($url)->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.uuid', $uuid);
        $this->postJson($url, ['task_uuid' => $task->uuid])->assertUnprocessable()->assertJsonValidationErrors('task_uuid');
        $this->patchJson($url . '/' . $uuid . '/complete')->assertOk()
            ->assertJsonPath('data.status', 'completed')->assertJsonPath('data.skipped_at', null);
        $this->assertNotNull(NightSessionTask::where('uuid', $uuid)->firstOrFail()->completed_at);
        $this->patchJson($url . '/' . $uuid . '/skip')->assertOk()
            ->assertJsonPath('data.status', 'skipped')->assertJsonPath('data.completed_at', null);
        $this->assertNotNull(NightSessionTask::where('uuid', $uuid)->firstOrFail()->skipped_at);
    }

    public function test_foreign_users_cannot_read_or_modify_sessions(): void
    {
        $entry = NightSessionTask::factory()->create();
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();
        $url = '/sessions/' . $entry->nightSession->uuid;
        $this->actingAs($user);
        $this->getJson($url)->assertForbidden();
        $this->getJson($url . '/tasks')->assertForbidden();
        $this->patchJson($url . '/finish')->assertForbidden();
        $this->postJson($url . '/tasks', ['task_uuid' => $task->uuid])->assertForbidden();
        foreach (['complete', 'skip'] as $operation) {
            $this->patchJson($url . '/tasks/' . $entry->uuid . '/' . $operation)->assertForbidden();
        }
        $this->assertNull($entry->nightSession->refresh()->ended_at);
        $this->assertSame(NightSessionTaskStatus::Selected, $entry->refresh()->status);
        $this->assertSame(1, $entry->nightSession->nightSessionTasks()->count());
    }

    public function test_mismatched_and_missing_uuids_are_not_found(): void
    {
        $entry = NightSessionTask::factory()->create();
        $session = NightSession::factory()->for($entry->nightSession->user)->create();
        $this->actingAs($session->user);
        foreach (['complete', 'skip'] as $operation) {
            $this->patchJson('/sessions/' . $session->uuid . '/tasks/' . $entry->uuid . '/' . $operation)->assertNotFound();
            $this->patchJson('/sessions/' . $session->uuid . '/tasks/not-a-uuid/' . $operation)->assertNotFound();
        }
        $this->getJson('/sessions/00000000-0000-4000-8000-000000000000')->assertNotFound();
        $this->assertSame(NightSessionTaskStatus::Selected, $entry->refresh()->status);
    }

    public function test_invalid_and_foreign_source_tasks_are_rejected(): void
    {
        $session = NightSession::factory()->create();
        $foreign = Task::factory()->create();
        $url = '/sessions/' . $session->uuid . '/tasks';
        $this->actingAs($session->user);
        $this->postJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('task_uuid');
        $this->postJson($url, ['task_uuid' => $foreign->uuid])->assertUnprocessable()->assertJsonValidationErrors('task_uuid');
        $this->postJson($url, ['task_uuid' => 'invalid', 'position' => -1])->assertUnprocessable()
            ->assertJsonValidationErrors(['task_uuid', 'position']);
        $this->assertSame(0, $session->nightSessionTasks()->count());
    }

    public function test_all_endpoints_require_authentication_and_verified_email(): void
    {
        $entry = NightSessionTask::factory()->create();
        $url = '/sessions/' . $entry->nightSession->uuid;
        $endpoints = [
            ['GET', '/sessions'], ['POST', '/sessions'], ['GET', $url], ['PATCH', $url . '/finish'],
            ['GET', $url . '/tasks'], ['POST', $url . '/tasks'],
            ['PATCH', $url . '/tasks/' . $entry->uuid . '/complete'], ['PATCH', $url . '/tasks/' . $entry->uuid . '/skip'],
        ];
        foreach ($endpoints as [$method, $path]) {
            $this->json($method, $path)->assertUnauthorized();
        }
        $this->actingAs(User::factory()->unverified()->create());
        foreach ($endpoints as [$method, $path]) {
            $this->json($method, $path)->assertForbidden();
        }
    }
}
