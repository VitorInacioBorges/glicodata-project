<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'first_name' => $this->first_name,
            'sex' => (bool) $this->sex,
            'neighborhood' => $this->neighborhood,
            'street_name' => $this->street_name,
        ];
    }
}
