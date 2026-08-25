<?php

namespace App\Http\Controllers\ReportControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\ReportRequests\ExportReportsRequest;
use App\Http\Requests\ReportRequests\StoreReportRequest;
use App\Http\Requests\ReportRequests\UpdateReportRequest;
use App\Models\ReportModel;
use App\Services\ReportServices\ReportService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $service,
        protected TenantContext $tenant,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', ReportModel::class);

        return response()->json($this->service->getReportsForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
        ));
    }

    public function export(ExportReportsRequest $request): JsonResponse|Response
    {
        Gate::authorize('export', ReportModel::class);
        $summary = $this->service->getAnonymizedSummaryForUbs($this->tenant->ubsId($request->user()));

        if ($request->validated('format', 'csv') === 'json') {
            return response()->json(['data' => $summary]);
        }

        return response($this->service->toAnonymizedCsv($summary), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="glicodata-relatorios-anonimizados.csv"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $report = $this->service->getReportById($id);
        Gate::authorize('view', $report);

        return response()->json($report);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $data = $request->validated();
        Gate::authorize('create', [ReportModel::class, $data['assessment_id']]);

        return response()->json($this->service->createReport($data), 201);
    }

    public function update(UpdateReportRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getReportById($id));

        return response()->json($this->service->updateReport($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getReportById($id));
        $this->service->deleteReport($id);

        return response()->json(null, 204);
    }
}
