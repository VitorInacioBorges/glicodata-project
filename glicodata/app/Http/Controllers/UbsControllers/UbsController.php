<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Requests\UbsRequests\StoreUbsRequest;
use App\Http\Requests\UbsRequests\UpdateUbsRequest;
use App\Http\Resources\UbsResource;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Services\UbsServices\UbsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UbsController extends Controller
{
    public function __construct(
        protected UbsService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', UbsModel::class);

        $account = $request->user();
        abort_unless($account instanceof UbsModel || $account instanceof AdministratorModel, 403);

        $collection = UbsResource::collection($account instanceof AdministratorModel
            ? $this->service->getAllUbs($request->perPage())
            : $this->service->getAuthenticatedUbs($request->perPage(), (string) $account->id));

        return response()->json($collection->response()->getData(true));
    }

    public function show(string $id): JsonResponse
    {
        $ubs = $this->service->getUbsById($id);
        Gate::authorize('view', $ubs);

        return response()->json(UbsResource::make($ubs));
    }

    public function store(StoreUbsRequest $request): JsonResponse
    {
        Gate::authorize('create', UbsModel::class);
        $administrator = $request->user();
        abort_unless($administrator instanceof AdministratorModel, 403);

        return response()->json(
            UbsResource::make($this->service->createUbs($request->validated(), $administrator)),
            201,
        );
    }

    public function update(UpdateUbsRequest $request, string $id): JsonResponse
    {
        Gate::authorize('update', $this->service->getUbsById($id));

        return response()->json(UbsResource::make($this->service->updateUbs($id, $request->validated())));
    }
}
