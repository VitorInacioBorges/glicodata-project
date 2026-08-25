<?php

namespace App\Policies\QuestionnairePolicies;

use App\Models\QuestionnaireVersionModel;
use App\Models\UserModel;

class QuestionnaireVersionPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return $user->hasActiveAccountContext();
    }

    public function view(UserModel $user, QuestionnaireVersionModel $version): bool
    {
        return $this->viewAny($user);
    }
}
