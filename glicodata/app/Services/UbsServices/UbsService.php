<?php

namespace App\Services\UbsServices;

use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use App\Repositories\UbsRepositories\UbsRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UbsService
{
    use ValidateUtils;

    public function __construct(
        protected UbsRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllUbs(int $perPage, ?string $search = null, ?bool $active = null): LengthAwarePaginator
    {
        $search = is_string($search) && trim($search) !== '' ? trim($search) : null;

        return $this->repository->paginateUbs($this->normalizePerPage($perPage), $search, $active);
    }

    public function getAuthenticatedUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateAuthenticatedUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getUbsById(string $id): UbsModel
    {
        $this->validateId($id);

        $ubs = $this->repository->findUbsById($id);

        if ($ubs === null) {
            throw (new ModelNotFoundException)->setModel(UbsModel::class, [$id]);
        }

        return $ubs;
    }

    public function getUbsByCnes(string $cnes): UbsModel
    {
        $ubs = $this->repository->findUbsByCnes($cnes);

        if ($ubs === null) {
            throw (new ModelNotFoundException)->setModel(UbsModel::class, [$cnes]);
        }

        return $ubs;
    }

    /**
     * @param  array{cnes: string, password: string}  $data
     */
    public function createUbs(array $data, ?AdministratorModel $actor = null): UbsModel
    {
        try {
            return DB::transaction(function () use ($data, $actor): UbsModel {
                $ubs = $this->repository->createUbs([
                    'cnes' => $data['cnes'],
                    'password' => $data['password'],
                    'is_active' => false,
                ]);

                $this->auditService->record(
                    $actor instanceof AdministratorModel ? 'create' : 'register',
                    $ubs,
                    (string) $ubs->id,
                    null,
                    $ubs->toArray(),
                    $actor ?? $ubs,
                );

                return $ubs->refresh();
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'cnes' => ['Este CNES já está cadastrado.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUbs(string $id, array $data): UbsModel
    {
        $ubs = $this->getUbsById($id);

        if ((bool) ($data['is_active'] ?? $ubs->is_active) && ($data['cnes'] ?? $ubs->cnes) === null) {
            throw ValidationException::withMessages([
                'cnes' => ['Defina um CNES válido antes de ativar a UBS.'],
            ]);
        }

        return DB::transaction(function () use ($ubs, $data): UbsModel {
            $before = $ubs->toArray();
            $cnesChanged = array_key_exists('cnes', $data) && $data['cnes'] !== $ubs->cnes;
            $ubs->fill($data)->save();
            $ubs = $ubs->refresh();

            if ($cnesChanged || ! $ubs->is_active) {
                $ubs->tokens()->delete();
                DB::table('sessions')->where('user_id', $ubs->id)->delete();
            }

            if (! $ubs->is_active) {
                $userIds = UserModel::withTrashed()->where('ubs_id', $ubs->id)->pluck('id');
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', UserModel::class)
                    ->whereIn('tokenable_id', $userIds)
                    ->delete();
                DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            }

            $this->auditService->record('update', $ubs, (string) $ubs->id, $before, $ubs->toArray());

            return $ubs;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
