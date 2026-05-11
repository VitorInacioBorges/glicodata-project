<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $service,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAllPatients());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->service->getPatient($id));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->service->createPatient($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json($this->service->updatePatient($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }
}
