<?php

namespace App\Repositories\RiskRepositories;

use App\Models\RiskModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiskRepository
{
    public function __construct(
        protected RiskModel $model,
    ) {}

    public function paginateRisksForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('assessment', function ($query) use ($ubsId): void {
                $query->where('ubs_id', $ubsId);
            })
            ->with(['assessment.patient', 'assessment.questionnaireVersion.questionnaire'])
            ->latest()
            ->paginate($perPage);
    }

    public function findRiskById(string $id): ?RiskModel
    {
        return $this->model->newQuery()->with(['assessment.patient'])->find($id);
    }
}
