<?php

namespace Tests\Feature;

use App\Actions\Sessions\SelectNextTaskAction;
use App\Enums\NightSessionTaskStatus;
use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectNextTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_combines_all_constraints(): void
    {
        $source = Task::factory()->withTaxonomy()->create();
        $session = NightSession::factory()->for($source->user)->create([
            'available_time_minutes' => 30,
            'difficulty' => TaskDifficulty::Normal,
            'area_id' => $source->area_id,
            'category_id' => $source->category_id,
            'task_type_id' => $source->task_type_id,
        ]);
        $source->update(['status' => TaskStatus::Archived]);
        $attributes = [
            'user_id' => $session->user_id,
            'estimated_time_minutes' => 30,
            'difficulty' => TaskDifficulty::Normal,
            'area_id' => $session->area_id,
            'category_id' => $session->category_id,
            'task_type_id' => $session->task_type_id,
        ];

        foreach ([
            ['user_id' => User::factory()->create()->id],
            ['estimated_time_minutes' => 31],
            ['difficulty' => TaskDifficulty::Easy],
            ['difficulty' => TaskDifficulty::Hard],
            ['area_id' => null],
            ['category_id' => null],
            ['task_type_id' => TaskType::factory()->create()->id],
            ['status' => TaskStatus::Completed],
        ] as $override) {
            Task::factory()->create(array_replace($attributes, $override));
        }

        foreach (NightSessionTaskStatus::cases() as $status) {
            $task = Task::factory()->create($attributes);
            NightSessionTask::factory()->for($session)->for($task)->create(['status' => $status]);
        }

        $expected = Task::factory()->create($attributes);
        $result = app(SelectNextTaskAction::class)->handle($session->user, $session);

        $this->assertTrue($expected->is($result));
        $this->assertSame(3, $session->nightSessionTasks()->count());
        $expected->update(['status' => TaskStatus::Archived]);
        $this->assertNull(app(SelectNextTaskAction::class)->handle($session->user, $session));
    }

    public function test_null_filters_allow_tasks_and_other_sessions_do_not_exclude_them(): void
    {
        $session = NightSession::factory()->create(['available_time_minutes' => 20]);
        $task = Task::factory()->for($session->user)->create(['estimated_time_minutes' => 20]);
        $other = NightSession::factory()->for($session->user)->create(['available_time_minutes' => 20]);
        NightSessionTask::factory()->for($other)->for($task)->create();

        $this->assertTrue($task->is(app(SelectNextTaskAction::class)->handle($session->user, $session)));
        $session->update(['ended_at' => now()]);
        $this->assertNull(app(SelectNextTaskAction::class)->handle($session->user, $session));
    }

    public function test_foreign_session_is_rejected(): void
    {
        $session = NightSession::factory()->create(['available_time_minutes' => 30]);

        $this->expectException(AuthorizationException::class);
        app(SelectNextTaskAction::class)->handle(User::factory()->create(), $session);
    }
}
