<?php

namespace App\Policies\DistrictPolicies;

use App\Models\DistrictModel;
use App\Models\UbsModel;

class DistrictPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return $this->isActive($ubs);
    }

    public function view(UbsModel $ubs, DistrictModel $district): bool
    {
        return $this->isActive($ubs);
    }

    public function create(UbsModel $ubs): bool
    {
        return false;
    }

    public function update(UbsModel $ubs, DistrictModel $district): bool
    {
        return false;
    }

    public function delete(UbsModel $ubs, DistrictModel $district): bool
    {
        return false;
    }

    private function isActive(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }
}
