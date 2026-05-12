<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    use ValidateUtils;

    public function __construct(
        protected UserRepository $repository,
    ) {
    }

    public function getAllUsers(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateUsers($this->normalizePerPage($perPage));
    }

    public function getUserById(string $id): UserModel
    {
        $this->validateId($id);

        $user = $this->repository->findUserById($id);

        if ($user === null) {
            throw (new ModelNotFoundException())->setModel(UserModel::class, [$id]);
        }

        return $user;
    }

    public function getUserByEmail(string $email): UserModel
    {
        $this->validateEmail($email);

        $user = $this->repository->findUserByEmail($email);

        if ($user === null) {
            throw (new ModelNotFoundException())->setModel(UserModel::class, [$email]);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): UserModel
    {
        return $this->repository->createUser($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateUser(string $id, array $data): UserModel
    {
        $user = $this->getUserById($id);
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function deleteUser(string $id): bool
    {
        return (bool) $this->getUserById($id)->delete();
    }

    public function deleteUserInstance(UserModel $user): bool
    {
        return (bool) $user->delete();
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
