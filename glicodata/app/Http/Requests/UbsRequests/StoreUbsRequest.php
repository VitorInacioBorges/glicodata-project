<?php

namespace App\Http\Requests\UbsRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUbsRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['cnes']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cnes' => [
                'required',
                'string',
                'size:7',
                'regex:/^[0-9]{7}$/',
                Rule::unique('ubs', 'cnes'),
            ],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ];
    }
}
