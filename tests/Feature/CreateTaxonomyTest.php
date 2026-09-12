<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    public static function kinds(): array
    {
        return [['areas'], ['categories']];
    }

    #[DataProvider('kinds')]
    public function test_creation_belongs_to_authenticated_user_and_appears_in_list(string $kind): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/' . $kind, [
            'name' => '  Personal  ', 'user_id' => $other->id,
        ])->assertCreated()->assertJsonPath('data.name', 'Personal');

        $this->assertDatabaseHas($kind, ['uuid' => $response->json('data.uuid'), 'name' => 'Personal', 'user_id' => $user->id]);
        $this->getJson('/api/' . $kind)->assertOk()->assertJsonFragment(['uuid' => $response->json('data.uuid')]);
        $this->actingAs($other)->getJson('/api/' . $kind)->assertOk()->assertJsonMissing(['uuid' => $response->json('data.uuid')]);
        $this->postJson('/api/' . $kind, ['name' => 'Personal'])->assertCreated();
    }

    #[DataProvider('kinds')]
    public function test_validation_prevents_empty_long_and_duplicate_names(string $kind): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['', '   ', str_repeat('a', 101)] as $name) {
            $this->postJson('/api/' . $kind, ['name' => $name])->assertUnprocessable()->assertJsonValidationErrors('name');
        }
        $this->postJson('/api/' . $kind, ['name' => 'Personal'])->assertCreated();
        $this->postJson('/api/' . $kind, ['name' => 'Personal'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount($kind, 1);
    }

    #[DataProvider('kinds')]
    public function test_creation_requires_verified_authentication(string $kind): void
    {
        $this->postJson('/api/' . $kind, ['name' => 'Personal'])->assertUnauthorized();
        $this->actingAs(User::factory()->unverified()->create())
            ->postJson('/api/' . $kind, ['name' => 'Personal'])->assertForbidden();
        $this->assertDatabaseCount($kind, 0);
    }
}
