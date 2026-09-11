<?php

namespace Tests\Unit;

use App\Enums\NightSessionTaskStatus;
use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_relationships_are_configured_correctly(): void
    {
        $user = new User;
        $task = new Task;
        $taskType = new TaskType;
        $area = new Area;
        $category = new Category;
        $nightSession = new NightSession;
        $nightSessionTask = new NightSessionTask;

        $this->assertBelongsTo($task->user(), User::class, 'user_id');
        $this->assertBelongsTo($task->area(), Area::class, 'area_id');
        $this->assertBelongsTo($task->category(), Category::class, 'category_id');
        $this->assertBelongsTo($task->taskType(), TaskType::class, 'task_type_id');
        $this->assertHasMany($task->nightSessionTasks(), NightSessionTask::class, 'task_id');

        $this->assertBelongsTo($taskType->user(), User::class, 'user_id');
        $this->assertHasMany($taskType->tasks(), Task::class, 'task_type_id');

        $this->assertBelongsTo($area->user(), User::class, 'user_id');
        $this->assertHasMany($area->tasks(), Task::class, 'area_id');
        $this->assertBelongsTo($category->user(), User::class, 'user_id');
        $this->assertHasMany($category->tasks(), Task::class, 'category_id');

        $this->assertBelongsTo($nightSession->user(), User::class, 'user_id');
        $this->assertHasMany(
            $nightSession->nightSessionTasks(),
            NightSessionTask::class,
            'night_session_id',
        );
        $this->assertBelongsTo(
            $nightSessionTask->nightSession(),
            NightSession::class,
            'night_session_id',
        );
        $this->assertBelongsTo($nightSessionTask->task(), Task::class, 'task_id');

        $this->assertHasMany($user->tasks(), Task::class, 'user_id');
        $this->assertHasMany($user->areas(), Area::class, 'user_id');
        $this->assertHasMany($user->categories(), Category::class, 'user_id');
        $this->assertHasMany($user->taskTypes(), TaskType::class, 'user_id');
        $this->assertHasMany($user->nightSessions(), NightSession::class, 'user_id');
    }

    public function test_factories_create_a_consistent_default_model_graph(): void
    {
        $sessionTask = NightSessionTask::factory()->create();

        $sessionTask->load(['nightSession.user', 'task.taskType']);

        $this->assertNotNull($sessionTask->uuid);
        $this->assertSame('uuid', $sessionTask->getRouteKeyName());
        $this->assertSame($sessionTask->nightSession->user_id, $sessionTask->task->user_id);
        $this->assertSame($sessionTask->task->user_id, $sessionTask->task->taskType->user_id);
        $this->assertSame(NightSessionTaskStatus::Selected, $sessionTask->status);
        $this->assertNull($sessionTask->completed_at);
        $this->assertNull($sessionTask->skipped_at);
    }

    public function test_task_factory_creates_taxonomy_for_the_same_user(): void
    {
        $task = Task::factory()->withTaxonomy()->create();

        $task->load(['area', 'category', 'taskType']);

        $this->assertSame($task->user_id, $task->area->user_id);
        $this->assertSame($task->user_id, $task->category->user_id);
        $this->assertSame($task->user_id, $task->taskType->user_id);
        $this->assertInstanceOf(TaskDifficulty::class, $task->difficulty);
        $this->assertSame(TaskStatus::Active, $task->status);
        $this->assertNotNull($task->uuid);
        $this->assertSame('uuid', $task->getRouteKeyName());
    }

    public function test_night_session_factory_states_and_casts_are_correct(): void
    {
        $activeSession = NightSession::factory()->create();
        $completedSession = NightSession::factory()->completed()->create();

        $this->assertInstanceOf(CarbonImmutable::class, $activeSession->started_at);
        $this->assertNull($activeSession->ended_at);
        $this->assertInstanceOf(CarbonImmutable::class, $completedSession->ended_at);
        $this->assertGreaterThanOrEqual($completedSession->started_at, $completedSession->ended_at);
        $this->assertNotNull($activeSession->uuid);
        $this->assertSame('uuid', $activeSession->getRouteKeyName());
    }

    public function test_night_session_task_factory_states_and_casts_are_correct(): void
    {
        $completedTask = NightSessionTask::factory()->completed()->create();
        $skippedTask = NightSessionTask::factory()->skipped()->create();

        $this->assertSame(NightSessionTaskStatus::Completed, $completedTask->status);
        $this->assertInstanceOf(CarbonImmutable::class, $completedTask->selected_at);
        $this->assertInstanceOf(CarbonImmutable::class, $completedTask->completed_at);
        $this->assertNull($completedTask->skipped_at);

        $this->assertSame(NightSessionTaskStatus::Skipped, $skippedTask->status);
        $this->assertInstanceOf(CarbonImmutable::class, $skippedTask->skipped_at);
        $this->assertNull($skippedTask->completed_at);
        $this->assertIsInt($skippedTask->position);
    }

    public function test_task_type_factory_creates_custom_and_system_types(): void
    {
        $customType = TaskType::factory()->create();
        $systemType = TaskType::factory()->system()->create();

        $this->assertNotNull($customType->user_id);
        $this->assertFalse($customType->is_system);
        $this->assertNull($systemType->user_id);
        $this->assertTrue($systemType->is_system);
    }

    private function assertBelongsTo(BelongsTo $relation, string $related, string $foreignKey): void
    {
        $this->assertInstanceOf($related, $relation->getRelated());
        $this->assertSame($foreignKey, $relation->getForeignKeyName());
    }

    private function assertHasMany(HasMany $relation, string $related, string $foreignKey): void
    {
        $this->assertInstanceOf($related, $relation->getRelated());
        $this->assertSame($foreignKey, $relation->getForeignKeyName());
    }
}
