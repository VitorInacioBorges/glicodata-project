<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAllUsers());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->service->getUser($id));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->service->createUser($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json($this->service->updateUser($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->deleteUser($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $this->service->deleteUser($id);

        return response()->json(null, 204);
    }
}
