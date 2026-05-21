<?php

namespace App\Http\Controllers\RiskControllers;

use App\Http\Controllers\Controller;
use App\Models\RiskModel;
use App\Services\RiskServices\RiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RiskController extends Controller
{
    public function __construct(
        protected RiskService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', RiskModel::class);

        return response()->json($this->service->getRisksForUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $risk = $this->service->getRiskById($id);
        Gate::authorize('view', $risk);

        return response()->json($risk);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', [RiskModel::class, $request->input('assessment_id')]);

        return response()->json($this->service->createRisk($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getRiskById($id));

        return response()->json($this->service->updateRisk($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getRiskById($id));
        $this->service->deleteRisk($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getRiskById($id));
        $this->service->deleteRisk($id);

        return response()->json(null, 204);
    }
}
