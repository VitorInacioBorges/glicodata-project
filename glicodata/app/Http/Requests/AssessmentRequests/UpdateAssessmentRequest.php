<?php

namespace App\Http\Requests\AssessmentRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'required', 'uuid', Rule::exists('patients', 'id')],
            'professional_id' => ['sometimes', 'required', 'uuid', Rule::exists('professionals', 'id')],
            'answers' => ['sometimes', 'required', 'array'],
            'answers.*' => ['nullable'],
            'user_id' => ['prohibited'],
            'symptoms' => ['prohibited'],
        ];
    }
}
