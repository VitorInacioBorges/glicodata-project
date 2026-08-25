<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'version' => (int) $this->version,
            'schema' => $this->schema,
            'published_at' => $this->published_at?->toISOString(),
            'questionnaire' => [
                'code' => $this->questionnaire?->code,
                'title' => $this->questionnaire?->title,
                'description' => $this->questionnaire?->description,
            ],
        ];
    }
}
