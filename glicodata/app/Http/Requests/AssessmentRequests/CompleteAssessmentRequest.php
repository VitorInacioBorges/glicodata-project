<?php

namespace App\Http\Requests\AssessmentRequests;

use App\Http\Requests\Support\ApiFormRequest;

class CompleteAssessmentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
            'user_id' => ['prohibited'],
            'symptoms' => ['prohibited'],
        ];
    }
}
