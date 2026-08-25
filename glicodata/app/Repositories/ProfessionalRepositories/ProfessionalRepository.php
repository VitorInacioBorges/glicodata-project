<?php

namespace App\Repositories\ProfessionalRepositories;

use App\Models\ProfessionalModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProfessionalRepository
{
    public function __construct(protected ProfessionalModel $model) {}

    public function paginateForUbs(int $perPage, string $ubsId, ?string $search = null): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('ubs_id', $ubsId)
            ->when($search, fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->whereRaw('LOWER(first_name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(specialty) LIKE ?', ['%'.mb_strtolower($search).'%']);
            }))
            ->withCount('assessments')
            ->orderBy('first_name')
            ->orderBy('specialty')
            ->paginate($perPage);
    }

    /** @return Collection<int, ProfessionalModel> */
    public function searchActiveForUbs(string $ubsId, string $search, int $limit = 20): Collection
    {
        return $this->model->newQuery()
            ->where('ubs_id', $ubsId)
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->whereRaw('LOWER(first_name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(specialty) LIKE ?', ['%'.mb_strtolower($search).'%']);
            }))
            ->orderBy('first_name')
            ->orderBy('specialty')
            ->limit($limit)
            ->get();
    }

    public function findById(string $id): ?ProfessionalModel
    {
        return $this->model->newQuery()->with(['assessments.patient', 'assessments.risk'])->find($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ProfessionalModel
    {
        return $this->model->newQuery()->create($data);
    }
}
