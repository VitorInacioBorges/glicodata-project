<?php

namespace App\Services;

use App\Models\RiskModel;
use App\Repositories\RiskRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RiskService
{
    use ValidateUtils;

    public function __construct(
        protected RiskRepository $repository,
    ) {
    }

    public function getAllRisks(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateRisks($this->normalizePerPage($perPage));
    }

    public function getRiskById(string $id): RiskModel
    {
        $this->validateId($id);

        $risk = $this->repository->findRiskById($id);

        if ($risk === null) {
            throw (new ModelNotFoundException())->setModel(RiskModel::class, [$id]);
        }

        return $risk;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRisk(array $data): RiskModel
    {
        return $this->repository->createRisk($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateRisk(string $id, array $data): RiskModel
    {
        $risk = $this->getRiskById($id);
        $risk->fill($data);
        $risk->save();

        return $risk->refresh();
    }

    public function deleteRisk(string $id): bool
    {
        return (bool) $this->getRiskById($id)->delete();
    }

    public function deleteRiskInstance(RiskModel $risk): bool
    {
        return (bool) $risk->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
