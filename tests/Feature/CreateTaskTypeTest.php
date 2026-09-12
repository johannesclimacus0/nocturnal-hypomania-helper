<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_type_is_owned_and_cannot_be_created_as_system(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/task-types', [
            'name' => 'Maintenance', 'slug' => 'maintenance',
            'user_id' => $other->id, 'is_system' => true,
        ])->assertCreated()->assertJsonPath('data.is_system', false);

        $this->assertDatabaseHas('task_types', [
            'uuid' => $response->json('data.uuid'), 'user_id' => $user->id,
            'slug' => 'maintenance', 'is_system' => false,
        ]);
        $this->getJson('/api/task-types')->assertOk()->assertJsonFragment(['uuid' => $response->json('data.uuid')]);
        $this->actingAs($other)->getJson('/api/task-types')->assertOk()->assertJsonMissing(['uuid' => $response->json('data.uuid')]);
        $this->postJson('/api/task-types', ['name' => 'Maintenance', 'slug' => 'maintenance'])->assertCreated();
    }

    public function test_slug_validation_and_duplicate_protection(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['', 'Two Words', 'bad--slug', str_repeat('a', 101), 'build'] as $slug) {
            $this->postJson('/api/task-types', ['name' => 'Custom', 'slug' => $slug])
                ->assertUnprocessable()->assertJsonValidationErrors('slug');
        }
        $this->postJson('/api/task-types', ['name' => '', 'slug' => 'custom'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/task-types', ['name' => 'Custom', 'slug' => 'custom'])->assertCreated();
        $this->postJson('/api/task-types', ['name' => 'Another', 'slug' => 'custom'])
            ->assertUnprocessable()->assertJsonValidationErrors('slug');
    }

    public function test_creation_requires_verified_authentication(): void
    {
        $this->postJson('/api/task-types', ['name' => 'Custom', 'slug' => 'custom'])->assertUnauthorized();
        $this->actingAs(User::factory()->unverified()->create())
            ->postJson('/api/task-types', ['name' => 'Custom', 'slug' => 'custom'])->assertForbidden();
    }
}
