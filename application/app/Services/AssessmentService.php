<?php

namespace App\Services;

use App\Models\AssessmentModel;
use App\Repositories\AssessmentRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssessmentService
{
    use ValidateUtils;

    public function __construct(
        protected AssessmentRepository $repository,
    ) {
    }

    public function getAllAssessments(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateAssessments($this->normalizePerPage($perPage));
    }

    public function getAssessmentById(string $id): AssessmentModel
    {
        $this->validateId($id);

        $assessment = $this->repository->findAssessmentById($id);

        if ($assessment === null) {
            throw (new ModelNotFoundException())->setModel(AssessmentModel::class, [$id]);
        }

        return $assessment;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAssessment(array $data): AssessmentModel
    {
        return $this->repository->createAssessment($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateAssessment(string $id, array $data): AssessmentModel
    {
        $assessment = $this->getAssessmentById($id);
        $assessment->fill($data);
        $assessment->save();

        return $assessment->refresh();
    }

    public function deleteAssessment(string $id): bool
    {
        return (bool) $this->getAssessmentById($id)->delete();
    }

    public function deleteAssessmentInstance(AssessmentModel $assessment): bool
    {
        return (bool) $assessment->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
