<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'actor_type' => $this->actor_administrator_id ? 'admin' : 'ubs',
            'owner_ubs_id' => (string) $this->owner_ubs_id,
            'subject_type' => $this->subject_type,
            'subject_id' => (string) $this->subject_id,
            'action' => $this->action,
            'changed_fields' => $this->changed_fields,
            'redacted_at' => $this->redacted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
