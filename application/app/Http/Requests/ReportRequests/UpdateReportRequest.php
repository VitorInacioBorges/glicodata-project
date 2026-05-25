<?php

namespace App\Http\Requests\ReportRequests;

use App\Http\Requests\Support\ApiFormRequest;

class UpdateReportRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['title', 'description', 'comment']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'comment' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
