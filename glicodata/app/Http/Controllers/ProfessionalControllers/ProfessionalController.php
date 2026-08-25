<?php

namespace App\Http\Controllers\ProfessionalControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\ProfessionalRequests\StoreProfessionalRequest;
use App\Http\Requests\ProfessionalRequests\UpdateProfessionalRequest;
use App\Http\Resources\ProfessionalResource;
use App\Models\ProfessionalModel;
use App\Services\ProfessionalServices\ProfessionalService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfessionalController extends Controller
{
    public function __construct(protected ProfessionalService $service, protected TenantContext $tenant) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', ProfessionalModel::class);
        $collection = ProfessionalResource::collection($this->service->getForUbs(
            $request->perPage(),
            $this->tenant->ubsId($request->user()),
            trim((string) $request->query('search')),
        ));

        return response()->json($collection->response()->getData(true));
    }

    public function search(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ProfessionalModel::class);
        $professionals = $this->service->searchActive(
            $this->tenant->ubsId($request->user()),
            mb_substr(trim((string) $request->query('q')), 0, 80),
        );

        return response()->json(['data' => ProfessionalResource::collection($professionals)]);
    }

    public function show(string $id): JsonResponse
    {
        $professional = $this->service->getById($id);
        Gate::authorize('view', $professional);

        return response()->json(ProfessionalResource::make($professional));
    }

    public function store(StoreProfessionalRequest $request): JsonResponse
    {
        $ubsId = $this->tenant->ubsId($request->user());
        Gate::authorize('create', [ProfessionalModel::class, $ubsId]);

        return response()->json(ProfessionalResource::make($this->service->create([
            ...$request->validated(),
            'ubs_id' => $ubsId,
        ])), 201);
    }

    public function update(UpdateProfessionalRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getById($id));

        return response()->json(ProfessionalResource::make($this->service->update($id, $request->validated())));
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $this->service->getById($id));
        $this->service->delete($id);

        return response()->json(null, 204);
    }
}
