<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(
        protected Model $model,
    ) {
    }

    /**
     * @return Collection<int, Model>
     */
    public function findAll(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function find(string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Model
    {
        $record = $this->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data);
        $record->save();

        return $record->refresh();
    }

    public function delete(string $id): bool
    {
        $record = $this->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function deleteSelf(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
