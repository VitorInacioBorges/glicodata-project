<?php

namespace App\Http\Requests\ReportRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class ExportReportsRequest extends ApiFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'format' => ['nullable', Rule::in(['csv', 'json'])],
        ];
    }
}
