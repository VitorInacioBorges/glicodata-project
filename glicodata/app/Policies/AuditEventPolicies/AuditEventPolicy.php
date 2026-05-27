<?php

namespace App\Policies\AuditEventPolicies;

use App\Models\AuditEventModel;
use App\Models\UbsModel;

class AuditEventPolicy
{
    public function viewAny(UbsModel $ubs): bool
    {
        return (bool) $ubs->is_active;
    }

    public function view(UbsModel $ubs, AuditEventModel $event): bool
    {
        return (bool) $ubs->is_active
            && ($ubs->isAuditAdmin() || hash_equals((string) $ubs->id, (string) $event->owner_ubs_id));
    }

    public function redact(UbsModel $ubs, AuditEventModel $event): bool
    {
        return (bool) $ubs->is_active && $ubs->isAuditAdmin();
    }
}
