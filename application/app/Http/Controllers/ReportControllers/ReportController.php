<?php

namespace App\Http\Controllers\ReportControllers;

use App\Http\Controllers\Controller;
use App\Models\ReportModel;
use App\Services\ReportServices\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ReportModel::class);

        return response()->json($this->service->getReportsForUbs(
            (int) $request->query('per_page', 20),
            (string) Auth::guard('keycloak')->id(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $report = $this->service->getReportById($id);
        Gate::authorize('view', $report);

        return response()->json($report);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', [ReportModel::class, $request->input('assessment_id')]);

        return response()->json($this->service->createReport($request->all()), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getReportById($id));

        return response()->json($this->service->updateReport($id, $request->all()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getReportById($id));
        $this->service->deleteReport($id);

        return response()->json(null, 204);
    }

    public function deleteSelf(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getReportById($id));
        $this->service->deleteReport($id);

        return response()->json(null, 204);
    }
}
