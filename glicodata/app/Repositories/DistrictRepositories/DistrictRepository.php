<?php

namespace App\Repositories\DistrictRepositories;

use App\Models\DistrictModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DistrictRepository
{
    public function __construct(
        protected DistrictModel $model,
    ) {}

    public function paginateDistricts(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function findDistrictById(string $id): ?DistrictModel
    {
        return $this->model->newQuery()->find($id);
    }
}
