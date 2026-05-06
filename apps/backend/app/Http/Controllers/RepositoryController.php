<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class RepositoryController extends Controller
{
    public function __construct(
        protected RepositoryInterface $repository,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->repository->findAll());
    }

    public function show(string $id): JsonResponse
    {
        $record = $this->repository->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        return response()->json($record);
    }

    public function store(Request $request): JsonResponse
    {
        $record = $this->repository->create($request->all());

        return response()->json($record, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $record = $this->repository->update($id, $request->all());

        if ($record === null) {
            return $this->notFound();
        }

        return response()->json($record);
    }

    public function destroy(string $id): JsonResponse
    {
        if (! $this->repository->delete($id)) {
            return $this->notFound();
        }

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $record = $this->repository->find($id);

        if (! $record instanceof Model) {
            return $this->notFound();
        }

        $this->repository->deleteSelf($record);

        return response()->json(null, 204);
    }

    protected function notFound(): JsonResponse
    {
        return response()->json([
            'message' => 'Registro nao encontrado.',
        ], 404);
    }
}
