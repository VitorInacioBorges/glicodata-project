<?php

namespace App\Services\AssessmentServices;

use App\Models\AssessmentModel;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Repositories\AssessmentRepositories\AssessmentRepository;
use App\Services\AuditEventServices\AuditEventService;
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

        return DB::transaction(function () use ($data): AssessmentModel {
            $assessment = $this->repository->createAssessment($data);
            $this->auditService->record('create', $assessment, (string) $assessment->ubs_id, null, $assessment->toArray());

            return $assessment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAssessment(string $id, array $data): AssessmentModel
    {
        $assessment = $this->getAssessmentById($id);
        $candidate = array_merge($assessment->only(['patient_id', 'user_id']), $data);
        $this->ensureAssessmentRelationsBelongToUbs($candidate, (string) $assessment->ubs_id);

        return DB::transaction(function () use ($assessment, $data): AssessmentModel {
            $before = $assessment->toArray();
            $assessment->fill($data)->save();
            $assessment = $assessment->refresh();

            $this->auditService->record('update', $assessment, (string) $assessment->ubs_id, $before, $assessment->toArray());

            return $assessment;
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
            $dependents = [$assessment->risk()->first(), $assessment->report()->first()];

            foreach ($dependents as $dependent) {
                if ($dependent === null) {
                    continue;
                }

                $dependentBefore = $dependent->toArray();

                if (! (bool) $dependent->delete()) {
                    throw new LogicException('Nao foi possivel excluir logicamente um registro dependente da avaliacao.');
                }

                $this->auditService->record('delete', $dependent, $ownerUbsId, $dependentBefore, $dependent->toArray());
            }

            if (! (bool) $assessment->delete()) {
                throw new LogicException('Nao foi possivel excluir logicamente a avaliacao.');
            }

            $this->auditService->record(
                'delete',
                $assessment,
                $ownerUbsId,
                $before,
                $assessment->toArray(),
            );

            return true;
        });
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
            ->exists();

        if (! $patientBelongs || ! $userBelongs) {
            throw ValidationException::withMessages([
                'assessment' => ['Paciente e usuario devem pertencer a UBS autenticada.'],
            ]);
        }
    }
}
