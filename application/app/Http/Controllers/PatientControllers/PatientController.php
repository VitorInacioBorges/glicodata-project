<?php

namespace App\Http\Controllers\PatientControllers;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Services\PatientServices\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PatientModel::class);

        return response()->json($this->service->getPatientsForUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $patient = $this->service->getPatientById($id);
        Gate::authorize('view', $patient);

        return response()->json($patient);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', [PatientModel::class, $request->input('ubs_id')]);

        return response()->json($this->service->createPatient($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getPatientById($id));

        return response()->json($this->service->updatePatient($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getPatientById($id));
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getPatientById($id));
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }
}
