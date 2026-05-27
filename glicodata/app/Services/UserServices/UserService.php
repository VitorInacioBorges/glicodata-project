<?php

namespace App\Services\UserServices;

use App\Models\UserModel;
use App\Repositories\UserRepositories\UserRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UserService
{
    use ValidateUtils;

    public function __construct(
        protected UserRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllUsers(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateUsers($this->normalizePerPage($perPage));
    }

    public function getUsersForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateUsersForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getUserById(string $id): UserModel
    {
        $this->validateId($id);

        $user = $this->repository->findUserById($id);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(UserModel::class, [$id]);
        }

        return $user;
    }

    public function getUserByEmail(string $email): UserModel
    {
        $email = $this->normalizeEmail($email);
        $this->validateEmail($email);

        $user = $this->repository->findUserByEmail($email);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(UserModel::class, [$email]);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): UserModel
    {
        return DB::transaction(function () use ($data): UserModel {
            $user = $this->repository->createUser($data);
            $this->auditService->record('create', $user, (string) $user->ubs_id, null, $user->toArray());

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(string $id, array $data): UserModel
    {
        $user = $this->getUserById($id);

        return DB::transaction(function () use ($user, $data): UserModel {
            $before = $user->toArray();
            $user->fill($data)->save();
            $user = $user->refresh();

            $this->auditService->record('update', $user, (string) $user->ubs_id, $before, $user->toArray());

            return $user;
        });
    }

    public function deleteUser(string $id): bool
    {
        return $this->deleteUserInstance($this->getUserById($id));
    }

    public function deleteUserInstance(UserModel $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $before = $user->toArray();
            $deleted = (bool) $user->delete();

            if ($deleted) {
                $this->auditService->record('delete', $user, (string) $user->ubs_id, $before, $user->toArray());
            }

            return $deleted;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
