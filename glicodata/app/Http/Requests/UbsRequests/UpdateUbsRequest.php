<?php

namespace App\Http\Requests\UbsRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateUbsRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(
            ['name', 'bairro_ref', 'address', 'phone', 'email'],
            ['email'],
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'district_id' => ['sometimes', 'required', 'uuid', Rule::exists('districts', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bairro_ref' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'email' => [
                'sometimes',
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('ubs', 'email')->ignore((string) $this->route('id')),
            ],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
