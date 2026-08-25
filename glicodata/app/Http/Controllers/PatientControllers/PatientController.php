<?php

namespace App\Http\Controllers\PatientControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\PatientRequests\StorePatientRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\PatientModel;
use App\Services\PatientServices\PatientService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(protected PatientService $service, protected TenantContext $tenant) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', PatientModel::class);
        $collection = PatientResource::collection($this->service->getPatientsForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
        ));

        return response()->json($collection->response()->getData(true));
    }

    public function show(string $id): JsonResponse
    {
        $patient = $this->service->getPatientById($id);
        Gate::authorize('view', $patient);

        return response()->json(PatientResource::make($patient));
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [PatientModel::class, $ubsId]);

        return response()->json(PatientResource::make($this->service->createPatient([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ])), 201);
    }

    public function update(UpdatePatientRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getPatientById($id));

        return response()->json(PatientResource::make($this->service->updatePatient($id, $request->validated())));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getPatientById($id));
        $this->service->deletePatient($id);

        return response()->json(null, 204);
    }
}
