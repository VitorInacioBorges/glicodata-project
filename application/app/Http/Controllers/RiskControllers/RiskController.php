<?php

namespace App\Http\Controllers\RiskControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\RiskRequests\StoreRiskRequest;
use App\Http\Requests\RiskRequests\UpdateRiskRequest;
use App\Models\RiskModel;
use App\Services\RiskServices\RiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RiskController extends Controller
{
    public function __construct(
        protected RiskService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', RiskModel::class);

        return response()->json($this->service->getRisksForUbs(
            $request->perPage(),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $risk = $this->service->getRiskById($id);
        Gate::authorize('view', $risk);

        return response()->json($risk);
    }

    public function store(StoreRiskRequest $request): JsonResponse
    {
        $data = $request->validated();
        Gate::authorize('create', [RiskModel::class, $data['assessment_id']]);

        return response()->json($this->service->createRisk($data), 201);
    }

    public function update(UpdateRiskRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getRiskById($id));

        return response()->json($this->service->updateRisk($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getRiskById($id));
        $this->service->deleteRisk($id);

        return response()->json(null, 204);
    }
}
