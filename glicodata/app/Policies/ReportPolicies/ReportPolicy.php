<?php

namespace App\Policies\ReportPolicies;

use App\Models\AssessmentModel;
use App\Models\ReportModel;
use App\Models\UbsModel;

class ReportPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return $this->isActive($ubs);
    }

    public function view(UbsModel $ubs, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($ubs, $report->assessment_id);
    }

    public function create(UbsModel $ubs, mixed $assessmentId = null): bool
    {
        return $this->assessmentBelongsToUbs($ubs, is_string($assessmentId) ? $assessmentId : null);
    }

    public function update(UbsModel $ubs, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($ubs, $report->assessment_id);
    }

    public function delete(UbsModel $ubs, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($ubs, $report->assessment_id);
    }

    private function assessmentBelongsToUbs(UbsModel $ubs, ?string $assessmentId): bool
    {
        if (! $this->isActive($ubs) || $assessmentId === null) {
            return false;
        }

        return AssessmentModel::query()
            ->whereKey($assessmentId)
            ->where('ubs_id', $ubs->id)
            ->exists();
    }

    private function isActive(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }
}
