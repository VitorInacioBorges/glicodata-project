<?php

namespace App\Http\Requests\ProfessionalRequests;

class UpdateProfessionalRequest extends StoreProfessionalRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:80', 'regex:/^[\pL][\pL\x{2019}\x{0027}-]*$/u'],
            'specialty' => ['sometimes', 'required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            ...$this->removedIdentityFields(),
        ];
    }
}
