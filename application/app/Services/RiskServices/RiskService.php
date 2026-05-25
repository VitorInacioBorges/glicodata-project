<?php

namespace App\Services\RiskServices;

use App\Models\RiskModel;
use App\Repositories\RiskRepositories\RiskRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class RiskService
{
    use ValidateUtils;

    public function __construct(
        protected RiskRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllRisks(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateRisks($this->normalizePerPage($perPage));
    }

    public function getRisksForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateRisksForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getRiskById(string $id): RiskModel
    {
        $this->validateId($id);

        $risk = $this->repository->findRiskById($id);

        if ($risk === null) {
            throw (new ModelNotFoundException)->setModel(RiskModel::class, [$id]);
        }

        return $risk;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRisk(array $data): RiskModel
    {
        return DB::transaction(function () use ($data): RiskModel {
            $risk = $this->repository->createRisk($data);
            $this->auditService->record('create', $risk, $this->ownerUbsId($risk), null, $risk->toArray());

            return $risk;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRisk(string $id, array $data): RiskModel
    {
        $risk = $this->getRiskById($id);

        return DB::transaction(function () use ($risk, $data): RiskModel {
            $before = $risk->toArray();
            $risk->fill($data)->save();
            $risk = $risk->refresh();

            $this->auditService->record('update', $risk, $this->ownerUbsId($risk), $before, $risk->toArray());

            return $risk;
        });
    }

    public function deleteRisk(string $id): bool
    {
        return $this->deleteRiskInstance($this->getRiskById($id));
    }

    public function deleteRiskInstance(RiskModel $risk): bool
    {
        return DB::transaction(function () use ($risk): bool {
            $before = $risk->toArray();
            $deleted = (bool) $risk->delete();

            if ($deleted) {
                $this->auditService->record('delete', $risk, $this->ownerUbsId($risk), $before, $risk->toArray());
            }

            return $deleted;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }

    private function ownerUbsId(RiskModel $risk): string
    {
        return (string) $risk->assessment()->value('ubs_id');
    }
}
