<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\UserRequests\StoreUserRequest;
use App\Http\Requests\UserRequests\UpdateUserRequest;
use App\Models\UserModel;
use App\Services\UserServices\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', UserModel::class);

        return response()->json($this->service->getUsersForUbs(
            $request->perPage(),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->service->getUserById($id);
        Gate::authorize('view', $user);

        return response()->json($user);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $ubsId = (string) Auth::guard('keycloak')->id();
        Gate::authorize('create', [UserModel::class, $ubsId]);

        return response()->json($this->service->createUser([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ]), 201);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getUserById($id));

        return response()->json($this->service->updateUser($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getUserById($id));
        $this->service->deleteUser($id);

        return response()->json(null, 204);
    }
}
