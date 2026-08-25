<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequests\ExportReportsRequest;
use App\Http\Requests\ReportRequests\StoreReportRequest;
use App\Http\Requests\ReportRequests\UpdateReportRequest;
use App\Models\AssessmentModel;
use App\Models\ReportModel;
use App\Services\ReportServices\ReportService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportWebController extends Controller
{
    public function __construct(
        private readonly ReportService $service,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ReportModel::class);

        return view('ubs.reports.index', [
            'reports' => $this->service->getReportsForUbs(20, $this->tenant->ubsId($request->user())),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('viewAny', ReportModel::class);
        $assessments = AssessmentModel::query()
            ->where('ubs_id', $this->tenant->ubsId($request->user()))
            ->where('status', AssessmentStatus::Completed)
            ->whereDoesntHave('report')
            ->with(['patient', 'risk'])
            ->latest('completed_at')
            ->get();

        return view('ubs.reports.create', compact('assessments'));
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $assessmentId = (string) $request->validated('assessment_id');
        Gate::authorize('create', [ReportModel::class, $assessmentId]);
        $report = $this->service->createReport($request->validated());

        return redirect()->route('ubs.reports.show', $report)->with('status', 'Relatório criado com sucesso.');
    }

    public function show(string $id): View
    {
        $report = $this->service->getReportById($id);
        Gate::authorize('view', $report);

        return view('ubs.reports.show', compact('report'));
    }

    public function edit(string $id): View
    {
        $report = $this->service->getReportById($id);
        Gate::authorize('update', $report);

        return view('ubs.reports.edit', compact('report'));
    }

    public function update(UpdateReportRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getReportById($id));
        $report = $this->service->updateReport($id, $request->validated());

        return redirect()->route('ubs.reports.show', $report)->with('status', 'Relatório atualizado com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('delete', $this->service->getReportById($id));
        $this->service->deleteReport($id);

        return redirect()->route('ubs.reports.index')->with('status', 'Relatório removido com sucesso.');
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
}
