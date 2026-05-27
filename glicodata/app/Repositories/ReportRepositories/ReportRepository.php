<?php

namespace App\Repositories\ReportRepositories;

use App\Models\ReportModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository
{
    public function __construct(
        protected ReportModel $model,
    ) {
    }

    public function paginateReports(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function paginateReportsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('assessment', function ($query) use ($ubsId): void {
                $query->where('ubs_id', $ubsId);
            })
            ->paginate($perPage);
    }

    public function findReportById(string $id): ?ReportModel
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createReport(array $data): ReportModel
    {
        return $this->model->newQuery()->create($data);
    }
}
