<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClearSessionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_clears_only_sessions_older_than_given_hours(): void
    {
        $user = User::factory()->create();

        $outdatedSession = NightSession::factory()->for($user)
            ->create([
                'started_at' => now()->subHours(25),
                'ended_at' => null,
                'available_time_minutes' => 60,
            ]);

        $recentSession = NightSession::factory()->for($user)
            ->create([
                'started_at' => now(),
                'ended_at' => null,
                'available_time_minutes' => 60,
            ]);

        $endedSession = NightSession::factory()->for($user)
            ->create([
                'started_at' => now()->subHours(25),
                'ended_at' => now()->subHours(3),
                'available_time_minutes' => 60,
            ]);

        $this->artisan('sessions:clear')
            ->expectsOutput('Cleared 1 session records')
            ->assertSuccessful();

        $this->assertModelExists($endedSession);
        $this->assertModelExists($recentSession);
    }
}
