<?php

namespace App\Policies\UserPolicies;

use App\Models\UbsModel;
use App\Models\UserModel;

class UserPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return $this->isActive($ubs);
    }

    public function view(UbsModel $ubs, UserModel $user): bool
    {
        return $this->ownsRecord($ubs, $user->ubs_id);
    }

    public function create(UbsModel $ubs, mixed $ubsId = null): bool
    {
        return $this->ownsRecord($ubs, is_string($ubsId) ? $ubsId : null);
    }

    public function update(UbsModel $ubs, UserModel $user): bool
    {
        return $this->ownsRecord($ubs, $user->ubs_id);
    }

    public function delete(UbsModel $ubs, UserModel $user): bool
    {
        return $this->ownsRecord($ubs, $user->ubs_id);
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
