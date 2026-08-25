<?php

namespace App\Services\AssessmentServices;

use App\Enums\AssessmentStatus;
use App\Models\AssessmentModel;
use App\Models\PatientModel;
use App\Models\RiskModel;
use App\Models\UserModel;
use App\Repositories\AssessmentRepositories\AssessmentRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Services\QuestionnaireServices\QuestionnaireAnswerValidator;
use App\Services\QuestionnaireServices\QuestionnaireService;
use App\Services\RiskServices\RiskCalculator;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class AssessmentService
{
    use ValidateUtils;

    public function __construct(
        protected AssessmentRepository $repository,
        protected AuditEventService $auditService,
        protected QuestionnaireService $questionnaireService,
        protected QuestionnaireAnswerValidator $answerValidator,
        protected RiskCalculator $riskCalculator,
    ) {}

    public function getAllAssessments(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateAssessments($this->normalizePerPage($perPage));
    }

    public function getAssessmentsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateAssessmentsForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getAssessmentById(string $id): AssessmentModel
    {
        $this->validateId($id);
        $assessment = $this->repository->findAssessmentById($id);

        if ($assessment === null) {
            throw (new ModelNotFoundException)->setModel(AssessmentModel::class, [$id]);
        }

        return $assessment;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAssessment(array $data): AssessmentModel
    {
        $this->ensureAssessmentRelationsBelongToUbs($data, (string) $data['ubs_id']);
        $patient = PatientModel::query()->findOrFail((string) $data['patient_id']);
        $version = $this->questionnaireService->currentPublished($data['questionnaire_version_id'] ?? null);
        $answers = $this->answerValidator->validate($version, $patient, $data['answers'] ?? [], false);

        return DB::transaction(function () use ($data, $version, $answers): AssessmentModel {
            $assessment = $this->repository->createAssessment([
                'patient_id' => $data['patient_id'],
                'user_id' => $data['user_id'],
                'ubs_id' => $data['ubs_id'],
                'questionnaire_version_id' => $version->id,
                'symptoms' => $data['symptoms'] ?? '',
                'answers' => $answers,
                'status' => AssessmentStatus::Draft,
                'started_at' => now(),
            ]);
            $this->auditService->record('create', $assessment, (string) $assessment->ubs_id, null, $assessment->toArray());

            return $this->getAssessmentById((string) $assessment->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAssessment(string $id, array $data): AssessmentModel
    {
        $assessment = $this->getAssessmentById($id);
        $this->ensureDraft($assessment);

        $candidate = array_merge($assessment->only(['patient_id', 'user_id']), $data);
        $this->ensureAssessmentRelationsBelongToUbs($candidate, (string) $assessment->ubs_id);
        $patient = PatientModel::query()->findOrFail((string) $candidate['patient_id']);
        $version = $assessment->questionnaireVersion()->firstOrFail();

        if (array_key_exists('answers', $data)) {
            $data['answers'] = $this->answerValidator->validate($version, $patient, $data['answers'], false);
        }

        return DB::transaction(function () use ($assessment, $data): AssessmentModel {
            $before = $assessment->toArray();
            $assessment->fill($data)->save();
            $assessment = $assessment->refresh();
            $this->auditService->record('update', $assessment, (string) $assessment->ubs_id, $before, $assessment->toArray());

            return $this->getAssessmentById((string) $assessment->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function completeAssessment(string $id, array $data): AssessmentModel
    {
        return DB::transaction(function () use ($id, $data): AssessmentModel {
            $assessment = AssessmentModel::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->ensureDraft($assessment);
            $assessment->load(['patient', 'questionnaireVersion']);

            $calculation = $this->riskCalculator->calculate(
                $assessment->questionnaireVersion,
                $assessment->patient,
                $data['answers'],
            );
            $before = $assessment->toArray();
            $assessment->fill([
                'symptoms' => $data['symptoms'] ?? '',
                'answers' => $calculation['answers'],
                'status' => AssessmentStatus::Completed,
                'completed_at' => now(),
            ])->save();

            $risk = RiskModel::withTrashed()->firstOrNew(['assessment_id' => $assessment->id]);
            $riskBefore = $risk->exists ? $risk->toArray() : null;
            $risk->fill([
                'score' => $calculation['score'],
                'percentage' => $calculation['percentage'],
                'classification' => $calculation['classification'],
                'deleted_at' => null,
            ])->save();

            $this->auditService->record('complete', $assessment, (string) $assessment->ubs_id, $before, $assessment->fresh()->toArray());
            $this->auditService->record(
                $riskBefore === null ? 'create' : 'recalculate',
                $risk,
                (string) $assessment->ubs_id,
                $riskBefore,
                $risk->fresh()->toArray(),
            );

            return $this->getAssessmentById((string) $assessment->id);
        });
    }

    public function deleteAssessment(string $id): bool
    {
        return $this->deleteAssessmentInstance($this->getAssessmentById($id));
    }

    public function deleteAssessmentInstance(AssessmentModel $assessment): bool
    {
        return DB::transaction(function () use ($assessment): bool {
            $ownerUbsId = (string) $assessment->ubs_id;
            $before = $assessment->toArray();

            foreach ([$assessment->risk()->first(), $assessment->report()->first()] as $dependent) {
                if ($dependent === null) {
                    continue;
                }

                $dependentBefore = $dependent->toArray();
                if (! (bool) $dependent->delete()) {
                    throw new LogicException('Não foi possível excluir logicamente um registro dependente da anamnese.');
                }
                $this->auditService->record('delete', $dependent, $ownerUbsId, $dependentBefore, $dependent->toArray());
            }

            if (! (bool) $assessment->delete()) {
                throw new LogicException('Não foi possível excluir logicamente a anamnese.');
            }

            $this->auditService->record('delete', $assessment, $ownerUbsId, $before, $assessment->toArray());

            return true;
        });
    }

    private function ensureDraft(AssessmentModel $assessment): void
    {
        if ($assessment->status === AssessmentStatus::Completed) {
            throw ValidationException::withMessages([
                'assessment' => ['Uma anamnese concluída é imutável. Crie uma nova versão assistencial para corrigir informações.'],
            ]);
        }
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureAssessmentRelationsBelongToUbs(array $data, string $ubsId): void
    {
        $patientBelongs = PatientModel::query()
            ->whereKey((string) $data['patient_id'])
            ->where('ubs_id', $ubsId)
            ->exists();
        $userBelongs = UserModel::query()
            ->whereKey((string) $data['user_id'])
            ->where('ubs_id', $ubsId)
            ->where('is_active', true)
            ->exists();

        if (! $patientBelongs || ! $userBelongs) {
            throw ValidationException::withMessages([
                'assessment' => ['Paciente e profissional devem pertencer à UBS autenticada e estar ativos.'],
            ]);
        }
    }
}
