<?php

namespace App\Http\Controllers;

use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentService $service,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAllAssessments());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->service->getAssessment($id));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->service->createAssessment($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json($this->service->updateAssessment($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }
}
