<?php

namespace App\Policies\PatientPolicies;

use App\Models\PatientModel;
use App\Models\UbsModel;

class PatientPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return $this->isActive($ubs);
    }

    public function view(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->ownsRecord($ubs, $patient->ubs_id);
    }

    public function create(UbsModel $ubs, mixed $ubsId = null): bool
    {
        return $this->ownsRecord($ubs, is_string($ubsId) ? $ubsId : null);
    }

    public function update(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->ownsRecord($ubs, $patient->ubs_id);
    }

    public function delete(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->ownsRecord($ubs, $patient->ubs_id);
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
