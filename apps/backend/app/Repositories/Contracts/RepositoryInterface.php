<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function findAll(): Collection;

    public function find(string $id): ?Model;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Model;

    public function delete(string $id): bool;

    public function deleteSelf(Model $model): bool;
}
