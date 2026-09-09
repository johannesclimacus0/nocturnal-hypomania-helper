<?php

namespace Database\Factories;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'area_id' => null,
            'category_id' => null,
            'task_type_id' => fn (array $attributes): int => TaskType::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->getKey(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'estimated_time_minutes' => fake()->numberBetween(5, 180),
            'difficulty' => fake()->randomElement(TaskDifficulty::cases()),
            'status' => TaskStatus::Active,
            'last_selected_at' => null,
            'last_skipped_at' => null,
            'last_completed_at' => null,
        ];
    }

    public function withTaxonomy(): static
    {
        return $this->afterCreating(function (Task $task): void {
            $task->area()->associate(Area::factory()->create([
                'user_id' => $task->user_id,
            ]));
            $task->category()->associate(Category::factory()->create([
                'user_id' => $task->user_id,
            ]));
            $task->save();
        });
    }
}
