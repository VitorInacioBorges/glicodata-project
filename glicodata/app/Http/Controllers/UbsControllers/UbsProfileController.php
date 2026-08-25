<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UbsRequests\UpdateUbsRequest;
use App\Models\UbsModel;
use App\Services\DistrictServices\DistrictService;
use App\Services\UbsServices\UbsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UbsProfileController extends Controller
{
    public function __construct(
        protected UbsService $service,
        protected DistrictService $districtService,
    ) {}

    public function edit(Request $request): View
    {
        $ubs = $request->user();
        abort_unless($ubs instanceof UbsModel, 403);
        Gate::authorize('update', $ubs);

        return view('ubs.profile.edit', [
            'ubs' => $ubs,
            'districts' => $this->districtService->getDistrictOptions(),
        ]);
    }

    public function update(UpdateUbsRequest $request): RedirectResponse
    {
        $ubs = $request->user();
        abort_unless($ubs instanceof UbsModel, 403);
        Gate::authorize('update', $ubs);
        $this->service->updateUbs((string) $ubs->id, $request->validated());

        return redirect()
            ->route('ubs.profile.edit')
            ->with('status', 'Perfil da UBS atualizado com sucesso.');
    }
}
