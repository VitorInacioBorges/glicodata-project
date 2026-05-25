<?php

namespace App\Http\Requests\RiskRequests;

use App\Enums\RiskClassification;
use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreRiskRequest extends ApiFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'assessment_id' => [
                'required',
                'uuid',
                Rule::exists('assessments', 'id'),
                Rule::unique('risks', 'assessment_id'),
            ],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'classification' => ['required', Rule::enum(RiskClassification::class)],
            'score' => ['required', 'integer', 'min:0'],
        ];
    }
}
