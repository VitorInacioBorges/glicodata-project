<?php

namespace App\Policies\AuditEventPolicies;

use App\Models\AdministratorModel;
use App\Models\AuditEventModel;
use App\Models\UbsModel;

class AuditEventPolicy
{
    public function viewAny(UbsModel|AdministratorModel $account): bool
    {
        return (bool) $account->is_active;
    }

    public function view(UbsModel|AdministratorModel $account, AuditEventModel $event): bool
    {
        return (bool) $account->is_active
            && ($account instanceof AdministratorModel || hash_equals((string) $account->id, (string) $event->owner_ubs_id));
    }

    public function redact(UbsModel|AdministratorModel $account, AuditEventModel $event): bool
    {
        return (bool) $account->is_active && $account instanceof AdministratorModel;
    }
}
