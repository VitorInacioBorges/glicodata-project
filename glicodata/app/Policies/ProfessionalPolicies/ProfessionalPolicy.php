<?php

namespace App\Policies\ProfessionalPolicies;

use App\Models\ProfessionalModel;
use App\Models\UbsModel;

class ProfessionalPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, ProfessionalModel $professional): bool
    {
        return (bool) $ubs->is_active && $professional->ubs_id === (string) $ubs->id;
    }

    public function create(UbsModel $ubs, mixed $ubsId = null): bool
    {
        return (bool) $ubs->is_active && $ubsId === (string) $ubs->id;
    }

    public function update(UbsModel $ubs, ProfessionalModel $professional): bool
    {
        return $this->view($ubs, $professional);
    }

    public function delete(UbsModel $ubs, ProfessionalModel $professional): bool
    {
        return $this->view($ubs, $professional);
    }
}
