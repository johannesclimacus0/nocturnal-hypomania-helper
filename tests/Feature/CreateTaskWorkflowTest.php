<?php

namespace Tests\Feature;

use App\Actions\Tasks\CreateTaskAction;
use App\DTO\Tasks\CreateTaskData;
use App\Enums\SystemTaskType;
use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Http\Requests\CreateTaskRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_accepts_valid_data_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $taskType = TaskType::factory()->for($user)->create();
        $area = Area::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $validator = $this->validatorFor($user, [
            'title' => 'Do something',
            'description' => null,
            'estimated_time_minutes' => 30,
            'difficulty' => TaskDifficulty::Normal->value,
            'task_type_uuid' => $taskType->uuid,
            'area_uuid' => $area->uuid,
            'category_uuid' => $category->uuid,
        ]);

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_controller_creates_task_from_validated_input(): void
    {
        $user = User::factory()->create();
        $taskType = TaskType::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/tasks', [
            'title' => 'Do something',
            'estimated_time_minutes' => '30',
            'difficulty' => 'normal',
            'task_type_uuid' => $taskType->uuid,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Do something')
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.estimated_time_minutes', 30)
            ->assertJsonPath('data.difficulty', 'normal')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.task_type_uuid', $taskType->uuid)
            ->assertJsonPath('data.area_uuid', null)
            ->assertJsonPath('data.category_uuid', null);

        $this->assertDatabaseHas('tasks', [
            'uuid' => $response->json('data.uuid'),
            'user_id' => $user->id,
            'task_type_id' => $taskType->id,
            'title' => 'Do something',
            'estimated_time_minutes' => 30,
            'status' => 'active',
        ]);
    }

    public function test_request_rejects_invalid_values_with_custom_messages(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreignTaskType = TaskType::factory()->for($otherUser)->create();
        $foreignArea = Area::factory()->for($otherUser)->create();
        $foreignCategory = Category::factory()->for($otherUser)->create();

        $validator = $this->validatorFor($user, [
            'title' => '',
            'description' => ['not a string'],
            'estimated_time_minutes' => 0,
            'difficulty' => 'impossible',
            'task_type_uuid' => $foreignTaskType->uuid,
            'area_uuid' => $foreignArea->uuid,
            'category_uuid' => $foreignCategory->uuid,
        ]);

        $this->assertFalse($validator->passes());
        $this->assertSame('Укажите название задачи', $validator->errors()->first('title'));
        $this->assertSame('Описание задачи должно быть строкой', $validator->errors()->first('description'));
        $this->assertSame(
            'Продолжительность задачи должна быть не меньше 1 минуты',
            $validator->errors()->first('estimated_time_minutes'),
        );
        $this->assertSame('Выберите допустимую сложность задачи', $validator->errors()->first('difficulty'));
        $this->assertSame('Выбранный тип задачи недоступен', $validator->errors()->first('task_type_uuid'));
        $this->assertSame('Выбранная область недоступна', $validator->errors()->first('area_uuid'));
        $this->assertSame('Выбранная категория недоступна', $validator->errors()->first('category_uuid'));
    }

    public function test_action_creates_a_task_with_user_owned_taxonomy(): void
    {
        $user = User::factory()->create();
        $taskType = TaskType::factory()->for($user)->create();
        $area = Area::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $task = app(CreateTaskAction::class)->handle($user, new CreateTaskData(
            title: 'Do something',
            description: null,
            estimatedTimeMinutes: 30,
            difficulty: TaskDifficulty::Normal,
            taskTypeUuid: $taskType->uuid,
            areaUuid: $area->uuid,
            categoryUuid: $category->uuid,
        ));

        $this->assertSame($user->getKey(), $task->user_id);
        $this->assertSame($taskType->getKey(), $task->task_type_id);
        $this->assertSame($area->getKey(), $task->area_id);
        $this->assertSame($category->getKey(), $task->category_id);
        $this->assertSame(TaskStatus::Active, $task->status);
        $this->assertTrue($task->relationLoaded('taskType'));
        $this->assertTrue($task->relationLoaded('area'));
        $this->assertTrue($task->relationLoaded('category'));
    }

    public function test_action_allows_a_system_task_type(): void
    {
        $user = User::factory()->create();
        $systemTaskType = TaskType::query()->where('is_system', true)->firstOrFail();

        $task = app(CreateTaskAction::class)->handle($user, new CreateTaskData(
            title: 'Do something',
            description: null,
            estimatedTimeMinutes: 20,
            difficulty: TaskDifficulty::Easy,
            taskTypeUuid: $systemTaskType->uuid,
        ));

        $this->assertSame($systemTaskType->getKey(), $task->task_type_id);
        $this->assertCount(count(SystemTaskType::cases()), TaskType::query()->where('is_system', true)->get());
    }

    public function test_action_rejects_another_users_task_type(): void
    {
        $user = User::factory()->create();
        $foreignTaskType = TaskType::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(CreateTaskAction::class)->handle($user, new CreateTaskData(
            title: 'Another users task',
            description: null,
            estimatedTimeMinutes: 10,
            difficulty: TaskDifficulty::Easy,
            taskTypeUuid: $foreignTaskType->uuid,
        ));
    }

    private function validatorFor(User $user, array $data): \Illuminate\Contracts\Validation\Validator
    {
        $request = CreateTaskRequest::create('/tasks', 'POST', $data);
        $request->setUserResolver(fn (): User => $user);

        return Validator::make($data, $request->rules(), $request->messages());
    }
}
