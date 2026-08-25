<?php

namespace App\Http\Controllers\QuestionnaireControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionnaireVersionResource;
use App\Models\QuestionnaireVersionModel;
use App\Services\QuestionnaireServices\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class QuestionnaireController extends Controller
{
    public function __construct(
        private readonly QuestionnaireService $service,
    ) {}

    public function current(): JsonResponse
    {
        Gate::authorize('viewAny', QuestionnaireVersionModel::class);

        return response()->json(QuestionnaireVersionResource::make($this->service->currentPublished()));
    }

    public function show(string $id): JsonResponse
    {
        $version = $this->service->currentPublished($id);
        Gate::authorize('view', $version);

        return response()->json(QuestionnaireVersionResource::make($version));
    }
}
