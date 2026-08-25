<?php

namespace App\Policies\PatientPolicies;

use App\Models\PatientModel;
use App\Models\UbsModel;

class PatientPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->owns($ubs, $patient->ubs_id);
    }

    public function create(UbsModel $ubs, mixed $ubsId = null): bool
    {
        return $this->owns($ubs, is_string($ubsId) ? $ubsId : null);
    }

    public function update(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->view($ubs, $patient);
    }

    public function delete(UbsModel $ubs, PatientModel $patient): bool
    {
        return $this->view($ubs, $patient);
    }

    private function owns(UbsModel $ubs, ?string $ownerId): bool
    {
        return (bool) $ubs->is_active && $ownerId === (string) $ubs->id;
    }
}
