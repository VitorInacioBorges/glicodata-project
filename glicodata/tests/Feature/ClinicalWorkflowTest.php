<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\RiskClassification;
use App\Models\AssessmentModel;
use App\Models\PatientModel;
use App\Models\QuestionnaireVersionModel;
use App\Models\ReportModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Database\Seeders\QuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private UbsModel $ubs;

    private UserModel $professional;

    private PatientModel $patient;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('A extensão pdo_sqlite é necessária para estes testes.');
        }

        parent::setUp();
        $this->seed(QuestionnaireSeeder::class);
        $this->ubs = UbsModel::factory()->create();
        $this->professional = UserModel::factory()->create(['ubs_id' => $this->ubs->id]);
        $this->patient = PatientModel::query()->create([
            'ubs_id' => $this->ubs->id,
            'name' => 'Paciente Banco Real',
            'birth' => '1970-01-01',
            'sex' => true,
            'cpf' => '529.982.247-25',
            'address' => 'Endereço protegido',
            'phone' => '(42) 99999-0000',
        ]);
    }

    public function test_only_an_individual_user_can_access_clinical_data(): void
    {
        Sanctum::actingAs($this->ubs, ['ubs']);
        $this->getJson('/api/patients')->assertForbidden();

        Sanctum::actingAs($this->professional, ['user']);
        $this->getJson('/api/patients')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Paciente Banco Real');
    }

    public function test_professional_login_returns_individual_identity_and_role(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'account_type' => 'user',
            'identifier' => $this->professional->email,
            'password' => 'StrongPassword!123',
            'device_name' => 'homologacao',
        ]);

        $response->assertOk()
            ->assertJsonPath('account_type', 'user')
            ->assertJsonPath('identity.ubs_id', $this->ubs->id)
            ->assertJsonPath('identity.role', 'professional')
            ->assertJsonPath('identity.council_type', 'CRM');
    }

    public function test_anamnesis_completion_calculates_risk_on_the_server_and_becomes_immutable(): void
    {
        Sanctum::actingAs($this->professional, ['user']);

        $assessmentId = $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
            'symptoms' => 'Fadiga relatada.',
        ])->assertCreated()->json('id');

        $this->postJson("/api/assessments/{$assessmentId}/complete", [
            'symptoms' => 'Fadiga relatada.',
            'answers' => $this->highRiskAnswers(),
            // Estes campos não pertencem ao contrato e jamais controlam o cálculo.
            'score' => 0,
            'percentage' => 0,
            'classification' => 'low',
        ])->assertOk()
            ->assertJsonPath('status', AssessmentStatus::Completed->value)
            ->assertJsonPath('risk.score', 25)
            ->assertJsonPath('risk.percentage', 50)
            ->assertJsonPath('risk.classification', RiskClassification::High->value);

        $assessment = AssessmentModel::query()->findOrFail($assessmentId);
        $this->assertSame($this->professional->id, $assessment->user_id);
        $this->assertNotNull($assessment->questionnaire_version_id);
        $this->assertDatabaseHas('risks', [
            'assessment_id' => $assessmentId,
            'score' => 25,
            'classification' => 'high',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'actor_user_id' => $this->professional->id,
            'subject_id' => $assessmentId,
            'action' => 'complete',
        ]);

        $this->putJson("/api/assessments/{$assessmentId}", [
            'symptoms' => 'Tentativa de alteração posterior.',
        ])->assertUnprocessable();
        $this->postJson('/api/risks', [
            'assessment_id' => $assessmentId,
            'score' => 0,
            'percentage' => 0,
            'classification' => 'low',
        ])->assertMethodNotAllowed();
    }

    public function test_incomplete_questionnaire_cannot_be_completed(): void
    {
        Sanctum::actingAs($this->professional, ['user']);
        $assessmentId = $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
        ])->assertCreated()->json('id');

        $this->postJson("/api/assessments/{$assessmentId}/complete", [
            'answers' => ['bmi' => 28],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'answers.waist',
                'answers.physical_activity',
                'answers.vegetables',
                'answers.antihypertensive',
                'answers.high_glucose',
                'answers.family_history',
            ]);

        $this->assertDatabaseMissing('risks', ['assessment_id' => $assessmentId]);
    }

    public function test_reseeding_does_not_rewrite_a_published_questionnaire_version(): void
    {
        $version = QuestionnaireVersionModel::query()->findOrFail(QuestionnaireSeeder::VERSION_ID);
        $rules = $version->risk_rules;
        DB::table('questionnaire_versions')->where('id', $version->id)->update([
            'risk_rules' => json_encode(['method' => 'sentinel'], JSON_THROW_ON_ERROR),
        ]);

        $this->seed(QuestionnaireSeeder::class);

        $this->assertSame(
            ['method' => 'sentinel'],
            $version->fresh()->risk_rules,
        );

        // Restaura somente dentro do banco descartável deste teste para que a
        // intenção da asserção permaneça explícita.
        DB::table('questionnaire_versions')->where('id', $version->id)->update([
            'risk_rules' => json_encode($rules, JSON_THROW_ON_ERROR),
        ]);
    }

    public function test_cross_tenant_records_are_forbidden(): void
    {
        $otherUbs = UbsModel::factory()->create();
        $otherProfessional = UserModel::factory()->create(['ubs_id' => $otherUbs->id]);
        Sanctum::actingAs($otherProfessional, ['user']);

        $this->getJson("/api/patients/{$this->patient->id}")->assertForbidden();
    }

    public function test_report_crud_and_anonymized_export_do_not_leak_personal_or_free_text_data(): void
    {
        $assessment = $this->completedAssessment();
        Sanctum::actingAs($this->professional, ['user']);

        $reportId = $this->postJson('/api/reports', [
            'assessment_id' => $assessment->id,
            'title' => 'Relatório individual sigiloso',
            'description' => 'Descrição que não pode aparecer na exportação.',
            'comment' => 'Comentário interno identificável.',
        ])->assertCreated()->json('id');

        $this->putJson("/api/reports/{$reportId}", ['title' => 'Relatório revisado'])
            ->assertOk()
            ->assertJsonPath('title', 'Relatório revisado');

        $export = $this->getJson('/api/reports/export?format=json')
            ->assertOk()
            ->assertJsonPath('data.0.questionnaire', 'findrisc-br')
            ->assertJsonPath('data.0.total', null)
            ->assertJsonPath('data.0.suppressed', true);

        $payload = $export->getContent();
        $this->assertStringNotContainsString($this->patient->name, $payload);
        $this->assertStringNotContainsString($this->patient->cpf, $payload);
        $this->assertStringNotContainsString('Descrição que não pode aparecer', $payload);
        $this->assertStringNotContainsString($reportId, $payload);

        $this->deleteJson("/api/reports/{$reportId}")->assertNoContent();
        $this->assertSoftDeleted(ReportModel::class, ['id' => $reportId]);
    }

    public function test_blade_reads_real_patient_data_from_the_authenticated_tenant(): void
    {
        $this->actingAs($this->professional, 'user')
            ->get('/ubs/pacientes')
            ->assertOk()
            ->assertSee('Paciente Banco Real')
            ->assertDontSee('Maria Aparecida Santos');
    }

    private function completedAssessment(): AssessmentModel
    {
        Sanctum::actingAs($this->professional, ['user']);
        $id = $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
        ])->assertCreated()->json('id');
        $this->postJson("/api/assessments/{$id}/complete", [
            'answers' => $this->highRiskAnswers(),
        ])->assertOk();

        return AssessmentModel::query()->findOrFail($id);
    }

    /** @return array<string, int|string> */
    private function highRiskAnswers(): array
    {
        return [
            'bmi' => 31,
            'waist' => 103,
            'physical_activity' => 'no',
            'vegetables' => 'no',
            'antihypertensive' => 'yes',
            'high_glucose' => 'yes',
            'family_history' => 'first_degree',
        ];
    }
}
