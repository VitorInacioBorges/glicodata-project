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
        return $this->ownsRecord($authenticatedUbs, $ubs->id);
    }

    public function create(UbsModel $ubs): bool
    {
        return false;
    }

    public function update(UbsModel $authenticatedUbs, UbsModel $ubs): bool
    {
        return $this->ownsRecord($authenticatedUbs, $ubs->id);
    }

    public function delete(UbsModel $authenticatedUbs, UbsModel $ubs): bool
    {
        return $this->ownsRecord($authenticatedUbs, $ubs->id);
    }

    private function ownsRecord(UbsModel $ubs, ?string $ubsId): bool
    {
        return $this->isActive($ubs) && $ubsId !== null && hash_equals((string) $ubs->id, $ubsId);
    }

    private function isActive(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }
}
