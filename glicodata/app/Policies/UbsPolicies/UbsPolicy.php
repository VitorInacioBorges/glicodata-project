<?php

namespace App\Policies\UbsPolicies;

use App\Models\UbsModel;

class UbsPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return $this->isActive($ubs);
    }

    public function view(UbsModel $authenticatedUbs, UbsModel $ubs): bool
    {
        return $this->isActive($authenticatedUbs)
            && ($authenticatedUbs->isAuditAdmin() || hash_equals((string) $authenticatedUbs->id, (string) $ubs->id));
    }

    public function create(UbsModel $ubs): bool
    {
        return false;
    }

    public function update(UbsModel $authenticatedUbs, UbsModel $ubs): bool
    {
        return $this->isActive($authenticatedUbs) && $authenticatedUbs->isAuditAdmin();
    }

    public function delete(UbsModel $authenticatedUbs, UbsModel $ubs): bool
    {
        return false;
    }

    private function isActive(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }
}
