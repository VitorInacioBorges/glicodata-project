<?php

namespace App\Http\Requests\AuditEventRequests;

use App\Http\Requests\Support\ApiFormRequest;

class RedactAuditEventRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['reason']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
