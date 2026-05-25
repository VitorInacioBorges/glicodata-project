<?php

namespace App\Services\ReportServices;

use App\Models\ReportModel;
use App\Repositories\ReportRepositories\ReportRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ReportService
{
    use ValidateUtils;

    public function __construct(
        protected ReportRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllReports(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateReports($this->normalizePerPage($perPage));
    }

    public function getReportsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateReportsForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getReportById(string $id): ReportModel
    {
        $this->validateId($id);

        $report = $this->repository->findReportById($id);

        if ($report === null) {
            throw (new ModelNotFoundException)->setModel(ReportModel::class, [$id]);
        }

        return $report;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReport(array $data): ReportModel
    {
        return DB::transaction(function () use ($data): ReportModel {
            $report = $this->repository->createReport($data);
            $this->auditService->record('create', $report, $this->ownerUbsId($report), null, $report->toArray());

            return $report;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateReport(string $id, array $data): ReportModel
    {
        $report = $this->getReportById($id);

        return DB::transaction(function () use ($report, $data): ReportModel {
            $before = $report->toArray();
            $report->fill($data)->save();
            $report = $report->refresh();

            $this->auditService->record('update', $report, $this->ownerUbsId($report), $before, $report->toArray());

            return $report;
        });
    }

    public function deleteReport(string $id): bool
    {
        return $this->deleteReportInstance($this->getReportById($id));
    }

    public function deleteReportInstance(ReportModel $report): bool
    {
        return DB::transaction(function () use ($report): bool {
            $before = $report->toArray();
            $deleted = (bool) $report->delete();

            if ($deleted) {
                $this->auditService->record('delete', $report, $this->ownerUbsId($report), $before, $report->toArray());
            }

            return $deleted;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }

    private function ownerUbsId(ReportModel $report): string
    {
        return (string) $report->assessment()->value('ubs_id');
    }
}
