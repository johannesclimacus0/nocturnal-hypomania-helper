<?php

namespace Database\Factories;

use App\Enums\NightSessionTaskStatus;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NightSessionTask>
 */
class NightSessionTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'night_session_id' => NightSession::factory(),
            'task_id' => function (array $attributes): int {
                $nightSession = NightSession::query()->findOrFail($attributes['night_session_id']);

                return Task::factory()->create([
                    'user_id' => $nightSession->user_id,
                ])->getKey();
            },
            'status' => NightSessionTaskStatus::Selected,
            'position' => fake()->numberBetween(0, 20),
            'selected_at' => now(),
            'completed_at' => null,
            'skipped_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => NightSessionTaskStatus::Completed,
            'completed_at' => now(),
            'skipped_at' => null,
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (): array => [
            'status' => NightSessionTaskStatus::Skipped,
            'completed_at' => null,
            'skipped_at' => now(),
        ]);
    }
}
