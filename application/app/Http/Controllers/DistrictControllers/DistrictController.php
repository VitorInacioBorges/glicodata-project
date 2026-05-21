<?php

namespace App\Http\Controllers\DistrictControllers;

use App\Http\Controllers\Controller;
use App\Models\DistrictModel;
use App\Services\DistrictServices\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DistrictController extends Controller
{
    public function __construct(
        protected DistrictService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', DistrictModel::class);

        return response()->json($this->service->getAllDistricts((int) $request->query('per_page', 20)));
    }

    public function show(string $id): JsonResponse
    {
        $district = $this->service->getDistrictById($id);
        Gate::authorize('view', $district);

        return response()->json($district);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', DistrictModel::class);

        return response()->json($this->service->createDistrict($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getDistrictById($id));

        return response()->json($this->service->updateDistrict($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getDistrictById($id));
        $this->service->deleteDistrict($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getDistrictById($id));
        $this->service->deleteDistrict($id);

        return response()->json(null, 204);
    }
}
