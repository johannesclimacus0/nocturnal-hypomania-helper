<?php

namespace Database\Seeders;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.org',
            ]);

            $areas = Area::factory()->for($user)->count(3)->create();
            $categories = Category::factory()->for($user)->count(3)->create();
            $taskTypes = TaskType::factory()->for($user)->count(3)->create([
                'is_system' => false,
            ]);

            Task::factory()->for($user)->count(15)
                ->sequence(fn (Sequence $sequence): array => [
                    'area_id' => $areas[$sequence->index % 3]->id,
                    'category_id' => $categories[intdiv($sequence->index, 3) % 3]->id,
                    'task_type_id' => $taskTypes[($sequence->index + intdiv($sequence->index, 3)) % 3]->id,
                    'difficulty' => TaskDifficulty::cases()[$sequence->index % 3],
                    'estimated_time_minutes' => 5 + ($sequence->index % 6) * 5,
                    'status' => TaskStatus::Active,
                ])
                ->create();
        });
    }
}
