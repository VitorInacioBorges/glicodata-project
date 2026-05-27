<?php

namespace App\Repositories\RiskRepositories;

use App\Models\RiskModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiskRepository
{
    public function __construct(
        protected RiskModel $model,
    ) {
    }

    public function paginateRisks(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function paginateRisksForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('assessment', function ($query) use ($ubsId): void {
                $query->where('ubs_id', $ubsId);
            })
            ->paginate($perPage);
    }

    public function findRiskById(string $id): ?RiskModel
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRisk(array $data): RiskModel
    {
        return $this->model->newQuery()->create($data);
    }
}
