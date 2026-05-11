<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $service,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAllReports());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->service->getReport($id));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->service->createReport($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json($this->service->updateReport($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->deleteReport($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $this->service->deleteReport($id);

        return response()->json(null, 204);
    }
}
