<?php

namespace App\Policies\UbsPolicies;

use App\Models\AdministratorModel;
use App\Models\UbsModel;

class UbsPolicy
{
    public function viewAny(UbsModel|AdministratorModel $account): bool
    {
        return $this->isActive($account);
    }

    public function view(UbsModel|AdministratorModel $account, UbsModel $ubs): bool
    {
        return $this->isActive($account)
            && ($account instanceof AdministratorModel || hash_equals((string) $account->id, (string) $ubs->id));
    }

    public function create(UbsModel|AdministratorModel $account): bool
    {
        return $this->isActive($account) && $account instanceof AdministratorModel;
    }

    public function update(UbsModel|AdministratorModel $account, UbsModel $ubs): bool
    {
        return $this->isActive($account)
            && ($account instanceof AdministratorModel || hash_equals((string) $account->id, (string) $ubs->id));
    }

    public function delete(UbsModel|AdministratorModel $account, UbsModel $ubs): bool
    {
        return false;
    }

    private function isActive(UbsModel|AdministratorModel $account): bool
    {
        return (bool) $account->is_active;
    }
}
