<?php

namespace App\Services;

use App\Models\UbsModel;
use App\Repositories\UbsRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UbsService
{
    use ValidateUtils;

    public function __construct(
        protected UbsRepository $repository,
    ) {
    }

    public function getAllUbs(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateUbs($this->normalizePerPage($perPage));
    }

    public function getUbsById(string $id): UbsModel
    {
        $this->validateId($id);

        $ubs = $this->repository->findUbsById($id);

        if ($ubs === null) {
            throw (new ModelNotFoundException())->setModel(UbsModel::class, [$id]);
        }

        return $ubs;
    }

    public function getUbsByEmail(string $email): UbsModel
    {
        $this->validateEmail($email);

        $ubs = $this->repository->findUbsByEmail($email);

        if ($ubs === null) {
            throw (new ModelNotFoundException())->setModel(UbsModel::class, [$email]);
        }

        return $ubs;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUbs(array $data): UbsModel
    {
        if (isset($data['email']) && is_string($data['email'])) {
            $this->validateEmail($data['email']);
        }

        $this->validateCreateUbsData($data);

        return $this->repository->createUbs($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateUbs(string $id, array $data): UbsModel
    {
        $ubs = $this->getUbsById($id);

        if (isset($data['email']) && is_string($data['email'])) {
            $this->validateEmail($data['email']);
        }

        $this->validateUpdateUbsData($data, $id);

        $ubs->fill($data);
        $ubs->save();

        return $ubs->refresh();
    }

    public function deleteUbs(string $id): bool
    {
        return (bool) $this->getUbsById($id)->delete();
    }

    public function deleteUbsInstance(UbsModel $ubs): bool
    {
        return (bool) $ubs->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
