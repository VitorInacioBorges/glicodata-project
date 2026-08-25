<?php

namespace App\Repositories\ReportRepositories;

use App\Models\ReportModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository
{
    public function __construct(
        protected ReportModel $model,
    ) {}

    public function paginateReportsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('assessment', function ($query) use ($ubsId): void {
                $query->where('ubs_id', $ubsId);
            })
            ->with(['assessment.patient', 'assessment.professional', 'assessment.risk', 'assessment.questionnaireVersion.questionnaire'])
            ->latest()
            ->paginate($perPage);
    }

    public function findReportById(string $id): ?ReportModel
    {
        return $this->model->newQuery()
            ->with(['assessment.patient', 'assessment.professional', 'assessment.risk', 'assessment.questionnaireVersion.questionnaire'])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReport(array $data): ReportModel
    {
        return $this->model->newQuery()->create($data);
    }
}
