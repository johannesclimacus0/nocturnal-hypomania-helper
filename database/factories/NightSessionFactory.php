<?php

namespace Database\Factories;

use App\Models\NightSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NightSession>
 */
class NightSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-1 month'),
            'ended_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes): array {
            $startedAt = CarbonImmutable::parse($attributes['started_at']);

            return [
                'ended_at' => $startedAt->addMinutes(fake()->numberBetween(15, 240)),
            ];
        });
    }
}
