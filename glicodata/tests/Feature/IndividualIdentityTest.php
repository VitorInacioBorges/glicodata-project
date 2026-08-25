<?php

namespace Tests\Feature;

use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndividualIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ubs_creates_a_professional_with_council_fields_and_hashed_password(): void
    {
        $ubs = UbsModel::factory()->create();
        Sanctum::actingAs($ubs, ['ubs']);

        $id = $this->postJson('/api/users', $this->professionalPayload())
            ->assertCreated()
            ->assertJsonPath('council_type', 'CRM')
            ->assertJsonMissing(['password'])
            ->json('id');

        $professional = UserModel::query()->findOrFail($id);
        $this->assertTrue(Hash::check('IndividualStrong!123', $professional->password));
        $this->assertSame('CRM', $professional->council_type->value);
        $this->assertSame('PR', $professional->council_uf);
        $this->assertSame('Medicina de família e comunidade', $professional->specialty);
    }

    public function test_professional_fields_are_required_and_unit_admin_cannot_claim_a_council(): void
    {
        $ubs = UbsModel::factory()->create();
        Sanctum::actingAs($ubs, ['ubs']);

        $payload = $this->professionalPayload();
        unset($payload['council_number'], $payload['council_uf'], $payload['specialty']);
        $this->postJson('/api/users', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['council_number', 'council_uf', 'specialty']);

        $adminPayload = [...$this->professionalPayload(), 'role' => 'admin'];
        $this->postJson('/api/users', $adminPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['council_type', 'council_number', 'council_uf', 'specialty']);
    }

    public function test_roles_prevent_a_professional_from_managing_the_team(): void
    {
        $ubs = UbsModel::factory()->create();
        $professional = UserModel::factory()->create(['ubs_id' => $ubs->id]);
        $manager = UserModel::factory()->unitAdministrator()->create(['ubs_id' => $ubs->id]);

        Sanctum::actingAs($professional, ['user']);
        $this->getJson('/api/users')->assertForbidden();

        Sanctum::actingAs($manager, ['user']);
        $this->getJson('/api/users')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_professional_can_login_to_blade_and_inactive_unit_blocks_access(): void
    {
        $ubs = UbsModel::factory()->create();
        $professional = UserModel::factory()->create(['ubs_id' => $ubs->id]);

        $this->post('/login', [
            'account_type' => 'user',
            'identifier' => $professional->email,
            'password' => 'StrongPassword!123',
        ])->assertRedirect(route('ubs.patients.index'));
        $this->assertAuthenticatedAs($professional, 'user');

        $ubs->forceFill(['is_active' => false])->save();
        $this->get('/ubs/pacientes')->assertRedirect(route('user.login'));
        $this->assertGuest('user');
    }

    public function test_deactivating_a_ubs_revokes_individual_tokens(): void
    {
        $ubs = UbsModel::factory()->create();
        $professional = UserModel::factory()->create(['ubs_id' => $ubs->id]);
        $professional->createToken('individual', ['user'], now()->addDay());
        $administrator = AdministratorModel::factory()->create();
        Sanctum::actingAs($administrator, ['admin']);

        $this->putJson("/api/ubs/{$ubs->id}", ['is_active' => false])->assertOk();

        $this->assertSame(0, $professional->tokens()->count());
    }

    /** @return array<string, mixed> */
    private function professionalPayload(): array
    {
        return [
            'name' => 'Dra. Ana Teste',
            'birth' => '1985-04-10',
            'sex' => false,
            'cpf' => '529.982.247-25',
            'address' => 'Rua de Teste, 10',
            'phone' => '(42) 99999-0000',
            'email' => 'ana.teste@example.test',
            'role' => 'professional',
            'council_type' => 'CRM',
            'council_number' => '123456',
            'council_uf' => 'PR',
            'specialty' => 'Medicina de família e comunidade',
            'password' => 'IndividualStrong!123',
            'password_confirmation' => 'IndividualStrong!123',
            'is_active' => true,
        ];
    }
}
