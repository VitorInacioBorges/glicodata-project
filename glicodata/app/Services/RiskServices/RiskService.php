<?php

namespace App\Services\RiskServices;

use App\Models\RiskModel;
use App\Repositories\RiskRepositories\RiskRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RiskService
{
    use ValidateUtils;

    public function __construct(
        protected RiskRepository $repository,
    ) {}

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

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
