<?php

namespace App\Http\Controllers\AssessmentControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequests\StoreAssessmentRequest;
use App\Http\Requests\AssessmentRequests\UpdateAssessmentRequest;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Models\AssessmentModel;
use App\Services\AssessmentServices\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', AssessmentModel::class);

        return response()->json($this->service->getAssessmentsForUbs(
            $request->perPage(),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('view', $assessment);

        return response()->json($assessment);
    }

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $ubsId = (string) Auth::guard('keycloak')->id();
        Gate::authorize('create', [AssessmentModel::class, $ubsId]);

        return response()->json($this->service->createAssessment([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ]), 201);
    }

    public function update(UpdateAssessmentRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getAssessmentById($id));

        return response()->json($this->service->updateAssessment($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getAssessmentById($id));
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }
}
