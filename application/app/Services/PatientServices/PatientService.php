<?php

namespace App\Services\PatientServices;

use App\Models\PatientModel;
use App\Repositories\PatientRepositories\PatientRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PatientService
{
    use ValidateUtils;

    public function __construct(
        protected PatientRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllPatients(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginatePatients($this->normalizePerPage($perPage));
    }

    public function getPatientsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginatePatientsForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getPatientById(string $id): PatientModel
    {
        $this->validateId($id);

        $patient = $this->repository->findPatientById($id);

        if ($patient === null) {
            throw (new ModelNotFoundException)->setModel(PatientModel::class, [$id]);
        }

        return $patient;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPatient(array $data): PatientModel
    {
        return DB::transaction(function () use ($data): PatientModel {
            $patient = $this->repository->createPatient($data);
            $this->auditService->record('create', $patient, (string) $patient->ubs_id, null, $patient->toArray());

            return $patient;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePatient(string $id, array $data): PatientModel
    {
        $patient = $this->getPatientById($id);

        return DB::transaction(function () use ($patient, $data): PatientModel {
            $before = $patient->toArray();
            $patient->fill($data)->save();
            $patient = $patient->refresh();

            $this->auditService->record('update', $patient, (string) $patient->ubs_id, $before, $patient->toArray());

            return $patient;
        });
    }

    public function deletePatient(string $id): bool
    {
        return $this->deletePatientInstance($this->getPatientById($id));
    }

    public function deletePatientInstance(PatientModel $patient): bool
    {
        return DB::transaction(function () use ($patient): bool {
            $before = $patient->toArray();
            $deleted = (bool) $patient->delete();

            if ($deleted) {
                $this->auditService->record('delete', $patient, (string) $patient->ubs_id, $before, $patient->toArray());
            }

            return $deleted;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
