<?php

namespace Tests\Feature;

use App\Models\ProfessionalModel;
use App\Models\UbsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;

    private UbsModel $ubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ubs = UbsModel::factory()->create();
        Sanctum::actingAs($this->ubs, ['ubs']);
    }

    public function test_professionals_require_ubs_authentication(): void
    {
        auth('sanctum')->forgetUser();

        $this->getJson('/api/professionals')->assertUnauthorized();
    }

    public function test_professionals_index_is_scoped_to_the_authenticated_ubs(): void
    {
        ProfessionalModel::factory()->create(['ubs_id' => $this->ubs->id, 'first_name' => 'Ana']);
        ProfessionalModel::factory()->create(['first_name' => 'Beatriz']);

        $this->getJson('/api/professionals')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Ana');
    }

    public function test_professional_contract_accepts_only_first_name_specialty_and_status(): void
    {
        $this->postJson('/api/professionals', [
            'first_name' => 'Ana',
            'specialty' => 'Endocrinologia',
            'email' => 'nao-deve-exist@example.test',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $this->postJson('/api/professionals', [
            'first_name' => 'Ana Maria',
            'specialty' => 'Endocrinologia',
        ])->assertUnprocessable()->assertJsonValidationErrors(['first_name']);
    }

    public function test_patient_contract_rejects_removed_identity_fields_and_house_number(): void
    {
        $this->postJson('/api/patients', [
            'first_name' => 'Iara',
            'sex' => false,
            'neighborhood' => 'Centro',
            'street_name' => 'Rua XV de Novembro, 42',
            'cpf' => '00000000000',
        ])->assertUnprocessable()->assertJsonValidationErrors(['street_name', 'cpf']);
    }
}
