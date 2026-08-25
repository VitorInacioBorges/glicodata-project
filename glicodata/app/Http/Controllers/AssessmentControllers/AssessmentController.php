<?php

namespace App\Http\Controllers\AssessmentControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequests\CompleteAssessmentRequest;
use App\Http\Requests\AssessmentRequests\StoreAssessmentRequest;
use App\Http\Requests\AssessmentRequests\UpdateAssessmentRequest;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Resources\AssessmentResource;
use App\Models\AssessmentModel;
use App\Services\AssessmentServices\AssessmentService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    public function __construct(protected AssessmentService $service, protected TenantContext $tenant) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', AssessmentModel::class);
        $collection = AssessmentResource::collection($this->service->getAssessmentsForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
        ));

        return response()->json($collection->response()->getData(true));
    }

    public function show(string $id): JsonResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('view', $assessment);

        return response()->json(AssessmentResource::make($assessment));
    }

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [AssessmentModel::class, $ubsId]);

        return response()->json(AssessmentResource::make($this->service->createAssessment([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ])), 201);
    }

    public function complete(CompleteAssessmentRequest $request, string $id): JsonResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('update', $assessment);

        return response()->json(AssessmentResource::make($this->service->completeAssessment($id, $request->validated())));
    }

    public function update(UpdateAssessmentRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getAssessmentById($id));

        return response()->json(AssessmentResource::make($this->service->updateAssessment($id, $request->validated())));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getAssessmentById($id));
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }
}
