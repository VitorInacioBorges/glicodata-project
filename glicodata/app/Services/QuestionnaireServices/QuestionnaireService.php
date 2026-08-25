<?php

namespace App\Services\QuestionnaireServices;

use App\Enums\QuestionnaireVersionStatus;
use App\Models\QuestionnaireVersionModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionnaireService
{
    public function currentPublished(?string $versionId = null): QuestionnaireVersionModel
    {
        $version = QuestionnaireVersionModel::query()
            ->with('questionnaire')
            ->where('status', QuestionnaireVersionStatus::Published)
            ->whereHas('questionnaire', fn ($query) => $query->where('is_active', true))
            ->when($versionId, fn ($query) => $query->whereKey($versionId))
            ->orderByDesc('published_at')
            ->orderByDesc('version')
            ->first();

        if ($version === null) {
            throw (new ModelNotFoundException)->setModel(QuestionnaireVersionModel::class, array_filter([$versionId]));
        }

        return $version;
    }
}
