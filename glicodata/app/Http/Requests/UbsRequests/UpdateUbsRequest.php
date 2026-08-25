<?php

namespace App\Http\Requests\UbsRequests;

use App\Http\Requests\Support\ApiFormRequest;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateUbsRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeNullableStrings(['name', 'bairro_ref', 'address', 'phone', 'email']);
        $this->normalizeStrings(['cnes']);

        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower((string) $this->input('email'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $account = $this->user();
        $isAdministrator = $account instanceof AdministratorModel;
        $ubsId = (string) ($this->route('id') ?? ($account instanceof UbsModel ? $account->id : ''));

        return [
            'cnes' => $isAdministrator
                ? [
                    'sometimes',
                    'required',
                    'string',
                    'size:7',
                    'regex:/^[0-9]{7}$/',
                    Rule::unique('ubs', 'cnes')->ignore($ubsId),
                ]
                : ['prohibited'],
            'district_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('districts', 'id')],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bairro_ref' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'email' => [
                'sometimes',
                'nullable',
                'email:rfc',
                'max:255',
                Rule::unique('ubs', 'email')->ignore($ubsId),
            ],
            'is_active' => $isAdministrator
                ? ['sometimes', 'required', 'boolean']
                : ['prohibited'],
            'password' => ['prohibited'],
        ];
    }
}
