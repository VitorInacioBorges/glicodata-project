<?php

namespace Tests\Feature;

use App\Models\UbsModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;

    private UbsModel $ubs;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('A extensão pdo_sqlite é necessária para estes testes.');
        }

        parent::setUp();

        $this->ubs = UbsModel::factory()->create();
        Sanctum::actingAs($this->ubs, ['ubs']);
    }

    public function test_api_users_requires_authentication(): void
    {
        auth('sanctum')->forgetUser();

        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_api_users_index_returns_successful_response(): void
    {
        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_create_rejects_invalid_email(): void
    {
        $this->postJson('/api/users', [
            'email' => 'email-invalido',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_update_rejects_invalid_email(): void
    {
        $user = UserModel::factory()->create(['ubs_id' => $this->ubs->id]);

        $this->patchJson("/api/users/{$user->id}", [
            'email' => 'email-invalido',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
