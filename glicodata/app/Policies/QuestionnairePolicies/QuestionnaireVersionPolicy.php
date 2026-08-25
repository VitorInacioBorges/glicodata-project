<?php

namespace App\Policies\QuestionnairePolicies;

use App\Enums\QuestionnaireVersionStatus;
use App\Models\QuestionnaireVersionModel;
use App\Models\UbsModel;

class QuestionnaireVersionPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, QuestionnaireVersionModel $version): bool
    {
        return (bool) $ubs->is_active && $version->status === QuestionnaireVersionStatus::Published;
    }
}
