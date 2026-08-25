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
            'symptoms' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'answers' => ['sometimes', 'required', 'array'],
            'answers.*' => ['nullable'],
        ];
    }
}
