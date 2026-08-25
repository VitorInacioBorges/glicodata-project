<?php

namespace Tests\Feature;

use App\Models\AdministratorModel;
use App\Models\ProfessionalModel;
use App\Models\UbsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfessionalPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_schema_has_no_professional_patient_or_admin_identity_columns(): void
    {
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('professionals'));

        foreach (['name', 'cpf', 'birth', 'sex', 'address', 'phone', 'email', 'password', 'role', 'council_type', 'council_number', 'council_uf'] as $column) {
            $this->assertFalse(Schema::hasColumn('professionals', $column), $column);
        }

        foreach (['name', 'cpf', 'birth', 'address', 'phone'] as $column) {
            $this->assertFalse(Schema::hasColumn('patients', $column), $column);
        }

        $this->assertFalse(Schema::hasColumn('administrators', 'name'));
        $this->assertFalse(Schema::hasColumn('administrators', 'email'));
        $this->assertTrue(Schema::hasColumn('administrators', 'admin_code'));
    }

    public function test_ubs_creates_and_searches_a_minimal_professional_reference(): void
    {
        $ubs = UbsModel::factory()->create();
        Sanctum::actingAs($ubs, ['ubs']);

        $id = $this->postJson('/api/professionals', [
            'first_name' => 'Ana',
            'specialty' => 'Medicina de família',
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('first_name', 'Ana')
            ->assertJsonPath('specialty', 'Medicina de família')
            ->json('id');

        $professional = ProfessionalModel::query()->findOrFail($id);
        $this->assertSame($ubs->id, $professional->ubs_id);
        $this->assertSame(['ubs_id', 'first_name', 'specialty', 'is_active'], $professional->getFillable());

        $payload = $this->getJson('/api/professionals/search?q=medicina')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->json('data.0');

        foreach (['ubs_id', 'email', 'password', 'cpf', 'birth', 'address', 'phone', 'council_number'] as $field) {
            $this->assertArrayNotHasKey($field, $payload);
        }
    }

    public function test_professional_login_type_and_route_do_not_exist(): void
    {
        $this->postJson('/api/auth/login', [
            'account_type' => 'user',
            'identifier' => 'medico',
            'password' => 'StrongPassword!123',
            'device_name' => 'teste',
        ])->assertUnprocessable()->assertJsonValidationErrors(['account_type']);

        $this->get('/login/profissional')->assertNotFound();
    }

    public function test_administrator_authenticates_with_code_without_name_or_email(): void
    {
        $administrator = AdministratorModel::factory()->create([
            'admin_code' => 'ADMIN_001',
            'password' => 'StrongPassword!123',
        ]);

        $this->postJson('/api/auth/login', [
            'account_type' => 'admin',
            'identifier' => 'admin_001',
            'password' => 'StrongPassword!123',
            'device_name' => 'teste',
        ])->assertOk()
            ->assertJsonPath('identity.admin_code', 'ADMIN_001')
            ->assertJsonMissingPath('identity.name')
            ->assertJsonMissingPath('identity.email');

        $this->assertSame(['admin_code', 'password', 'is_active'], $administrator->getFillable());
    }
}
