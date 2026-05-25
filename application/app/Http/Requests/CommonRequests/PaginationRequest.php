<?php

namespace App\Http\Requests\CommonRequests;

use App\Http\Requests\Support\ApiFormRequest;

class PaginationRequest extends ApiFormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 20);
    }
}
