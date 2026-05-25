<?php

namespace App\Http\Controllers\DistrictControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Models\DistrictModel;
use App\Services\DistrictServices\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DistrictController extends Controller
{
    public function __construct(
        protected DistrictService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', DistrictModel::class);

        return response()->json($this->service->getAllDistricts($request->perPage()));
    }

    public function show(string $id): JsonResponse
    {
        $district = $this->service->getDistrictById($id);
        Gate::authorize('view', $district);

        return response()->json($district);
    }
}
