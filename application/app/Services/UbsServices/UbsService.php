<?php

namespace App\Services\UbsServices;

use App\Models\UbsModel;
use App\Repositories\UbsRepositories\UbsRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UbsService
{
    use ValidateUtils;

    public function __construct(
        protected UbsRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getAllUbs(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateUbs($this->normalizePerPage($perPage));
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

    public function getUbsByEmail(string $email): UbsModel
    {
        $email = $this->normalizeEmail($email);
        $this->validateEmail($email);

        $ubs = $this->repository->findUbsByEmail($email);

        if ($ubs === null) {
            throw (new ModelNotFoundException)->setModel(UbsModel::class, [$email]);
        }

        return $ubs;
    }

    public function getUbsByKeycloakId(string $keycloakId): UbsModel
    {
        $ubs = $this->repository->findUbsByKeycloakId($keycloakId);

        if ($ubs === null) {
            throw (new ModelNotFoundException)->setModel(UbsModel::class, [$keycloakId]);
        }

        return $ubs;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUbs(string $id, array $data): UbsModel
    {
        $ubs = $this->getUbsById($id);
        $this->ensureOperationalIfActive($ubs, $data);

        return DB::transaction(function () use ($ubs, $data): UbsModel {
            $before = $ubs->toArray();
            $ubs->fill($data)->save();
            $ubs = $ubs->refresh();

            $this->auditService->record('update', $ubs, (string) $ubs->id, $before, $ubs->toArray());

            return $ubs;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureOperationalIfActive(UbsModel $ubs, array $data): void
    {
        if (! (bool) ($data['is_active'] ?? $ubs->is_active)) {
            return;
        }

        $email = Str::lower((string) ($data['email'] ?? $ubs->email));
        $phone = Str::lower((string) ($data['phone'] ?? $ubs->phone));
        $address = Str::lower((string) ($data['address'] ?? $ubs->address));

        if (str_ends_with($email, '@seed.local')) {
            throw ValidationException::withMessages([
                'email' => ['Uma UBS ativa deve possuir email institucional confirmado.'],
            ]);
        }

        if (in_array($phone, ['a validar', 'nao informado', 'não informado'], true)) {
            throw ValidationException::withMessages([
                'phone' => ['Uma UBS ativa deve possuir telefone confirmado.'],
            ]);
        }

        if ($address === 'a validar') {
            throw ValidationException::withMessages([
                'address' => ['Uma UBS ativa deve possuir endereco confirmado.'],
            ]);
        }
    }
}
