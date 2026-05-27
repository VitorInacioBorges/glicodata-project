<?php

namespace App\Http\Controllers\PatientControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\PatientRequests\StorePatientRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Models\PatientModel;
use App\Services\PatientServices\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', PatientModel::class);

        return response()->json($this->service->getPatientsForUbs(
            $request->perPage(),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $patient = $this->service->getPatientById($id);
        Gate::authorize('view', $patient);

        return response()->json($patient);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $ubsId = (string) Auth::guard('keycloak')->id();
        Gate::authorize('create', [PatientModel::class, $ubsId]);

        return response()->json($this->service->createPatient([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ]), 201);
    }

    public function update(UpdatePatientRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getPatientById($id));

        return response()->json($this->service->updatePatient($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getPatientById($id));
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }
}
