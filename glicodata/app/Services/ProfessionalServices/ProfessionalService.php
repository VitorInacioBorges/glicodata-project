<?php

namespace App\Services\ProfessionalServices;

use App\Models\ProfessionalModel;
use App\Repositories\ProfessionalRepositories\ProfessionalRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProfessionalService
{
    use ValidateUtils;

    public function __construct(
        protected ProfessionalRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getForUbs(int $perPage, string $ubsId, ?string $search = null): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateForUbs(max(1, min(20, $perPage)), $ubsId, $search);
    }

    /** @return Collection<int, ProfessionalModel> */
    public function searchActive(string $ubsId, string $search): Collection
    {
        $this->validateId($ubsId);

        return $this->repository->searchActiveForUbs($ubsId, trim($search));
    }

    public function getById(string $id): ProfessionalModel
    {
        $this->validateId($id);
        $professional = $this->repository->findById($id);

        if ($professional === null) {
            throw (new ModelNotFoundException)->setModel(ProfessionalModel::class, [$id]);
        }

        return $professional;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ProfessionalModel
    {
        return DB::transaction(function () use ($data): ProfessionalModel {
            $professional = $this->repository->create($data);
            $this->auditService->record('create', $professional, (string) $professional->ubs_id, null, $professional->toArray());

            return $professional;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(string $id, array $data): ProfessionalModel
    {
        $professional = $this->getById($id);

        return DB::transaction(function () use ($professional, $data): ProfessionalModel {
            $before = $professional->toArray();
            $professional->fill($data)->save();
            $professional = $professional->refresh();
            $this->auditService->record('update', $professional, (string) $professional->ubs_id, $before, $professional->toArray());

            return $professional;
        });
    }

    public function delete(string $id): bool
    {
        $professional = $this->getById($id);

        return DB::transaction(function () use ($professional): bool {
            $before = $professional->toArray();
            $deleted = (bool) $professional->delete();
            if ($deleted) {
                $this->auditService->record('delete', $professional, (string) $professional->ubs_id, $before, $professional->toArray());
            }

            return $deleted;
        });
    }
}
