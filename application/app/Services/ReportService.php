<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Repositories\ReportRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReportService
{
    use ValidateUtils;

    public function __construct(
        protected ReportRepository $repository,
    ) {
    }

    public function getAllReports(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateReports($this->normalizePerPage($perPage));
    }

    public function getReportById(string $id): ReportModel
    {
        $this->validateId($id);

        $report = $this->repository->findReportById($id);

        if ($report === null) {
            throw (new ModelNotFoundException())->setModel(ReportModel::class, [$id]);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createReport(array $data): ReportModel
    {
        $this->validateCreateReportData($data);

        return $this->repository->createReport($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateReport(string $id, array $data): ReportModel
    {
        $report = $this->getReportById($id);
        $this->validateUpdateReportData($data, $id);

        $report->fill($data);
        $report->save();

        return $report->refresh();
    }

    public function deleteReport(string $id): bool
    {
        return (bool) $this->getReportById($id)->delete();
    }

    public function deleteReportInstance(ReportModel $report): bool
    {
        return (bool) $report->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
