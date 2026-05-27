<?php

namespace App\Http\Requests\RiskRequests;

use App\Enums\RiskClassification;
use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateRiskRequest extends ApiFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'classification' => ['sometimes', 'required', Rule::enum(RiskClassification::class)],
            'score' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
