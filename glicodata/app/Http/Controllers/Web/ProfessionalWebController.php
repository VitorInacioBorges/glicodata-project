<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessionalRequests\StoreProfessionalRequest;
use App\Http\Requests\ProfessionalRequests\UpdateProfessionalRequest;
use App\Models\ProfessionalModel;
use App\Services\ProfessionalServices\ProfessionalService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProfessionalWebController extends Controller
{
    public function __construct(
        private readonly ProfessionalService $service,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ProfessionalModel::class);

        return view('ubs.professionals.index', [
            'professionals' => $this->service->getForUbs(
                20,
                $this->tenant->ubsId($request->user()),
                trim((string) $request->query('search')),
            ),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ProfessionalModel::class);
        $professionals = $this->service->searchActive(
            $this->tenant->ubsId($request->user()),
            mb_substr(trim((string) $request->query('q')), 0, 80),
        );

        return response()->json(['data' => $professionals->map(fn (ProfessionalModel $professional): array => [
            'id' => (string) $professional->id,
            'first_name' => $professional->first_name,
            'specialty' => $professional->specialty,
        ])->all()]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', [ProfessionalModel::class, $this->tenant->ubsId($request->user())]);

        return view('ubs.professionals.create');
    }

    public function store(StoreProfessionalRequest $request): RedirectResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [ProfessionalModel::class, $ubsId]);
        $professional = $this->service->create([...$request->validated(), 'ubs_id' => $ubsId]);

        return redirect()->route('ubs.professionals.show', $professional)->with('status', 'Profissional criado com sucesso.');
    }

    public function show(string $id): View
    {
        $professional = $this->service->getById($id);
        Gate::authorize('view', $professional);

        return view('ubs.professionals.show', compact('professional'));
    }

    public function edit(string $id): View
    {
        $professional = $this->service->getById($id);
        Gate::authorize('update', $professional);

        return view('ubs.professionals.edit', compact('professional'));
    }

    public function update(UpdateProfessionalRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getById($id));
        $professional = $this->service->update($id, $request->validated());

        return redirect()->route('ubs.professionals.show', $professional)->with('status', 'Profissional atualizado com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('delete', $this->service->getById($id));
        $this->service->delete($id);

        return redirect()->route('ubs.professionals.index')->with('status', 'Profissional removido com sucesso.');
    }
}
