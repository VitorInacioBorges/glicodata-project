<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Models\UbsModel;
use App\Services\UbsServices\UbsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UbsController extends Controller
{
    public function __construct(
        protected UbsService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UbsModel::class);

        return response()->json($this->service->getAuthenticatedUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $ubs = $this->service->getUbsById($id);
        Gate::authorize('view', $ubs);

        return response()->json($ubs);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', UbsModel::class);

        return response()->json($this->service->createUbs($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getUbsById($id));

        return response()->json($this->service->updateUbs($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getUbsById($id));
        $this->service->deleteUbs($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getUbsById($id));
        $this->service->deleteUbs($id);

        return response()->json(null, 204);
    }
}
