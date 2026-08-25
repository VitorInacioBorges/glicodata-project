<?php

namespace App\Http\Requests\ReportRequests;

use App\Http\Requests\Support\ApiFormRequest;

class UpdateReportRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['description']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'title' => ['prohibited'],
            'comment' => ['prohibited'],
        ];
    }
}
