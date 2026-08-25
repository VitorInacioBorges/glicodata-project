<?php

namespace App\Policies\RiskPolicies;

use App\Models\RiskModel;
use App\Models\UbsModel;

class RiskPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, RiskModel $risk): bool
    {
        return (bool) $ubs->is_active && $risk->assessment()->where('ubs_id', $ubs->id)->exists();
    }
}
