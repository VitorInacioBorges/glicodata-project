<?php

namespace App\Http\Controllers\AssessmentControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequests\CompleteAssessmentRequest;
use App\Http\Requests\AssessmentRequests\StoreAssessmentRequest;
use App\Http\Requests\AssessmentRequests\UpdateAssessmentRequest;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Models\AssessmentModel;
use App\Services\AssessmentServices\AssessmentService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentService $service,
        protected TenantContext $tenant,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', AssessmentModel::class);

        return response()->json($this->service->getAssessmentsForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
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
        $user = $this->tenant->user($request->user());
        $ubsId = (string) $user->ubs_id;
        Gate::authorize('create', [AssessmentModel::class, $ubsId]);

        return response()->json($this->service->createAssessment([
            ...$request->validated(),
            'ubs_id' => $ubsId,
            'user_id' => (string) $user->id,
        ]), 201);
    }

    public function complete(CompleteAssessmentRequest $request, string $id): JsonResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('complete', $assessment);

        return response()->json($this->service->completeAssessment($id, $request->validated()));
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
