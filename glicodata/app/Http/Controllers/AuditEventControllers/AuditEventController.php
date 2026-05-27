<?php

namespace App\Http\Controllers\AuditEventControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditEventRequests\RedactAuditEventRequest;
use App\Http\Requests\CommonRequests\PaginationRequest;
use App\Models\AuditEventModel;
use App\Models\UbsModel;
use App\Services\AuditEventServices\AuditEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuditEventController extends Controller
{
    public function __construct(
        protected AuditEventService $service,
    ) {}

    public function index(PaginationRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', AuditEventModel::class);

        return response()->json($this->service->getAuditEventsForActor(
            $request->perPage(),
            $this->authenticatedUbs(),
        ));
    }

    public function show(string $id): JsonResponse
    {
        $event = $this->service->getAuditEventById($id);
        Gate::authorize('view', $event);

        return response()->json($event);
    }

    public function redact(RedactAuditEventRequest $request, string $id): JsonResponse
    {
        $event = $this->service->getAuditEventById($id);
        Gate::authorize('redact', $event);

        return response()->json($this->service->redactAuditEvent(
            $id,
            (string) $request->validated('reason'),
            $this->authenticatedUbs(),
        ));
    }

    private function authenticatedUbs(): UbsModel
    {
        /** @var UbsModel $ubs */
        $ubs = Auth::guard('keycloak')->user();

        return $ubs;
    }
}
