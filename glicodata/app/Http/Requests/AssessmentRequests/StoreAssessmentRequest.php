<?php

namespace App\Http\Requests\AssessmentRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'uuid', Rule::exists('patients', 'id')],
            'professional_id' => ['required', 'uuid', Rule::exists('professionals', 'id')],
            'questionnaire_version_id' => ['nullable', 'uuid', Rule::exists('questionnaire_versions', 'id')],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],
            'user_id' => ['prohibited'],
            'symptoms' => ['prohibited'],
        ];
    }
}
