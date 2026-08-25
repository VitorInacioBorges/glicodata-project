<?php

namespace App\Policies\PatientPolicies;

use App\Models\PatientModel;
use App\Models\UserModel;

class PatientPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return $this->isActive($user);
    }

    public function view(UserModel $user, PatientModel $patient): bool
    {
        return $this->ownsRecord($user, $patient->ubs_id);
    }

    public function create(UserModel $user, mixed $ubsId = null): bool
    {
        return $this->ownsRecord($user, is_string($ubsId) ? $ubsId : null);
    }

    public function update(UserModel $user, PatientModel $patient): bool
    {
        return $this->ownsRecord($user, $patient->ubs_id);
    }

    public function delete(UserModel $user, PatientModel $patient): bool
    {
        return $this->ownsRecord($user, $patient->ubs_id);
    }

    private function ownsRecord(UserModel $user, ?string $ubsId): bool
    {
        return $this->isActive($user) && $ubsId !== null && hash_equals((string) $user->ubs_id, $ubsId);
    }

    private function isActive(UserModel $user): bool
    {
        return $user->hasActiveAccountContext();
    }
}
