<?php

namespace App\Services\UserServices;

use App\Enums\ProfessionalCouncil;
use App\Enums\UserRole;
use App\Models\UserModel;
use App\Repositories\UserRepositories\UserRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $data = $this->validatedProfessionalData($data);

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
        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        }
        $data = $this->validatedProfessionalData($data, $user);

        return DB::transaction(function () use ($user, $data): UserModel {
            $before = $user->toArray();
            $mustRevokeAccess = array_key_exists('password', $data)
                || (array_key_exists('is_active', $data) && ! $data['is_active']);
            $user->fill($data)->save();
            if ($mustRevokeAccess) {
                $user->tokens()->delete();
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
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
            $user->tokens()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validatedProfessionalData(array $data, ?UserModel $current = null): array
    {
        $candidate = array_merge(
            $current?->only(['role', 'council_type', 'council_number', 'council_uf', 'specialty']) ?? [],
            $data,
        );
        $role = $candidate['role'] instanceof UserRole
            ? $candidate['role']
            : UserRole::tryFrom((string) ($candidate['role'] ?? ''));

        if ($role === UserRole::Admin) {
            foreach (['council_type', 'council_number', 'council_uf', 'specialty'] as $field) {
                if (($candidate[$field] ?? null) !== null && $candidate[$field] !== '') {
                    throw ValidationException::withMessages([
                        $field => ['Perfis administrativos da UBS não possuem registro em conselho profissional.'],
                    ]);
                }
            }

            return [
                ...$data,
                'council_type' => null,
                'council_number' => null,
                'council_uf' => null,
                'specialty' => null,
            ];
        }

        $required = ['council_type', 'council_number', 'council_uf', 'specialty'];
        $errors = [];
        foreach ($required as $field) {
            if (($candidate[$field] ?? null) === null || $candidate[$field] === '') {
                $errors[$field][] = 'Este campo é obrigatório para profissionais de saúde.';
            }
        }

        $council = $candidate['council_type'] instanceof ProfessionalCouncil
            ? $candidate['council_type']
            : ProfessionalCouncil::tryFrom((string) ($candidate['council_type'] ?? ''));
        if ($council === null) {
            $errors['council_type'][] = 'Informe CRM ou COREN.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
