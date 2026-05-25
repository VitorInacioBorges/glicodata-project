<?php

namespace App\Http\Requests\ReportRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['title', 'description', 'comment']);
    }

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
                Rule::unique('reports', 'assessment_id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
