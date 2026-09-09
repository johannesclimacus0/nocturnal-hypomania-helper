<?php

namespace Database\Factories;

use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskType>
 */
class TaskTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'user_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'is_system' => true,
        ]);
    }
}
