<?php

namespace App\Policies\ReportPolicies;

use App\Models\AssessmentModel;
use App\Models\ReportModel;
use App\Models\UserModel;

class ReportPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return $this->isActive($user);
    }

    public function view(UserModel $user, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($user, $report->assessment_id);
    }

    public function create(UserModel $user, mixed $assessmentId = null): bool
    {
        return $this->assessmentBelongsToUbs($user, is_string($assessmentId) ? $assessmentId : null);
    }

    public function update(UserModel $user, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($user, $report->assessment_id);
    }

    public function delete(UserModel $user, ReportModel $report): bool
    {
        return $this->assessmentBelongsToUbs($user, $report->assessment_id);
    }

    public function export(UserModel $user): bool
    {
        return $this->isActive($user);
    }

    private function assessmentBelongsToUbs(UserModel $user, ?string $assessmentId): bool
    {
        if (! $this->isActive($user) || $assessmentId === null) {
            return false;
        }

        return AssessmentModel::query()
            ->whereKey($assessmentId)
            ->where('ubs_id', $user->ubs_id)
            ->exists();
    }

    private function isActive(UserModel $user): bool
    {
        return $user->hasActiveAccountContext();
    }
}
