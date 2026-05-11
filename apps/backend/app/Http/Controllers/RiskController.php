<?php

namespace App\Http\Controllers;

use App\Services\RiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function __construct(
        protected RiskService $service,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAllRisks());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->service->getRisk($id));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->service->createRisk($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json($this->service->updateRisk($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->deleteRisk($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $this->service->deleteRisk($id);

        return response()->json(null, 204);
    }
}
