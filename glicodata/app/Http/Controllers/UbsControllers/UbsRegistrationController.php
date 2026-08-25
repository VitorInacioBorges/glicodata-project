<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UbsRequests\StoreUbsRequest;
use App\Models\AdministratorModel;
use App\Services\UbsServices\UbsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UbsRegistrationController extends Controller
{
    public function __construct(
        protected UbsService $service,
    ) {}

    public function create(): View
    {
        return view('ubs.auth.register');
    }

    public function store(StoreUbsRequest $request): RedirectResponse
    {
        $administrator = Auth::guard('admin')->user();
        $this->service->createUbs(
            $request->validated(),
            $administrator instanceof AdministratorModel ? $administrator : null,
        );

        if ($administrator instanceof AdministratorModel) {
            return redirect()
                ->route('admin.ubs.index', ['status' => 'pending'])
                ->with('status', 'UBS cadastrada como pendente. Revise os dados antes da ativação.');
        }

        return redirect()
            ->route('ubs.login')
            ->with('status', 'Cadastro recebido. Aguarde a aprovação de um administrador para acessar o sistema.');
    }
}
