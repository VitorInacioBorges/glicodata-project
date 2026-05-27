<?php

namespace App\Repositories\UserRepositories;

use App\Models\UserModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(
        protected UserModel $model,
    ) {
    }

    public function paginateUsers(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function paginateUsersForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('ubs_id', $ubsId)
            ->paginate($perPage);
    }

    public function findUserById(string $id): ?UserModel
    {
        return $this->model->newQuery()->find($id);
    }

    public function findUserByEmail(string $email): ?UserModel
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): UserModel
    {
        return $this->model->newQuery()->create($data);
    }
}
