<?php

namespace App\Http\Requests\AssessmentRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['symptoms']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'required', 'uuid', Rule::exists('patients', 'id')],
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'id')],
            'symptoms' => ['sometimes', 'required', 'string'],
            'answers' => ['sometimes', 'required', 'array'],
        ];
    }
}
