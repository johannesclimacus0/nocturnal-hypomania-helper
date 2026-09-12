<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSessionSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_saves_and_returns_matching_tasks_in_order(): void
    {
        $task = Task::factory()->withTaxonomy()->create([
            'estimated_time_minutes' => 20,
            'difficulty' => 'normal',
        ]);
        $attributes = [
            'user_id' => $task->user_id,
            'area_id' => $task->area_id,
            'category_id' => $task->category_id,
            'task_type_id' => $task->task_type_id,
            'difficulty' => 'normal',
            'estimated_time_minutes' => 30,
        ];
        $second = Task::factory()->create(array_replace($attributes, ['estimated_time_minutes' => 10]));
        Task::factory()->create($attributes);
        foreach ([
            ['estimated_time_minutes' => 31],
            ['difficulty' => 'hard'],
            ['area_id' => null],
            ['category_id' => null],
            ['status' => 'archived'],
            ['user_id' => User::factory()->create()->id],
        ] as $override) {
            Task::factory()->create(array_replace($attributes, $override));
        }

        $response = $this->actingAs($task->user)->postJson('/api/sessions', [
            'available_time_minutes' => 30,
            'difficulty' => 'normal',
            'area_uuid' => $task->area->uuid,
            'category_uuid' => $task->category->uuid,
            'task_type_uuid' => $task->taskType->uuid,
        ])->assertCreated()
            ->assertJsonPath('data.tasks_count', 2)
            ->assertJsonCount(2, 'data.tasks')
            ->assertJsonPath('data.tasks.0.task.uuid', $second->uuid)
            ->assertJsonPath('data.tasks.0.position', 1)
            ->assertJsonPath('data.tasks.0.status', 'selected')
            ->assertJsonPath('data.tasks.1.task.uuid', $task->uuid)
            ->assertJsonPath('data.tasks.1.position', 2);

        $this->getJson('/api/sessions/' . $response->json('data.uuid'))->assertOk()
            ->assertJsonPath('data.tasks_count', 2)
            ->assertJsonPath('data.tasks.0.task.uuid', $second->uuid)
            ->assertJsonPath('data.tasks.1.task.uuid', $task->uuid);
        $this->assertSame(30, collect($response->json('data.tasks'))->sum('task.estimated_time_minutes'));
        $this->assertDatabaseCount('night_session_tasks', 2);
    }

    public function test_creation_without_candidates_returns_an_empty_session(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/api/sessions', [
            'available_time_minutes' => 30,
        ])->assertCreated()
            ->assertJsonPath('data.tasks_count', 0)
            ->assertJsonPath('data.tasks', []);
        $this->assertDatabaseCount('night_sessions', 1);
        $this->assertDatabaseCount('night_session_tasks', 0);
    }
}
