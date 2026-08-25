<?php

namespace App\Policies\DistrictPolicies;

use App\Models\AdministratorModel;
use App\Models\DistrictModel;
use App\Models\UbsModel;

class DistrictPolicy
{
    public function viewAny(UbsModel|AdministratorModel $account): bool
    {
        return $this->isActive($account);
    }

    public function view(UbsModel|AdministratorModel $account, DistrictModel $district): bool
    {
        return $this->isActive($account);
    }

    public function create(UbsModel|AdministratorModel $account): bool
    {
        return false;
    }

    public function update(UbsModel|AdministratorModel $account, DistrictModel $district): bool
    {
        return false;
    }

    public function delete(UbsModel|AdministratorModel $account, DistrictModel $district): bool
    {
        return false;
    }

    private function isActive(UbsModel|AdministratorModel $account): bool
    {
        return (bool) $account->is_active;
    }
}
