<?php

namespace App\Http\Requests\PatientRequests;

class UpdatePatientRequest extends StorePatientRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:80', 'regex:/^[\pL][\pL\x{2019}\x{0027}-]*$/u'],
            'sex' => ['sometimes', 'required', 'boolean'],
            'neighborhood' => ['sometimes', 'required', 'string', 'max:120'],
            'street_name' => ['sometimes', 'nullable', 'string', 'max:160', $this->streetNameRule()],
            ...$this->removedIdentityFields(),
        ];
    }
}
