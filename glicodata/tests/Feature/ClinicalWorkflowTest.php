<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\RiskClassification;
use App\Models\AssessmentModel;
use App\Models\PatientModel;
use App\Models\ProfessionalModel;
use App\Models\QuestionnaireVersionModel;
use App\Models\ReportModel;
use App\Models\UbsModel;
use Database\Seeders\QuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private UbsModel $ubs;

    private ProfessionalModel $professional;

    private PatientModel $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(QuestionnaireSeeder::class);
        $this->ubs = UbsModel::factory()->create();
        $this->professional = ProfessionalModel::factory()->create([
            'ubs_id' => $this->ubs->id,
            'first_name' => 'Ana',
            'specialty' => 'Endocrinologia',
        ]);
        $this->patient = PatientModel::query()->create([
            'ubs_id' => $this->ubs->id,
            'first_name' => 'Iara',
            'sex' => true,
            'neighborhood' => 'Centro',
            'neighborhood_normalized' => 'centro',
            'street_name' => 'Rua XV de Novembro',
        ]);

        Sanctum::actingAs($this->ubs, ['ubs']);
    }

    public function test_ubs_session_owns_clinical_access_and_patient_api_is_minimal(): void
    {
        $payload = $this->getJson('/api/patients')
            ->assertOk()
            ->assertJsonPath('data.0.first_name', 'Iara')
            ->assertJsonPath('data.0.neighborhood', 'Centro')
            ->json('data.0');

        foreach (['ubs_id', 'name', 'cpf', 'birth', 'address', 'phone'] as $field) {
            $this->assertArrayNotHasKey($field, $payload);
        }
    }

    public function test_completion_calculates_risk_on_server_and_keeps_selected_professional(): void
    {
        $assessmentId = $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
            'professional_id' => $this->professional->id,
        ])->assertCreated()->json('id');

        $this->postJson("/api/assessments/{$assessmentId}/complete", [
            'answers' => $this->highRiskAnswers(),
            'score' => 0,
            'percentage' => 0,
            'classification' => 'low',
        ])->assertOk()
            ->assertJsonPath('status', AssessmentStatus::Completed->value)
            ->assertJsonPath('professional.first_name', 'Ana')
            ->assertJsonPath('risk.score', 26)
            ->assertJsonPath('risk.percentage', 50)
            ->assertJsonPath('risk.classification', RiskClassification::High->value);

        $assessment = AssessmentModel::query()->findOrFail($assessmentId);
        $this->assertSame($this->professional->id, $assessment->professional_id);
        $this->assertDatabaseHas('audit_events', [
            'actor_ubs_id' => $this->ubs->id,
            'subject_id' => $assessmentId,
            'action' => 'complete',
        ]);

        $this->putJson("/api/assessments/{$assessmentId}", [
            'answers' => $this->highRiskAnswers(),
        ])->assertUnprocessable();
        $this->postJson('/api/risks', [])->assertMethodNotAllowed();
    }

    public function test_patient_and_professional_must_belong_to_same_ubs(): void
    {
        $otherProfessional = ProfessionalModel::factory()->create();

        $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
            'professional_id' => $otherProfessional->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['assessment']);

        $otherUbs = UbsModel::factory()->create();
        Sanctum::actingAs($otherUbs, ['ubs']);
        $this->getJson("/api/patients/{$this->patient->id}")->assertForbidden();
    }

    public function test_report_keeps_only_description_and_export_is_aggregated(): void
    {
        $assessment = $this->completedAssessment();

        $reportId = $this->postJson('/api/reports', [
            'assessment_id' => $assessment->id,
            'description' => 'Descrição clínica restrita.',
        ])->assertCreated()
            ->assertJsonMissingPath('title')
            ->assertJsonMissingPath('comment')
            ->json('id');

        $this->putJson("/api/reports/{$reportId}", [
            'description' => 'Descrição revisada.',
        ])->assertOk()->assertJsonPath('description', 'Descrição revisada.');

        $export = $this->getJson('/api/reports/export?format=json')
            ->assertOk()
            ->assertJsonPath('data.0.questionnaire', 'findrisc-br')
            ->assertJsonPath('data.0.total', null)
            ->assertJsonPath('data.0.suppressed', true);

        $payload = $export->getContent();
        $this->assertStringNotContainsString('Iara', $payload);
        $this->assertStringNotContainsString('Ana', $payload);
        $this->assertStringNotContainsString('Descrição', $payload);
        $this->assertStringNotContainsString($reportId, $payload);

        $this->deleteJson("/api/reports/{$reportId}")->assertNoContent();
        $this->assertSoftDeleted(ReportModel::class, ['id' => $reportId]);
    }

    public function test_blade_uses_ubs_session_and_exposes_dynamic_professional_search(): void
    {
        $this->actingAs($this->ubs, 'ubs');

        $this->get('/ubs/pacientes')
            ->assertOk()
            ->assertSee('Iara')
            ->assertDontSee('CPF')
            ->assertDontSee('Nascimento');

        $this->get('/ubs/avaliacoes/create')
            ->assertOk()
            ->assertSee('data-professional-search', false)
            ->assertSee(route('ubs.professionals.search'), false)
            ->assertSee('Ana')
            ->assertDontSee('Sintomas');
    }

    public function test_published_questionnaire_version_is_not_rewritten_by_seed(): void
    {
        $version = QuestionnaireVersionModel::query()->findOrFail(QuestionnaireSeeder::VERSION_ID);
        DB::table('questionnaire_versions')->where('id', $version->id)->update([
            'risk_rules' => json_encode(['method' => 'sentinel'], JSON_THROW_ON_ERROR),
        ]);

        $this->seed(QuestionnaireSeeder::class);

        $this->assertSame(['method' => 'sentinel'], $version->fresh()->risk_rules);
    }

    private function completedAssessment(): AssessmentModel
    {
        $id = $this->postJson('/api/assessments', [
            'patient_id' => $this->patient->id,
            'professional_id' => $this->professional->id,
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
            'age' => '65_plus',
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
