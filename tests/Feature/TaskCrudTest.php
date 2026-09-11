<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    public static function endpoints(): array
    {
        return [
            'index' => ['GET', '/tasks'],
            'store' => ['POST', '/tasks'],
            'show' => ['GET', '/tasks/{task}'],
            'put' => ['PUT', '/tasks/{task}'],
            'patch' => ['PATCH', '/tasks/{task}'],
            'delete' => ['DELETE', '/tasks/{task}'],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_guest_cannot_access_tasks(string $method, string $uri): void
    {
        $task = Task::factory()->create();

        $this->json($method, str_replace('{task}', $task->uuid, $uri))
            ->assertUnauthorized();
    }

    #[DataProvider('endpoints')]
    public function test_unverified_user_cannot_access_tasks(string $method, string $uri): void
    {
        $user = User::factory()->unverified()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)
            ->json($method, str_replace('{task}', $task->uuid, $uri))
            ->assertForbidden();
    }

    public function test_index_paginates_only_owned_tasks_newest_first(): void
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(21)->for($user)->create();
        $foreign = Task::factory()->create();

        $this->actingAs($user)->getJson('/tasks')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 21)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('data.0.uuid', $tasks->last()->uuid)
            ->assertJsonMissing(['uuid' => $foreign->uuid]);

        $this->getJson('/tasks?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $tasks->first()->uuid);
    }

    public function test_show_returns_resource_by_uuid(): void
    {
        $task = Task::factory()->withTaxonomy()->create();

        $this->actingAs($task->user)->getJson('/tasks/' . $task->uuid)
            ->assertOk()
            ->assertJsonPath('data.uuid', $task->uuid)
            ->assertJsonPath('data.task_type_uuid', $task->taskType->uuid)
            ->assertJsonPath('data.area_uuid', $task->area->uuid)
            ->assertJsonPath('data.category_uuid', $task->category->uuid)
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.user_id');
    }

    public static function itemMethods(): array
    {
        return [['GET'], ['PUT'], ['PATCH'], ['DELETE']];
    }

    #[DataProvider('itemMethods')]
    public function test_foreign_task_is_forbidden_and_unchanged(string $method): void
    {
        $task = Task::factory()->create(['title' => 'Original']);
        $original = $task->fresh()->getAttributes();

        $this->actingAs(User::factory()->create())
            ->json($method, '/tasks/' . $task->uuid, ['title' => 'Changed'])
            ->assertForbidden();

        $this->assertEquals($original, $task->fresh()->getAttributes());
    }

    #[DataProvider('itemMethods')]
    public function test_missing_task_returns_not_found(string $method): void
    {
        $this->actingAs(User::factory()->create())
            ->json($method, '/tasks/' . Str::uuid(), ['title' => 'Changed'])
            ->assertNotFound();
    }

    public function test_patch_preserves_omitted_fields(): void
    {
        $task = Task::factory()->withTaxonomy()->create(['description' => 'Keep me']);
        $original = $task->fresh()->getAttributes();

        $this->actingAs($task->user)->patchJson('/tasks/' . $task->uuid, ['title' => 'Changed'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Changed')
            ->assertJsonPath('data.description', 'Keep me');

        $updated = $task->fresh()->getAttributes();
        unset($original['title'], $original['updated_at'], $updated['title'], $updated['updated_at']);
        $this->assertEquals($original, $updated);
    }

    public function test_patch_can_clear_nullable_fields(): void
    {
        $task = Task::factory()->withTaxonomy()->create(['description' => 'Clear me']);

        $this->actingAs($task->user)->patchJson('/tasks/' . $task->uuid, [
            'description' => null,
            'area_uuid' => null,
            'category_uuid' => null,
        ])->assertOk()
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.area_uuid', null)
            ->assertJsonPath('data.category_uuid', null);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id, 'description' => null, 'area_id' => null, 'category_id' => null,
        ]);
    }

    public function test_put_updates_fields_and_owned_relationships(): void
    {
        $task = Task::factory()->create();
        $user = $task->user;
        $type = TaskType::factory()->for($user)->create();
        $area = Area::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->putJson('/tasks/' . $task->uuid, [
            'title' => 'Updated', 'description' => 'Details',
            'estimated_time_minutes' => 45, 'difficulty' => 'hard', 'status' => 'completed',
            'task_type_uuid' => $type->uuid, 'area_uuid' => $area->uuid, 'category_uuid' => $category->uuid,
        ])->assertOk()->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id, 'user_id' => $user->id,
            'title' => 'Updated', 'description' => 'Details',
            'estimated_time_minutes' => 45, 'difficulty' => 'hard', 'status' => 'completed',
            'task_type_id' => $type->id, 'area_id' => $area->id, 'category_id' => $category->id,
        ]);
    }

    public function test_system_type_can_be_used_for_creation_and_update(): void
    {
        $user = User::factory()->create();
        $type = TaskType::factory()->system()->create();

        $this->actingAs($user)->postJson('/tasks', [
            'title' => 'System task', 'estimated_time_minutes' => 10,
            'difficulty' => 'easy', 'task_type_uuid' => $type->uuid,
        ])->assertCreated()->assertJsonPath('data.task_type_uuid', $type->uuid);

        $task = Task::factory()->for($user)->create();
        $this->patchJson('/tasks/' . $task->uuid, ['task_type_uuid' => $type->uuid])
            ->assertOk()->assertJsonPath('data.task_type_uuid', $type->uuid);
        $this->assertSame($type->id, $task->fresh()->task_type_id);
    }

    public function test_foreign_relationships_are_rejected_without_writes(): void
    {
        $task = Task::factory()->create();
        $original = $task->fresh()->getAttributes();
        $data = [
            'title' => 'Rejected', 'estimated_time_minutes' => 10, 'difficulty' => 'easy',
            'task_type_uuid' => TaskType::factory()->create()->uuid,
            'area_uuid' => Area::factory()->create()->uuid,
            'category_uuid' => Category::factory()->create()->uuid,
        ];

        $this->actingAs($task->user)->postJson('/tasks', $data)->assertUnprocessable()
            ->assertJsonValidationErrors(['task_type_uuid', 'area_uuid', 'category_uuid']);
        $this->patchJson('/tasks/' . $task->uuid, $data)->assertUnprocessable()
            ->assertJsonValidationErrors(['task_type_uuid', 'area_uuid', 'category_uuid']);

        $this->assertDatabaseCount('tasks', 1);
        $this->assertEquals($original, $task->fresh()->getAttributes());
    }

    public static function invalidUpdates(): array
    {
        return [
            'empty title' => ['title', ''],
            'long title' => ['title', str_repeat('a', 256)],
            'long description' => ['description', str_repeat('a', 5001)],
            'zero minutes' => ['estimated_time_minutes', 0],
            'fractional minutes' => ['estimated_time_minutes', 1.5],
            'null minutes' => ['estimated_time_minutes', null],
            'invalid difficulty' => ['difficulty', 'impossible'],
            'invalid status' => ['status', 'unknown'],
            'null type' => ['task_type_uuid', null],
            'malformed area' => ['area_uuid', 'bad'],
            'malformed category' => ['category_uuid', 'bad'],
            'missing type' => ['task_type_uuid', '11111111-1111-4111-8111-111111111111'],
            'missing area' => ['area_uuid', '11111111-1111-4111-8111-111111111111'],
            'missing category' => ['category_uuid', '11111111-1111-4111-8111-111111111111'],
        ];
    }

    #[DataProvider('invalidUpdates')]
    public function test_invalid_update_leaves_task_unchanged(string $field, mixed $value): void
    {
        $task = Task::factory()->create();
        $original = $task->fresh()->getAttributes();

        $this->actingAs($task->user)->patchJson('/tasks/' . $task->uuid, [$field => $value])
            ->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertEquals($original, $task->fresh()->getAttributes());
    }

    public function test_owner_can_delete_task_without_deleting_taxonomy(): void
    {
        $task = Task::factory()->withTaxonomy()->create();

        $this->actingAs($task->user)->deleteJson('/tasks/' . $task->uuid)->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('areas', ['id' => $task->area_id]);
        $this->assertDatabaseHas('categories', ['id' => $task->category_id]);
        $this->assertDatabaseHas('task_types', ['id' => $task->task_type_id]);
    }
}
