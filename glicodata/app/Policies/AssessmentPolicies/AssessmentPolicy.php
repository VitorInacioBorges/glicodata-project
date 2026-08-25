<?php

namespace App\Policies\AssessmentPolicies;

use App\Models\AssessmentModel;
use App\Models\UbsModel;

class AssessmentPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, AssessmentModel $assessment): bool
    {
        return (bool) $ubs->is_active && $assessment->ubs_id === (string) $ubs->id;
    }

    public function create(UbsModel $ubs, mixed $ubsId = null): bool
    {
        return (bool) $ubs->is_active && $ubsId === (string) $ubs->id;
    }

    public function update(UbsModel $ubs, AssessmentModel $assessment): bool
    {
        return $this->view($ubs, $assessment);
    }

    public function delete(UbsModel $ubs, AssessmentModel $assessment): bool
    {
        return $this->view($ubs, $assessment);
    }
}
