<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequests\CompleteAssessmentRequest;
use App\Http\Requests\AssessmentRequests\StoreAssessmentRequest;
use App\Http\Requests\AssessmentRequests\UpdateAssessmentRequest;
use App\Models\AssessmentModel;
use App\Models\PatientModel;
use App\Models\ProfessionalModel;
use App\Services\AssessmentServices\AssessmentService;
use App\Services\QuestionnaireServices\QuestionnaireService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssessmentWebController extends Controller
{
    public function __construct(
        private readonly AssessmentService $service,
        private readonly QuestionnaireService $questionnaires,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AssessmentModel::class);

        return view('ubs.assessments.index', [
            'assessments' => $this->service->getAssessmentsForUbs(20, $this->tenant->ubsId($request->user())),
        ]);
    }

    public function create(Request $request): View
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [AssessmentModel::class, $ubsId]);

        return view('ubs.assessments.create', [
            'patients' => PatientModel::query()->where('ubs_id', $ubsId)->orderBy('first_name')->get(),
            'professionals' => ProfessionalModel::query()
                ->where('ubs_id', $ubsId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->orderBy('specialty')
                ->limit(20)
                ->get(),
            'questionnaireVersion' => $this->questionnaires->currentPublished(),
        ]);
    }

    public function store(StoreAssessmentRequest $request): RedirectResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [AssessmentModel::class, $ubsId]);
        $assessment = $this->service->createAssessment([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ]);

        return redirect()->route('ubs.assessments.edit', $assessment)->with('status', 'Anamnese iniciada. Preencha as respostas para concluir.');
    }

    public function show(string $id): View
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('view', $assessment);

        return view('ubs.assessments.show', compact('assessment'));
    }

    public function edit(string $id): View|RedirectResponse
    {
        $assessment = $this->service->getAssessmentById($id);
        Gate::authorize('update', $assessment);

        if ($assessment->status === AssessmentStatus::Completed) {
            return redirect()->route('ubs.assessments.show', $assessment);
        }

        return view('ubs.assessments.edit', compact('assessment'));
    }

    public function update(UpdateAssessmentRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getAssessmentById($id));
        $assessment = $this->service->updateAssessment($id, $request->validated());

        return redirect()->route('ubs.assessments.edit', $assessment)->with('status', 'Rascunho salvo com sucesso.');
    }

    public function complete(CompleteAssessmentRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getAssessmentById($id));
        $assessment = $this->service->completeAssessment($id, $request->validated());

        return redirect()->route('ubs.assessments.show', $assessment)->with('status', 'Anamnese concluída e risco calculado no servidor.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('delete', $this->service->getAssessmentById($id));
        $this->service->deleteAssessment($id);

        return redirect()->route('ubs.assessments.index')->with('status', 'Anamnese removida com sucesso.');
    }
}
