<?php

namespace App\Repositories\UbsRepositories;

use App\Models\UbsModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UbsRepository
{
    public function __construct(
        protected UbsModel $model,
    ) {}

    public function paginateUbs(int $perPage, ?string $search = null, ?bool $active = null): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('district')
            ->when($search !== null, function (Builder $query) use ($search): void {
                $like = '%'.mb_strtolower($search).'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->whereRaw('LOWER(cnes) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like]);
                });
            })
            ->when($active !== null, fn (Builder $query): Builder => $query->where('is_active', $active))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateAuthenticatedUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereKey($ubsId)
            ->paginate($perPage);
    }

    public function findUbsById(string $id): ?UbsModel
    {
        return $this->model->newQuery()->with('district')->find($id);
    }

    public function findUbsByCnes(string $cnes): ?UbsModel
    {
        return $this->model->newQuery()
            ->where('cnes', $cnes)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUbs(array $data): UbsModel
    {
        return $this->model->newQuery()->create($data);
    }
}
