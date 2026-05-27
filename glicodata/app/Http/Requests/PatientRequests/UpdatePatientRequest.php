<?php

namespace App\Http\Requests\PatientRequests;

use App\Http\Requests\Support\ApiFormRequest;
use App\Rules\CpfRules\ValidCpf;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['name', 'cpf']);
        $this->normalizeNullableStrings(['address', 'phone']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'birth' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'sex' => ['sometimes', 'required', 'boolean'],
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'max:14',
                new ValidCpf,
                Rule::unique('patients', 'cpf')->ignore((string) $this->route('id')),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
