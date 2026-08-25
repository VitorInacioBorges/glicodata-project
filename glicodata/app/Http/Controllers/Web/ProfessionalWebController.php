<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\StoreUserRequest;
use App\Http\Requests\UserRequests\UpdateUserRequest;
use App\Models\UserModel;
use App\Services\UserServices\UserService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProfessionalWebController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', UserModel::class);

        return view('ubs.professionals.index', [
            'professionals' => $this->service->getUsersForUbs(20, $this->tenant->ubsId($request->user())),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', [UserModel::class, $this->tenant->ubsId($request->user())]);

        return view('ubs.professionals.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [UserModel::class, $ubsId]);
        $professional = $this->service->createUser([...$request->validated(), 'ubs_id' => $ubsId]);

        return redirect()->route('ubs.professionals.show', $professional)->with('status', 'Conta individual criada com sucesso.');
    }

    public function show(string $id): View
    {
        $professional = $this->service->getUserById($id);
        Gate::authorize('view', $professional);

        return view('ubs.professionals.show', compact('professional'));
    }

    public function edit(string $id): View
    {
        $professional = $this->service->getUserById($id);
        Gate::authorize('update', $professional);

        return view('ubs.professionals.edit', compact('professional'));
    }

    public function update(UpdateUserRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getUserById($id));
        $professional = $this->service->updateUser($id, $request->validated());

        return redirect()->route('ubs.professionals.show', $professional)->with('status', 'Conta individual atualizada com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('delete', $this->service->getUserById($id));
        $this->service->deleteUser($id);

        return redirect()->route('ubs.professionals.index')->with('status', 'Conta individual removida com sucesso.');
    }
}
