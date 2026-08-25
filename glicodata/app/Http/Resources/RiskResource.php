<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'assessment_id' => (string) $this->assessment_id,
            'score' => (int) $this->score,
            'percentage' => (float) $this->percentage,
            'classification' => $this->classification?->value ?? $this->classification,
        ];
    }
}
