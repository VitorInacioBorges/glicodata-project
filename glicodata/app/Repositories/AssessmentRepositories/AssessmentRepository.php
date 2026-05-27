<?php

namespace App\Repositories\AssessmentRepositories;

use App\Models\AssessmentModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AssessmentRepository
{
    public function __construct(
        protected AssessmentModel $model,
    ) {
    }

    public function paginateAssessments(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function paginateAssessmentsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('ubs_id', $ubsId)
            ->paginate($perPage);
    }

    public function findAssessmentById(string $id): ?AssessmentModel
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAssessment(array $data): AssessmentModel
    {
        return $this->model->newQuery()->create($data);
    }
}
