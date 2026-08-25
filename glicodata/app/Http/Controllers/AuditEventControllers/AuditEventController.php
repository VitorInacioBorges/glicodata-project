<?php

namespace App\Http\Controllers\AuditEventControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditEventRequests\RedactAuditEventRequest;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Http\Resources\AuditEventResource;
use App\Models\AdministratorModel;
use App\Models\AuditEventModel;
use App\Models\UbsModel;
use App\Services\AuditEventServices\AuditEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditEventController extends Controller
{
    public function __construct(
        protected AuditEventService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', AuditEventModel::class);

        $collection = AuditEventResource::collection($this->service->getAuditEventsForActor(
            $request->perPage(),
            $this->authenticatedAccount($request),
        ));

        return response()->json($collection->response()->getData(true));
    }

    public function show(string $id): JsonResponse
    {
        $event = $this->service->getAuditEventById($id);
        Gate::authorize('view', $event);

        return response()->json(AuditEventResource::make($event));
    }

    public function redact(RedactAuditEventRequest $request, string $id): JsonResponse
    {
        $event = $this->service->getAuditEventById($id);
        Gate::authorize('redact', $event);

        return response()->json(AuditEventResource::make($this->service->redactAuditEvent(
            $id,
            (string) $request->validated('reason'),
            $this->authenticatedAdministrator($request),
        )));
    }

    private function authenticatedAccount(Request $request): UbsModel|AdministratorModel
    {
        $account = $request->user();
        abort_unless($account instanceof UbsModel || $account instanceof AdministratorModel, 403);

        return $account;
    }

    private function authenticatedAdministrator(Request $request): AdministratorModel
    {
        $account = $request->user();
        abort_unless($account instanceof AdministratorModel, 403);

        return $account;
    }
}
