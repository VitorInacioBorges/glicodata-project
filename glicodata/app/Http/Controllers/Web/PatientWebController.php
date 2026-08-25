<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequests\StorePatientRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Models\PatientModel;
use App\Services\PatientServices\PatientService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientWebController extends Controller
{
    public function __construct(
        private readonly PatientService $service,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', PatientModel::class);

        return view('ubs.patients.index', [
            'patients' => $this->service->getPatientsForUbs(20, $this->tenant->ubsId($request->user())),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [PatientModel::class, $this->tenant->ubsId(request()->user())]);

        return view('ubs.patients.create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [PatientModel::class, $ubsId]);
        $patient = $this->service->createPatient([...$request->validated(), 'ubs_id' => $ubsId]);

        return redirect()->route('ubs.patients.show', $patient)->with('status', 'Paciente cadastrado com sucesso.');
    }

    public function show(string $id): View
    {
        $patient = $this->service->getPatientById($id);
        Gate::authorize('view', $patient);

        return view('ubs.patients.show', ['patient' => $patient]);
    }

    public function edit(string $id): View
    {
        $patient = $this->service->getPatientById($id);
        Gate::authorize('update', $patient);

        return view('ubs.patients.edit', ['patient' => $patient]);
    }

    public function update(UpdatePatientRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getPatientById($id));
        $patient = $this->service->updatePatient($id, $request->validated());

        return redirect()->route('ubs.patients.show', $patient)->with('status', 'Paciente atualizado com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('delete', $this->service->getPatientById($id));
        $this->service->deletePatient($id);

        return redirect()->route('ubs.patients.index')->with('status', 'Paciente removido com sucesso.');
    }
}
