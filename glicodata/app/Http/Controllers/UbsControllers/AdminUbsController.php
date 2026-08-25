<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UbsRequests\AdminUbsIndexRequest;
use App\Http\Requests\UbsRequests\UpdateUbsRequest;
use App\Models\UbsModel;
use App\Services\DistrictServices\DistrictService;
use App\Services\UbsServices\UbsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AdminUbsController extends Controller
{
    public function __construct(
        protected UbsService $service,
        protected DistrictService $districtService,
    ) {}

    public function index(AdminUbsIndexRequest $request): View
    {
        Gate::authorize('viewAny', UbsModel::class);

        return view('admin.ubs.index', [
            'ubsCollection' => $this->service->getAllUbs(20, $request->search(), $request->active()),
        ]);
    }

    public function edit(string $id): View
    {
        $ubs = $this->service->getUbsById($id);
        Gate::authorize('update', $ubs);

        return view('admin.ubs.edit', [
            'ubs' => $ubs,
            'districts' => $this->districtService->getDistrictOptions(),
        ]);
    }

    public function update(UpdateUbsRequest $request, string $id): RedirectResponse
    {
        Gate::authorize('update', $this->service->getUbsById($id));
        $this->service->updateUbs($id, $request->validated());

        return redirect()
            ->route('admin.ubs.edit', $id)
            ->with('status', 'UBS atualizada com sucesso.');
    }
}
