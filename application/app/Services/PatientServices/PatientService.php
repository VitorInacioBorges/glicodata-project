<?php

namespace App\Services\PatientServices;

use App\Models\PatientModel;
use App\Repositories\PatientRepositories\PatientRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PatientService
{
    use ValidateUtils;

    public function __construct(
        protected PatientRepository $repository,
    ) {
    }

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
            throw (new ModelNotFoundException())->setModel(PatientModel::class, [$id]);
        }

        return $patient;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPatient(array $data): PatientModel
    {
        $this->validateCreatePatientData($data);

        return $this->repository->createPatient($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updatePatient(string $id, array $data): PatientModel
    {
        $patient = $this->getPatientById($id);
        $this->validateUpdatePatientData($data, $id);

        $patient->fill($data);
        $patient->save();

        return $patient->refresh();
    }

    public function deletePatient(string $id): bool
    {
        return (bool) $this->getPatientById($id)->delete();
    }

    public function deletePatientInstance(PatientModel $patient): bool
    {
        return (bool) $patient->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
