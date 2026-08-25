<?php

namespace App\Http\Requests\AssessmentRequests;

use App\Http\Requests\Support\ApiFormRequest;

class CompleteAssessmentRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeNullableStrings(['symptoms']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'symptoms' => ['nullable', 'string', 'max:5000'],
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ];
    }
}
