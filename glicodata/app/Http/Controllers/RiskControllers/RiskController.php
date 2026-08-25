<?php

namespace App\Http\Controllers\RiskControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Resources\RiskResource;
use App\Models\RiskModel;
use App\Services\RiskServices\RiskService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RiskController extends Controller
{
    public function __construct(protected RiskService $service, protected TenantContext $tenant) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', RiskModel::class);
        $collection = RiskResource::collection($this->service->getRisksForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
        ));

        return response()->json($collection->response()->getData(true));
    }

    public function show(string $id): JsonResponse
    {
        $risk = $this->service->getRiskById($id);
        Gate::authorize('view', $risk);

        return response()->json(RiskResource::make($risk));
    }
}
