<?php

namespace App\Policies\UserPolicies;

use App\Enums\UserRole;
use App\Models\UbsModel;
use App\Models\UserModel;

class UserPolicy
{
    public function viewAny(UbsModel|UserModel $actor): bool
    {
        return $this->canManage($actor);
    }

    public function view(UbsModel|UserModel $actor, UserModel $user): bool
    {
        return $this->sameUbs($actor, (string) $user->ubs_id)
            && ($this->canManage($actor) || ($actor instanceof UserModel && $actor->is($user)));
    }

    public function create(UbsModel|UserModel $actor, mixed $ubsId = null): bool
    {
        return $this->canManage($actor) && $this->sameUbs($actor, is_string($ubsId) ? $ubsId : null);
    }

    public function update(UbsModel|UserModel $actor, UserModel $user): bool
    {
        return $this->canManage($actor) && $this->sameUbs($actor, (string) $user->ubs_id);
    }

    public function delete(UbsModel|UserModel $actor, UserModel $user): bool
    {
        return $this->canManage($actor)
            && $this->sameUbs($actor, (string) $user->ubs_id)
            && (! $actor instanceof UserModel || ! $actor->is($user));
    }

    private function sameUbs(UbsModel|UserModel $actor, ?string $ubsId): bool
    {
        $actorUbsId = $actor instanceof UbsModel ? $actor->id : $actor->ubs_id;

        return $this->isActive($actor) && $ubsId !== null && hash_equals((string) $actorUbsId, $ubsId);
    }

    private function canManage(UbsModel|UserModel $actor): bool
    {
        return $this->isActive($actor)
            && ($actor instanceof UbsModel || $actor->role === UserRole::Admin);
    }

    private function isActive(UbsModel|UserModel $actor): bool
    {
        return $actor instanceof UserModel
            ? $actor->hasActiveAccountContext()
            : (bool) $actor->is_active;
    }
}
