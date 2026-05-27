<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('A extensao pdo_sqlite e necessaria para estes testes com banco em memoria.');
        }

        parent::setUp();
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
        $userId = $this->createUserRecord();

        $this->patchJson("/api/users/{$userId}", [
            'email' => 'email-invalido',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    private function createUserRecord(): string
    {
        $now = now();
        $districtId = (string) Str::uuid();
        $ubsId = (string) Str::uuid();
        $userId = (string) Str::uuid();

        DB::table('districts')->insert([
            'id' => $districtId,
            'name' => 'Distrito Teste',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('ubs')->insert([
            'id' => $ubsId,
            'district_id' => $districtId,
            'name' => 'UBS Teste',
            'bairro_ref' => 'Centro',
            'address' => 'Rua Teste, 100',
            'phone' => '42999999999',
            'email' => 'ubs@example.com',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            'id' => $userId,
            'ubs_id' => $ubsId,
            'name' => 'Usuario Teste',
            'age' => 30,
            'sex' => true,
            'cpf' => '00000000000',
            'address' => 'Rua Teste, 200',
            'phone' => '42988888888',
            'email' => 'user@example.com',
            'email_verified_at' => null,
            'password' => 'password',
            'role' => 'user',
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $userId;
    }
}
