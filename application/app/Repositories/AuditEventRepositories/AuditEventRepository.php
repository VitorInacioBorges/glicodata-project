<?php

namespace App\Repositories\AuditEventRepositories;

use App\Models\AuditEventModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditEventRepository
{
    public function __construct(
        protected AuditEventModel $model,
    ) {}

    public function paginateAuditEvents(int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->latest()
            ->paginate($perPage);
    }

    public function paginateAuditEventsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('owner_ubs_id', $ubsId)
            ->latest()
            ->paginate($perPage);
    }

    public function findAuditEventById(string $id): ?AuditEventModel
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAuditEvent(array $data): AuditEventModel
    {
        return $this->model->newQuery()->create($data);
    }
}
