<?php

namespace App\Policies\ReportPolicies;

use App\Models\AssessmentModel;
use App\Models\ReportModel;
use App\Models\UbsModel;

class ReportPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, ReportModel $report): bool
    {
        return (bool) $ubs->is_active && $report->assessment()->where('ubs_id', $ubs->id)->exists();
    }

    public function create(UbsModel $ubs, mixed $assessmentId = null): bool
    {
        return (bool) $ubs->is_active && is_string($assessmentId)
            && AssessmentModel::query()->whereKey($assessmentId)->where('ubs_id', $ubs->id)->exists();
    }

    public function update(UbsModel $ubs, ReportModel $report): bool
    {
        return $this->view($ubs, $report);
    }

    public function delete(UbsModel $ubs, ReportModel $report): bool
    {
        return $this->view($ubs, $report);
    }
}
