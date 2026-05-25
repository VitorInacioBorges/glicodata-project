<?php

namespace App\Services\DistrictServices;

use App\Models\DistrictModel;
use App\Repositories\DistrictRepositories\DistrictRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DistrictService
{
    use ValidateUtils;

    public function __construct(
        protected DistrictRepository $repository,
    ) {}

    public function getAllDistricts(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateDistricts($this->normalizePerPage($perPage));
    }

    public function getDistrictById(string $id): DistrictModel
    {
        $this->validateId($id);

        $district = $this->repository->findDistrictById($id);

        if ($district === null) {
            throw (new ModelNotFoundException)->setModel(DistrictModel::class, [$id]);
        }

        return $district;
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
