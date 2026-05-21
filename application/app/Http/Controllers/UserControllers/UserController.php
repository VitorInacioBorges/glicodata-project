<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Services\UserServices\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UserModel::class);

        return response()->json($this->service->getUsersForUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->service->getUserById($id);
        Gate::authorize('view', $user);

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', [UserModel::class, $request->input('ubs_id')]);

        return response()->json($this->service->createUser($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getUserById($id));

        return response()->json($this->service->updateUser($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getUserById($id));
        $this->service->deleteUser($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getUserById($id));
        $this->service->deleteUser($id);

        return response()->json(null, 204);
    }
}
