<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'patient_id' => (string) $this->patient_id,
            'professional_id' => (string) $this->professional_id,
            'questionnaire_version_id' => (string) $this->questionnaire_version_id,
            'status' => $this->status?->value ?? $this->status,
            'answers' => $this->answers,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'patient' => PatientResource::make($this->whenLoaded('patient')),
            'professional' => ProfessionalResource::make($this->whenLoaded('professional')),
            'risk' => RiskResource::make($this->whenLoaded('risk')),
            'report' => ReportResource::make($this->whenLoaded('report')),
        ];
    }
}
