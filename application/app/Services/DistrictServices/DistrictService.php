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
    ) {
    }

    public function getAllDistricts(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateDistricts($this->normalizePerPage($perPage));
    }

    public function getDistrictById(string $id): DistrictModel
    {
        $this->validateId($id);

        $district = $this->repository->findDistrictById($id);

        if ($district === null) {
            throw (new ModelNotFoundException())->setModel(DistrictModel::class, [$id]);
        }

        return $district;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createDistrict(array $data): DistrictModel
    {
        $this->validateCreateDistrictData($data);

        return $this->repository->createDistrict($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateDistrict(string $id, array $data): DistrictModel
    {
        $district = $this->getDistrictById($id);
        $this->validateUpdateDistrictData($data);

        $district->fill($data);
        $district->save();

        return $district->refresh();
    }

    public function deleteDistrict(string $id): bool
    {
        return (bool) $this->getDistrictById($id)->delete();
    }

    public function deleteDistrictInstance(DistrictModel $district): bool
    {
        return (bool) $district->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
