<?php

namespace App\Http\Controllers\AssessmentControllers;

use App\Http\Controllers\Controller;
use App\Models\AssessmentModel;
use App\Services\AssessmentServices\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', AssessmentModel::class);

        return response()->json($this->service->getAssessmentsForUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('view', $assessment);

        return response()->json($assessment);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', [AssessmentModel::class, $request->input('ubs_id')]);

        return response()->json($this->service->createAssessment($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getAssessmentById($id));

        return response()->json($this->service->updateAssessment($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getAssessmentById($id));
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getAssessmentById($id));
        $this->service->deleteAssessment($id);

        return response()->json(null, 204);
    }
}
