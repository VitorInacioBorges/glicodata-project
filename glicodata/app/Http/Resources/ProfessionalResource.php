<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'first_name' => $this->first_name,
            'specialty' => $this->specialty,
            'is_active' => (bool) $this->is_active,
            'assessments_count' => $this->whenCounted('assessments'),
        ];
    }
}
