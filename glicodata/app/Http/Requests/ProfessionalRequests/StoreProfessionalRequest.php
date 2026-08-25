<?php

namespace App\Http\Requests\ProfessionalRequests;

use App\Http\Requests\Support\ApiFormRequest;

class StoreProfessionalRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['first_name', 'specialty']);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80', 'regex:/^[\pL][\pL\x{2019}\x{0027}-]*$/u'],
            'specialty' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            ...$this->removedIdentityFields(),
        ];
    }

    /** @return array<string, array<int, string>> */
    protected function removedIdentityFields(): array
    {
        return array_fill_keys([
            'name', 'cpf', 'birth', 'sex', 'address', 'phone', 'email',
            'password', 'password_confirmation', 'role', 'council_type',
            'council_number', 'council_uf',
        ], ['prohibited']);
    }
}
