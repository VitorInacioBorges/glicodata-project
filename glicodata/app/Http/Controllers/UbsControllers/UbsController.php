<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\UbsRequests\UpdateUbsRequest;
use App\Models\UbsModel;
use App\Services\UbsServices\UbsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UbsController extends Controller
{
    public function __construct(
        protected UbsService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', UbsModel::class);

        $ubs = Auth::guard('keycloak')->user();

        return response()->json($ubs->isAuditAdmin()
            ? $this->service->getAllUbs($request->perPage())
            : $this->service->getAuthenticatedUbs($request->perPage(), (string) $ubs->id));
    }

    public function show(string $id): JsonResponse
    {
        $ubs = $this->service->getUbsById($id);
        Gate::authorize('view', $ubs);

        return response()->json($ubs);
    }

    public function update(UpdateUbsRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getUbsById($id));

        return response()->json($this->service->updateUbs($id, $request->validated()));
    }
}
